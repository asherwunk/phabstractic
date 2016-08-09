<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Abstract List
 * 
 * This file contains the AbstractList class.  This abstract class defines common
 * functions to be expected from lists, as well as implements the
 * Phabstractic\Data\Types\Resource\ListInterface all in one go.  Inheriting from
 * AbstractList not only gives you shoulders to stand on (exchange, for instance)
 * but also makes $ instanceof Phabstractic\Data\Types\Resource\ListInterface true
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
 * Falcraft Libraries Data Types Resource Namespace
 * 
 */
namespace Phabstractic\Data\Types\Resource
{
    require_once(realpath( __DIR__ . '/../../../') . '/falcraftLoad.php');
    
    $includes = array(// we implement the list interface
                      '/Data/Types/Resource/ListInterface.php',);
    
    falcraftLoad($includes, __FILE__);
    
    /**
     * List Abstract Class - Defines a basic list class,
     *     implements ListInterface
     * 
     * A list can be several things: a stack, a queue, a ring buffer, etc.
     * This abstract class implements the ListInterface and defines
     * an internal list variable (list = array).  Inherit from this class
     * if you are building a list like class.
     * 
     * CHANGELOG
     * 
     * 1.0: Created AbstractList - May 10th, 2013
     * 2.0: Refactored and re-formatted for inclusion
     *          in primus - April 11th, 2015
     * 3.0: eliminated dependency on configurationtrait, subs can implement
     *      reformatted for inclusion in phabstractic - July 21st, 2016
     * 
     * @todo make some static methods that implement common stack applications
     * 
     * @link http://en.wikipedia.org/wiki/Stack_(data_structure) [English]
     * 
     * @version 3.0
     * 
     */
    abstract class AbstractList implements ListInterface
    {
        /**
         * The internal list member
         * 
         * Generally an array, but can be overridden by
         * a child class to be anything.
         * 
         * @var array The list data structure array
         * 
         */
        protected $list = array();
        
        /**
         * AbstractList Constructor
         * 
         * Populates the internal member array
         * 
         * Creates an empty list if no parameter given.
         * 
         * @param mixed $data The data to populate the internal member array
         * 
         */
        public function __construct($data = null) 
        {
            if (is_array($data)) { 
                $this->list = $data; 
            } elseif ($data instanceof AbstractList) { 
                $this->list = $data->getList(); 
            } elseif ( $data ) { 
                $this->list = array($data);
            } else { 
                $this->list = array();
            }
            
        } 
        
        /**
         * Retrieve the 'top' of the list (lifo, fifo, etc.)
         * 
         * Note: Does not 'pop' the value off the list
         * 
         * @return mixed|null 'Top' Value of List, or null
         * 
         */
        abstract public function top();
        
        /**
         * Return the 'top' of the list as a reference
         * 
         * Note: Does not 'pop' the value off the list
         * 
         * @return mixed|null 'Top' Values as Reference of List, or null
         * 
         */
        abstract public function &topReference();
        
        /**
         * Push a value on to the list (lifo, fifo, etc)
         * 
         * @return int Count of new list
         * 
         */
        abstract public function push();
        
        /**
         * Push a reference on to the list (fifo, lifo, etc)
         * 
         * @return int Count of new list
         * 
         */
        abstract public function pushReference( &$a );
        
        /**
         * Returns the 'top' value and pops the value off the list
         * 
         * @return mixed|null The 'top' value of the list, or null
         */
        abstract public function pop();
        
        /**
         * Returns the 'top' value as reference, pops value off
         * 
         * @return mixed|null The 'top' value as reference of the list, or null
         * 
         */
        abstract public function &popReference();
        
        /**
         * Return the $i'th element of the list
         * 
         * @param integer $i The numerical index into the list
         * 
         * @return mixed The value found at index of list: list[index]
         * 
         * @throws \RangeException If index is out of well... range.
         * 
         */
        abstract public function index($i);
        
        /**
         * Return the $i'th element of the list as a reference
         * 
         * @param integer $i The numerical index into the list
         * 
         * @return mixed The value at the list's numerical index as a reference
         * 
         * @throws \RangeException If index is out of well... range.
         *
         */
        abstract public function &indexReference($i); 
        
        /**
         * Return the size of the list
         * 
         * @return int The number of elements in the list currently
         * 
         */
        public function count()
        {
            return count($this->list);
        }
        
        /**
         * Return whether the list is empty currently
         * 
         * @return bool Whether ths list is empty or not
         * 
         */
        public function isEmpty()
        { 
            return empty($this->list); 
        } 
    
        /**
         * Clear all the values from the list
         * 
         * @return Phabstractic\Data\Types\Resource\AbstractList For chaining
         * 
         */
        public function clear()
        { 
            $this->list = array();
            return $this;
        }
        
        /**
         * Remove a value out of the list
         * 
         * This is just in case we want to remove something out of order.
         * 
         * Only removes the first occurance of value
         * 
         * @param mixed $value The value to remove
         * 
         * @return Phabstractic\Data\Types\Resource\AbstractList For chaining
         * 
         */
        public function remove($value)
        {
            if ($key = array_search($value, $this->list)) {
                unset($this->list[$key]);
            }
            
            // resets indices
            $this->list = array_merge($this->list, array());
            return $this->list;
        }
        
        /**
         * Retrieve the list element of the list
         * 
         * @return array The current internal list member
         * 
         */
        public function getList()
        {
            return $this->list;
        }
        
        /**
         * Exchange the two top elements of the list
         * 
         * @abstract
         * 
         * @return Phabstractic\Data\Types\Resource\AbstractList For chaining
         * 
         */
        abstract public function exchange();
         
        /**
         * Duplicate the value at the top of the list
         * 
         * Note: Adds the duplicate to the front of the list
         * 
         * @abstract
         * 
         * @return Phabstractic\Data\Types\Resource\AbstractList For chaining
         * 
         */
        abstract public function duplicate();
        
        /**
         * 'Rolls' the list like one might 'roll' a wheel.
         * 
         * Negative values roll the list the opposite direction
         * positive values roll the list.
         * 
         * Note: Edits the list in place, does not return copy of list
         * 
         * EX:
         * 
         * 12345 would become 34512
         * 
         * @param int $i The amount to roll the list
         * 
         * @throws Exception\InvalidArgumentException if parameter isn't integer
         */
        abstract function roll($i);
    }     
}
