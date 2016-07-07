<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Range of Values (Integers)
 * 
 * This file contains the Range class.  A range is a (generally) integer
 * sequence with a minimum and a maximum.  A number can fall either in range,
 * or out of range.
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Data
 * @subpackage Structures
 * 
 */

/**
 * Falcraft Libraries Data Types Namespace
 * 
 */
namespace Phabstractic\Data\Types
{
    require_once(realpath( __DIR__ . '/../../') . '/falcraftLoad.php');
    
    /* This class contains a static function for returning unique values
       for an array that is more object compatible. */
    $includes = array('/Data/Types/Exception/InvalidArgumentException.php');
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types\Exception;
    
    /**
     * Range - Defines A Range of Values
     * 
     * A range is a predicate that checks if a value is between a minimum and a maximum
     * 
     * CHANGELOG
     * 
     * 1.0: Created Range - May 20th, 2013
     * 2.0: Refactored and re-formatted for inclusion
     *          in primus - April 11th, 2015
     * 3.0: formatted for inclusion in phabstractic - July 7th, 2016
     *      (exchanged construction arguments)
     * 
     * @version 3.0
     * 
     */
    class Range implements \IteratorAggregate
    {
        
        /**
         * The maximum of the range
         * 
         * @var int Maximum of range
         * 
         */
        private $max = 0;
        
        /**
         * The minimum of the range
         * 
         * @var int Minimum of range
         * 
         */
        private $min = 0;
        
        // \IteratorAggregate functions
        
        /**
         * Instead of \Iterator we use \IteratorAggregate instead
         * 
         * This gets the iterator for the range
         * 
         * @return \ArrayIterator The iterator for the range
         * 
         */
        public function getIterator()
        {
            return \ArrayIterator(range($this->min, $this->max));
        }
        
        /**
         * The Range Constructor
         * 
         * Takes a maximum, and a minimum, if an argument is missing, defaults to 0
         * 
         * @param int $max Maximum range value
         * @param int $min Minimum range value
         * 
         * @throws \InvalidArgumentException if Max and Min are backwards, or are not integers
         * 
         */
        public function __construct($min = 0, $max = 0)
        {
            if ( !is_int($max) || !is_int($min) ) {
                throw new Exception\InvalidArgumentException(
                    'Phabstractic\\Data\\Types\\Range->__construct: constructor arguments not integers');
            }
            
            if ( $max < $min ) {
                throw new Exception\InvalidArgumentException(
                    'Phabstractic\\Data\\Types\\Range->__construct: max and minimum reversed');
            }
            
            $this->max = $max;
            $this->min = $min;
        }
        
        /**
         * Sets the maximum of the range
         * 
         * @param int $max The given maximum
         * 
         * @throws \InvalidArgumentException if max is smaller than $this->min
         * 
         */
        public function setMaximum($max)
        {
            if ($max < $this->min) {
                throw new Exception\InvalidArgumentException(
                    'Phabstractic\\Data\\Types\\Range->setMaximum: maximum less than determined minimum.');
            }
            
            $this->max = $max;
        }
        
        /**
         * Sets the minimum of the range
         * 
         * @param int $min The given minimum
         * 
         * @throws \InvalidArgumentException if min is larger than $this->Max
         * 
         */
        public function setMinimum($min)
        {
            if ( $min > $this->max ) {
                throw new Exception\InvalidArgumentException(
                    'Phabstractic\\Data\\Types\\Range->setMinimum: minimum greater than determined maximum.');
            }
            
            $this->min = $min;
        }
        
        /**
         * Retrieve range maximum
         * 
         * @return int The range maximum
         * 
         */
        public function getMaximum()
        {
            return $this->max;
        }
        
        /**
         * Retrieve range minimum
         * 
         * @return int The range minimum
         * 
         */
        public function getMinimum()
        {
            return $this->min;
        }
        
        /**
         * Checks to see if an integer is in range.
         * 
         * include array - minimum => true if minimum included
         *                 maximum => true if maximum included
         * 
         * @param int $x The integer to check
         * @param array $include Whether to include the maximum or minimum
         * 
         * @return bool Is x in range?
         */
        public function isInRange($x, array $include = array())
        {
            $include = array_change_key_case($include);
            $leftOperator = '<';
            $rightOperator = '>';
            if ($include) {
                if (isset($include['minimum'] ) && $include['minimum']) {
                    $leftOperator .= '=';
                }
                
                if (isset( $include['maximum'] ) && $include['maximum']) {
                    $rightOperator .= '=';
                }
            }
            
            return eval("return ((\$this->min $leftOperator \$x) && (\$this->max $rightOperator \$x));" );
        }
        
    }
    
}
