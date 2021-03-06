<?php

/*
 * RESTful API Template
 * 
 * A RESTful API template based on flight-PHP framework
 * This software project is based on my recent REST-API development experiences. 
 * 
 * ANYONE IN THE DEVELOPER COMMUNITY MAY USE THIS PROJECT FREELY
 * FOR THEIR OWN DEVELOPMENT SELF-LEARNING OR DEVELOPMENT or LIVE PROJECT 
 * 
 * @author	Sabbir Hossain Rupom
 * @since	Version 1.0.0
 * @filesource
 */

(defined('APP_NAME')) OR exit('Forbidden 403');

/**
 * Controller for application
 *
 * @property BaseClass $action BaseClass
 * @author sabbir-hossain
 */
class Controller {

    protected static $apiName;
    protected static $getParams;
    protected static $headers;
    protected static $json;

    /**
     * Initialize application
     * 
     * @param array $arrayParams Array parameters for initializing API class
     */
    public static function init($arrayParams) {
        $data = null;
        try {
            // prepare api controller from request url call
            self::$apiName = self::prepareApiClass($arrayParams['name'], $arrayParams['group']);
            self::$getParams = $_GET;
            self::$headers = getallheaders();

            if (in_array($arrayParams['method'], array('POST', 'PUT', 'PATCH', 'DELETE'))) {
                /*
                 * Fetch all requested parameters
                 */
                $data = file_get_contents('php://input');

                self::$json = json_decode($data);

                /*
                 * Check if requested parameters are in json format or not 
                 */
                if (!empty($data) && json_last_error() != JSON_ERROR_NONE && empty($_FILES)) {
                    throw new System_Exception(ResultCode::INVALID_JSON, "Invalid JSON: $data");
                }
            } else {
                self::$json = array();
            }

            /*
             * Check if requested API controller exist in server
             */
            if (!class_exists(self::$apiName)) {
                throw new System_Exception(ResultCode::UNKNOWN_ERROR, "No such api: " . self::$apiName);
            }

            /**
             * Call Base Controller to Retrieve Instance of API Controller
             */
            $action = new self::$apiName(self::$headers, self::$getParams, self::$json, self::$apiName);
            $result = $action->process();
        } catch (Exception $e) {
            /*
             * Handle all exception messages
             */

            if ($e instanceof System_Exception) {
                /*
                 * Handle all application error messages
                 */
                header("HTTP/1.1 " . ResultCode::getHTTPstatusCode($e->getCode()) . " " . ResultCode::getTitle($e->getCode()));
                $errMsg = empty($e->getMessage()) ? ResultCode::getMessage($e->getCode()) : $e->getMessage();
                $result = array(
                    'result_code' => $e->getCode(),
                    'time' => Helper_DateUtil::getToday(),
                    'error' => array(
                        'title' => ResultCode::getTitle($e->getCode()),
                        'msg' => $errMsg
                    )
                );

                System_Log::log(self::$apiName . ' (' . ResultCode::DATABASE_ERROR . '): ' . $errMsg);
            } else if ($e instanceof PDOException) {
                /*
                 * Handle all database related error messages
                 */
                header("HTTP/1.1 " . ResultCode::getHTTPstatusCode(ResultCode::DATABASE_ERROR) . " " . ResultCode::getTitle(ResultCode::DATABASE_ERROR));
                $errMsg = empty($e->getMessage()) ? ResultCode::getMessage(ResultCode::DATABASE_ERROR) . ': check connection' : $e->getMessage();
                $result = array(
                    'result_code' => ResultCode::DATABASE_ERROR,
                    'time' => Helper_DateUtil::getToday(),
                    'error' => array(
                        'title' => ResultCode::getTitle(ResultCode::DATABASE_ERROR),
                        'msg' => $errMsg
                    )
                );

                System_Log::log(self::$apiName . ' (' . ResultCode::DATABASE_ERROR . '): ' . $errMsg);
            } else {
                /*
                 * Handle all system error messages
                 */
                header("HTTP/1.1 " . ResultCode::getHTTPstatusCode(ResultCode::UNKNOWN_ERROR) . " " . ResultCode::getTitle(ResultCode::UNKNOWN_ERROR));
                $errMsg = empty($e->getMessage()) ? ResultCode::getMessage(ResultCode::UNKNOWN_ERROR) : $e->getMessage();
                $result = array(
                    'result_code' => ResultCode::UNKNOWN_ERROR,
                    'time' => Helper_DateUtil::getToday(),
                    'error' => array(
                        'title' => ResultCode::getTitle(ResultCode::UNKNOWN_ERROR),
                        'msg' => $errMsg
                    )
                );

                System_Log::log(array(
                    'message' => self::$apiName . ' (' . ResultCode::UNKNOWN_ERROR . '): ' . $errMsg,
                    'file_name' => $e->getFile(),
                    'line_number' => $e->getLine()
                ));
            }

            if (Config_Config::getInstance()->isErrorDump()) {
                /*
                 * Additional error messages 
                 * For developers debug purpose
                 */
                $result['error_dump'] = array(
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                );
            }
        }
        $json_array = $result;


        if (strtoupper(Flight::get('env')) != 'PRODUCTION') {
            /*
             * Calculate server execution time for running API script [ For developers only ]
             * And add to output result
             */
            $json_array['execution_time'] = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

            $sql = "INSERT INTO `api_exec_time` (`api_name`, `exec_time`) VALUES ('" . self::$apiName . "', {$json_array['execution_time']});";
            $pdo = Flight::pdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }

        // JSON Output
        View_Output::responseJson($json_array);
    }

    /**
     * Initialize API application
     * @param type $name Api name
     */
    public static function initAPI($name) {
        self::init([
            'name' => $name,
            'method' => $_SERVER['REQUEST_METHOD'],
            'group' => NULL
        ]);
    }

    /**
     * Initialize API Application from Group
     * @param type $group Group name
     * @param type $name Api name
     */
    public static function initGroupAPI($group, $name) {
        self::init([
            'name' => $name,
            'method' => $_SERVER['REQUEST_METHOD'],
            'group' => $group,
        ]);
    }

    /**
     * Prepare API class name
     * @param string $name Api name
     * @param string $group Api Group name
     */
    public static function prepareApiClass($name, $group) {
        return is_null($group) ? Helper_CommonUtil::camelize($name) : ucfirst($group) . '_' . Helper_CommonUtil::camelize($name);
    }

}
