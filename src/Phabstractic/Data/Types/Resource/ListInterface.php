<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * List Interface
 * 
 * This file contains the ListInterface, which extends the \Countable interface
 * This defines basic list like operators that other modules and classes can depend on
 * even if the list is a stack, a queue, a ring buffer, etc.
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
     * List Interface - Defines basic list functions
     * 
     * A list can be several things: a stack, a queue, a ring buffer, etc.
     * This list interface defines basic list operators that would exist on 
     * any list, be it a stack, a queue, a ring buffer, etc.
     * 
     * CHANGELOG
     * 
     * 1.0: Created ListInterface - May 16th, 2013
     * 2.0: Refactored and re-formatted for inclusion in
     *          primus - April 11th, 2015
     * 3.0: reformatted for inclusion in phabstractic - July 21st, 2016
     * 
     * @link http://en.wikipedia.org/wiki/Stack_(data_structure) [English]
     * 
     * @version 3.0
     * 
     */
    interface ListInterface extends \Countable {
        
        /**
         * Return the 'top' of the list (lifo/fifo/etc)
         * 
         * Does not 'pop' the value off of the list
         * 
         * @return mixed The top value of the list
         * 
         */ 
        public function top(); 
        
        /**
         * Return 'top' value as a reference
         * 
         * @see top()
         * 
         * @return mixed The top value as a reference of the list
         * 
         */ 
        public function &topReference();
        
        /**
         * 'Pushes'/Inserts a value into the list
         * 
         * However that is in implementation (lifo/fifo/etc)
         * 
         * @param $data,... Variable length arguments to push
         * 
         * @return int Number of values in list
         * 
         */
        public function push();
        
        /**
         * 'Pushes'/Inserts a value as a reference into the list
         * 
         * However that is in implementation (lifo/fifo/etc)
         * 
         * @param &$data Reference to push onto the stack
         * 
         * @return int Number of values in list
         * 
         */
        public function pushReference(&$a); 
        
        /**
         * 'Pops'/Takes first value from list, and removes it from list
         * 
         * However that is in implementation (lifo/fifo/etc)
         * 
         * @return mixed The 'first' value
         * 
         */
        public function pop();
        
        /**
         * 'Pops'/Takes first value from list, and removes it from list
         * 
         * However that is in implementation (lifo/fifo/etc)
         * 
         * @return mixed The 'first' value as a reference
         * 
         */
        public function &popReference(); 
        
        /**
         * Accesses a specific index in the 'list'
         * 
         * This should be used carefully, as implementation varies
         * 
         * @return mixed The value at the index
         * 
         */
        public function index($i);
        
        /**
         * Accesses the specific index in the 'list'
         * 
         * @see index( $i )
         * 
         * @return mixed The value as a reference at the index
         * 
         */
        public function &indexReference($i); 
        
        /**
         * Implements \Countable Interface Method
         * 
         * @return int The number of current elements in the list
         * 
         */
        public function count();
        
        /**
         * Is the list empty?
         * 
         * @return bool True if the list is empty
         */
        public function isEmpty();
        
        /**
         * Clears all values out of the list
         * 
         * Be careful!
         * 
         */
        public function clear();
        
        /**
         * Returns the potentially internal list, usually array
         * 
         * Implementation varies return value
         * 
         * @return mixed The internal list variable
         * 
         */
        public function getList();
        
    }
    
}
