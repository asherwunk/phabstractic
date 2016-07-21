<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Filter Interface
 * 
 * A filter is more a predicate than a data type, but not quite a design
 * pattern (to me).  This allows you to define a filter that you can test values
 * against.
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Data
 * @subpackage Resource
 * 
 */
 
/**
 * Falcraft Libraries Data Types Resource Namespace
 * 
 */
namespace Phabstractic\Data\Types\Resource
{
    
    /**
     * Filter Interface
     * 
     * A filter is able to test a value against some internal logic.  It will
     * return true if it passes, false otherwise.  There is also a static
     * function that must be implemented that enables you to check an array
     * of values against the internal test.
     * 
     * CHANGELOG
     * 
     * 1.0: Created FilterInterface - April 11th, 2015
     * 2.0: reformatted for inclusion in phabstractic - July 13th, 2016
     * 
     * @version 2.0
     * 
     */
    interface FilterInterface
    {
        /**
         * Is a value allowed through this filter?
         * 
         * @param mixed $value The value to check
         * @param bool $strict Should we throw errors?
         * 
         * @return bool Allowed or not?
         * 
         * @throws Phabstractic\Data\Types\Exception\InvalidArgumentException
         * 
         */
        public function isAllowed($type, $strict = false);
        
        /**
         * Checks values to see if they fit 'through' the filter
         * 
         * This goes through each value, grabs its type, and compares it
         * against the filter
         * 
         * @static
         * 
         * @param array $values The values to check against the restrictions
         * @param Resource\FilterInerface The filter to run them through
         * @param bool $strict Throw errors?
         * 
         * @return bool Valid types?
         * 
         * @throws Phabstractic\Data\Types\Exception\InvalidArgumentException
         *              if the value is untypeable, or illegal (only strict)
         * 
         */
        public static function checkElements(
            array $values,
            FilterInterface $filter,
            $strict = false);
        
        /**
         * Retrieve default filter settings
         * 
         * This should be just about any basic filter setting
         * 
         * @static
         * 
         * @return Phabstractic\Data\Types\Resource\FilterInterface
         * 
         */
        public static function getDefaultRestrictions();
        
    }
}
