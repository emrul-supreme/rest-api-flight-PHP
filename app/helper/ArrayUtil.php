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
 * A utility class that summarizes processing related to arrays
 */
class Helper_ArrayUtil {

    /**
     * Specify elements of the array and acquire data
     *
     * If it is possible to acquire the data contained in the key.
     *
     * @param string $key
     * @param array $array
     * @param unknown_type $defaultValue
     */
    public static function getArrayValue($key, $array, $defaultValue = null) {
        if (!array_key_exists($key, $array)) {
            return $defaultValue;
        }
        return $array[$key];
    }

    public static function searchPrefixValue($prefix, array $array) {
        foreach ($array as $key => $value) {
            if (strpos($value, $prefix) !== false) {
                return $key;
            }
        }
        return false;
    }

    /**
     * Rewind array index from 0
     * If the value is set to null, it is removed from the array
     *
     * @access public
     * @param array $arrayData Target sequence
     */
    public static function trimArray($arrayData) {
        // If the target array is empty or null, return it as it is
        if (empty($arrayData)) {
            return $arrayData;
        }

        $returnArray = array();

        reset($arrayData);

        while (list($key, $value) = each($arrayData)) {
            if ($value !== null) {
                $returnArray[] = $value;
            }
        }

        return $returnArray;
    }

    /**
     * Add an element to the specified index of the base array
     *
     * @param array $array
     * @param array $element
     * @param int $index
     * @return array
     */
    public static function add(array &$array, array $element, $index = null) {

        if ($index === null) {
            $index = count($array);
        }

        array_splice($array, $index, 0, $element);

        return true;
    }

}
