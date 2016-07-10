<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Static Utility Functions: Array Functions
 * 
 * Sometimes the built-in array functions are either insufficient for our
 * purposes, times when they aren't entirely available, and/or sometimes don't
 * work with our datatype as we would like.  These array functions are more
 * compatible with the rest of the Phabstractic library as needed.  If a PHP based
 * array function will work in your instance, use that instead.
 * 
 * @copyright Copyright 2015 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Utilities
 * @subpackage Array
 * 
 */

/**
 * Falcraft Resource/Utilities Namespace
 * 
 */
namespace Phabstractic\Resource
{
    require __DIR__ . '/../../../vendor/autoload.php';
    
    use Zend\Stdlib;
    
    /**
     * The Static Array Utility Class
     * 
     * This class contains all the static array-based utility functions
     * that are used in particular cases throughout the library.
     *
     * CHANGELOG
     * 
     * 1.0:   Documented Utility - October 7th, 2013
     * 2.0:   Refactored into Primus/Falcraft - April 10th, 2015
     * 2.0.1: Added returnUniqueByReference - April 10th, 2015
     * 3.0:   Inherited from Zend\Stdlib\ArrayUtils - April 12th. 2015
     * 3.1:   Modified elementComparison for
     *        further compatibility - April 14th, 2015
     * 4.0:   returnUnique now returns appropriate data
     *        objectToArray now includes values
     *        reformatted for inclusion in phabstractic - July 8th, 2015
     * 
     * @version 4.0
     * 
     */
    class ArrayUtilities extends Stdlib\ArrayUtils
    {
        
        /**
         * Return Unique (array)
         * 
         * This can be used as an alternative to array_unique when that
         * built-in function doesn't seem to work for your purposes.  This is
         * not recusrive but should work on associative arrays (though,
         * haphazardly)
         * 
         * Version 4: Compatible with PHP 7
         *            Added associative option as second default argument
         * 
         * @static
         * 
         * @param array $data The given data to sort
         * 
         * @return array The unique array, this DOES NOT alter the argument
         * 
         */
        public static function returnUnique(array $data, $assoc = false)
        {
            $r = array();
            foreach ($data as $key => $datum) {
                if (!$r) {
                    $r[$key] = $datum;
                } else {
                    reset($r);
                    $nu = false;
                    while ($t = each($r)) {
                        if (self::elementComparison($t['value'],$datum) === 0) {
                            $nu = true;
                        }
                    }
                    
                    // If we've reached the end, then it's a unique value
                    if (!$nu) {
                        if ($assoc) {
                            $r[$key] = $datum;
                        } else {
                            $r[] = $datum;
                        }
                    }
                    
                }
                
            }
            
            return $r;
        }
        
        /**
         * Return Unique (array)
         * 
         * This can be used as an alternative to array_unique when that
         * built-in function doesn't seem to work for your purposes.  This is
         * not recusrive but should work on associative arrays (though,
         * haphazardly)
         * 
         * Version 4: Compatible with PHP 7
         *            Added associative option as second default argument
         * 
         * @static
         * 
         * @param array $data The given data to sort
         * 
         * @return array The unique array, THIS DOES ALTER the argument
         * 
         */
        public static function returnUniqueByReference(array &$data, $assoc = false)
        {
            $r = array();
            foreach ($data as $key => $datum) {
                if (!$r) {
                    $r[$key] = &$data[$key];
                } else {
                    reset($r);
                    $nu = false;
                    while ($t = each($r)) {
                        if (self::elementComparison($t['value'], $datum) === 0) {
                            $nu = true;
                        }
                    }
                    
                    // If we've reached the end, then it's a unique value
                    if (!$nu) {
                        if ($assoc) {
                            $r[$key] = &$data[$key];
                        } else {
                            $r[] = &$data[$key];
                        }
                    }
                }
            }
            
            $data = $r;
            return $r;
        }
        
        /**
         * Compare Array Elements in an Object Compatible Sense
         * 
         * This is an attempt to compare unknown objects in an array to
         * each other.  The idea is that one object will have the same hash
         * even if referenced in different places, and that that hash will be
         * different from other object's.  It seems to work okay.
         * 
         * @param mixed $a The first element
         * @param mixed $b The second element (imagine in an equality test)
         * 
         * @return int 0, 1,  or -1
         * 
         */
        public static function elementComparison($a, $b) {
            if (is_object($a) && is_object($b)) {
                $c1 = get_class($a);
                $c2 = get_class($b);
                if ( $c1 == $c2 ) {
                    if ($a === $b) {
                        return 0;
                    } else {
                        if (spl_object_hash($a) == spl_object_hash($b)) {
                            return 0;
                        } elseif (spl_object_hash($a) > spl_object_hash($b)) {
                            return 1;
                        } else {
                            return -1;
                        }
                        
                    }
                    
                } else {
                    if ( $c1 > $c2 ) {
                        return 1;
                    } else {
                        return -1;
                    }
                }
            
            } else if (is_object($a) && !is_object($b)) {
                return 1;
            } else if (!is_object($a) && is_object($b)) {
                return -1;
            } else {
                if ($a == $b) {
                    return 0;
                } else if ($a > $b) {
                    return 1;
                } else {
                    return -1;
                }
                
            }
        
        }
        
        
        /**
         * A utility function for converting a stdClass object to an array
         * 
         * This could also work on other objects as well, precariously,
         * but it's designed for turning stdClass objects into arrays
         * 
         * Version 4: Compatible with PHP 7
         *            Now includes values
         * 
         * @link http://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
         * 
         * @static
         * 
         * @param object $o The given object to convert
         * 
         * @return array The converted array
         * 
         */
        public static function objectToArray($o)
        {
            // Recursive Function
            if (is_object($o)) {
                // Gets the properties of the given object with get_object_vars function
                $v = get_object_vars($o);
                $r = array();
                foreach ($v as $k => $val) {
                    if (is_object($val)) {
                        $r[$k] = self::objectToArray($val);
                    } else {
                        $r[$k] = $o->$k;
                    }
                }
            }
            
            return $r;
        }
        
        /**
         * Convert an array into a stdClass object
         * 
         * Converts an array to a simple object
         * 
         * @link http://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
         * 
         * @static
         * 
         * @param array $v The given array to convert
         * 
         * @return stdClass The returned converted object
         * 
         */
        public static function arrayToObject($v)
        {
            // Recursive Function
            if (is_array($v)) {
                // Return array converted to object
                return (object) array_map(array('static', 'arrayToObject' ), $v);
            } else {
                // Return object
                return $v;
            }
            
        }
        
        /**
         * Change the case of the values of an array
         * 
         * Like array_change_key_case except for values, defaults
         * to CASE_LOWER, but CASE_UPPER can be used as well.
         * 
         * NOTE: Using recursion, this processes the given array recursively.
         * 
         * @link http://www.php.net/manual/en/function.array-change-key-case.php#78056
         * 
         * @static
         * 
         * @param array $input The array to use as input
         * @param int $case CASE_LOWER or CASE_UPPER
         * 
         * @return array An array with the values all cased
         * 
         */
        public static function arrayChangeValueCase($input, $case = CASE_LOWER)
        {
            $aRet = array();
            if (!is_array($input)) {
                return $aRet;
            }
                 
            foreach ($input as $key => $value) {
                if (is_array($value)) {
                    $aRet[$key] = self::array_change_value_case($value, $case);
                    continue;
                }
                         
                $aRet[$key] = ($case == CASE_UPPER ?
                                            strtoupper($value) : 
                                            strtolower($value));
            }
                 
            return $aRet;
        }
        
    }
    
}
