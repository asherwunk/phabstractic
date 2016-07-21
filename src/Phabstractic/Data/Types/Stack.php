<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * This file contains the Stack class
 * 
 * A stack is a form of a 'heap'.  It implements a LIFO (for last in, first out)
 * list of objects. This stack conforms to almost all of the PostScript stack
 * manipulators except for mark and cleartomark.  It is modified from its 
 * riginal design to eliminate the superfluous $index variable.
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
    
    $includes = array(// inherits AbstractList (which implements ListInterface)
                      '/Data/Types/Resource/AbstractList.php',
                      // is configurable
                      '/Features/ConfigurationTrait.php',
                      // returns None on some methods
                      '/Data/Types/None.php',
                      // throws these exceptions
                      '/Data/Types/Exception/RangeException.php',
                      '/Data/Types/Exception/InvalidArgumentException.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types\Resource as TypesResource;
    use Phabstractic\Data\Types\Exception as TypesException;
    use Phabstractic\Features;
    
    /**
     * Stack Class - Defines a Stack (heap) Data Structure
     * 
     * A stack is a basic Last In First Out structure.  The
     * The last thing you put in it, is the first thing you get from it.
     * 
     * This implementation takes inspiration from the PostScript stack
     * and includes a couple of member functions that implement common
     * PostScript stack behavior.
     * 
     * @TODO add bottom methods
     * 
     * CHANGELOG
     * 
     * 1.0: Created Stack - May 10th, 2013
     * 2.0: Refactored and re-formatted for inclusion
     *      inclusion in Primus - April 14th, 2015
     * 3.0: reformatted for inclusion in phabstractic - July 21st, 2016
     * 
     * @link http://en.wikipedia.org/wiki/Stack_(data_structure) [English]
     * 
     * @version 3.0
     * 
     */
    class Stack extends TypesResource\AbstractList implements \ArrayAccess 
    {
        use Features\ConfigurationTrait;
        
        /**
         * Stack constructor
         * 
         * options - strict: throw errrors
         * 
         * @param mixed $data the data to initialize the list with
         * @param array $options The options to give the object
         * 
         */
        public function __construct($data = null, $options = array())
        {
            $this->configure($options);
            
            parent::__construct($data);
        }
        
        /* The \ArrayAccess member functions
         *
         * NOTE: Indices into this data structure are relative to the 'top' of
         *       of the stack.  So an indice of 1 refers to the second item to
         *       the top of the stack, indice of 2 one 'below' that, etc.
         */
        
        /**
         * Set the offset in the list to the provided value
         * 
         * @param int $key The index to the list item
         * @param mixed $value The value to set to
         * 
         * @return intNew number of list elements
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if the value is not a stack index
         * 
         */
        public function offsetSet($key, $value)
        {
            if (is_numeric($key)) {
                if ($this->offsetExists($key)) {
                    // encapsulate indexing logic in indexing function
                    $r = &$this->indexReference($key);
                    $r = $value;
                    return count($this->list);
                }
                
            }
            
            // If the key is empty: stack[], push value
            if (!$key) {
                return $this->push($value);
            }
            
            // At this point, we should have returned, or something is wrong
            // Such as treating the stack as an associative variable
            
            if ($this->conf->strict) {
                throw new TypesException\RangeException(
                    'Phabstractic\\Data\\Types\\Stack->offsetSet: offsetSet key ' .
                    $key . ' out of range.');
            }
        } 
        
        /**
         * Retrieve the value in the list at the provided index
         * 
         * @param int $key The index to the list item
         * 
         * @return mixed The value at the list index
         * 
         * @throws Phabstractic\Data\Types\Exception\InvalidArgumentException
         *              if the value is not a stack index
         * 
         */
        public function offsetGet($key)
        {
            if (!is_numeric($key)) {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\Stack->offsetGet: ' .
                        'Invalid Argument Key');
                }
                
            }
            
            // encapsulates the indexing logic in the index method
            return $this->index($key);
        } 
        
        /**
         * Unset the index and value on the list
         * 
         * Note: Like the unset method, this throws no error if the index
         *          doesn't exist.
         * 
         * @param int $key The index to the list item
         * 
         * @return bool False if the index is improper, or not numeric, true
         *              otherwise
         * 
         */
        public function offsetUnset($key)
        { 
            if (is_numeric($key)) {
                // is this out of range?
                if ($key > ($l = count($this->list) - 1) || $key < 0) { 
                    return false;
                } else {
                    unset($this->list[$l - $key]);
                    // reset keys
                    $this->list = array_merge($this->list, array());
                    return true;
                }
                
            }
            
            return false;
        } 
        
        /**
         * Does the given key exist in the list?
         * 
         * Note: This method also returns false if the key is out of range
         * 
         * @param int $key The index into the stack
         * 
         * @return bool Existing?
         * 
         */
        public function offsetExists($key)
        {
            if (is_numeric($key)) {
                // is this out of range?
                if ($key > ($l = count($this->list) - 1) || $key < 0) {
                    return false;
                } else {
                    return isset($this->list[$l - $key]);
                }
                
            }
            
        } 
        
        /**
         * Retrieve the 'top' of the list
         * 
         * Returns a None object if the list is empty and strict is false
         * 
         * Note: Does not 'pop' the value off the list
         * 
         * @return mixed|Phabstractic\Data\Types\None 'Top' Value of List
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if no top exists
         * 
         */
        public function top()
        {
            if (!empty($this->list))
            {
                $stack = $this->getStack();
                return $stack[0];
            } else {
                if ($this->conf->strict) {
                   throw new TypesException\RangeException(
                       'Phabstractic\\Data\\Types\\Stack->top: ' .
                       'top called on empty stack.');
                } else {
                    return new None();
                }
                
            }
            
        } 
        
        /**
         * Return the 'top' of the list as a reference
         * 
         * Returns a None object if the list is empty and strict is false
         * 
         * Note: Does not 'pop' the value off the list
         * 
         * @return mixed|Phabstractic\Data\Types\None 'Top' Values as Reference of
         *              List
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              when called on empty stack
         * 
         */
        public function &topReference()
        {
            if (!empty($this->list)) { 
                return $this->list[count($this->list) - 1]; 
            } else {
               if ($this->conf->strict) {
                   throw new TypesException\RangeException(
                       'Phabstractic\\Data\\Types\\Stack->topReference: ' .
                       'top called on empty stack.');
               } else {
                   // must return a reference to an object
                   $none = new None();
                   return $none;
               }
               
            }
            
        }
    
        /**
         * Push a value on to the list (lifo)
         * 
         * This pushes a value on to the 'top' of the stack
         * 
         * This is a variadic method, and may accept multiple arguments
         * 
         * @return int Count of new list
         * 
         */
        public function push()
        {
            // variadic function
            $args = func_get_args(); 
            $exec = 'array_push( $this->list'; 
            for ($a = 0; $a < count($args); $a++) { 
                $exec .= ', $args[' . $a . ']'; 
            } 
             
            $exec .= ' );'; 
            return eval($exec);
        } 
         
         /**
         * Push a reference on to the list (lifo)
         * 
         * This pushes a value on to the 'top' of the stack
         * 
         * This is NOT a variadic method, and only accepts one argument
         * 
         * @return int Count of new list
         * 
         */
        public function pushReference(&$a)
        {
            return $this->list[] =& $a; 
        } 
    
        /**
         * Returns the 'top' value and pops the value off the list
         * 
         * @return mixed The 'top' value of the list (None if list is empty)
         * 
         */
        public function pop()
        {
            if (!($this->top() instanceof None)) {
                return array_pop($this->list);
            }
            
            return new None();
        }
        
        /**
         * Returns the 'top' value as reference, pops value off
         * 
         * @return mixed The 'top' value as reference of the list, None if list empty
         * 
         */
        public function &popReference()
        {
            $return = &$this->topReference();
            
            array_pop($this->list);
            // can return Types\None
            return $return;
        }
        
        /**
         * Exchange the two top elements of the list
         * 
         * @return Phabstractic\Data\Types\Resource\AbstractList For chaining
         * 
         */
        public function exchange() { 
            $m1 = &$this->popReference(); 
            $m2 = &$this->popReference();
            $this->list[] = &$m1; 
            $this->list[] = &$m2;
            return $this;
        }
        
        /**
         * Duplicate the value at the top of the list
         * 
         * Note: Adds the duplicate to the front of the list
         * 
         * @return Phabstractic\Data\Types\Resource\AbstractList For chaining
         * 
         */
        public function duplicate() {
            $this->push($this->top());
            return $this;
        }
        
        /**
         * Return the $i'th element of the list
         * 
         * NOTE: With a stack we start counting at the 'top' of the stack
         *       0 is the top of the stack, 1, the next, and so on
         * 
         * @param integer $i The numerical index into the list
         * 
         * @return mixed|Phabstractic\Data\Types\None
         *          The value found at index of list: list[index]
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              If index is out of well... range.
         * 
         */
        public function index($i)
        {
            if ($i > ($l = count($this->list) - 1) || $i < 0)
            {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\Stack->index: ' .
                        'index out of range');
                }
                
            } else {
                return $this->list[$l - $i];
            }
            
            return new None();
            
        }
        
        /**
         * Return the $i'th element of the list as a reference
         * 
         * NOTE: With a stack we start counting at the 'top' of the stack
         *       0 is the top of the stack, 1, the next, and so on
         * 
         * @param integer $i The numerical index into the list
         * 
         * @return mixed The value at the list's numerical index as a reference
         * 
         * @throws Exception\RangeException If index is out of well... range.
         *
         */
        public function &indexReference($i)
        {
            if ($i > ($l = count($this->list) - 1) || $i < 0)
            {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\Stack->indexReference: ' .
                        'index out of range');
                }
            } else {
                return $this->list[$l - $i];
            }
            
            $none = new None();
            return $none;
        }
    
        /**
         * Returns the stack as an array
         * 
         * NOTE: This reverses the array, so that the top of the stack is
         *       element 0, then 1, etc.
         * 
         * Note: Returns internal array
         * 
         * @return array The internal stack array
         * 
         */
        public function getStack()
        {
            return array_reverse($this->list); 
        }
        
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
         * 12345 might become 34512
         * 
         * @param int $i The amount to roll the list
         * 
         * @throws Phabstractic\Data\Types\Exception\InvalidArgumentException
         *              if argument isn't integer
         * 
         */
        public function roll($c)
        {
            if (!is_int($c)) {
                if ($this->conf->strict) {
                    throw new TypesException\InvalidArgumentException(
                        'Phabstractic\\Data\\Types\\Stack->roll: ' .
                        'invalid argument to roll');
                } else {
                    return false;
                }
                
            }
            
            if (abs($c) > ($l = count($this->list))) {
                if ($c < 0) {
                    $c = (abs($c) % $l) * -1;
                } else {
                    $c %= $l;
                }
                
            }
            
            if ($c > 0) { // rotate right
                $this->list = array_merge(array_slice($this->list, $l - $c),
                                          array_slice($this->list, 0, $l - $c));
            } else if ($c < 0) { // rotate left
                $c = abs($c);
                $this->list = array_merge(array_slice($this->list, $c),
                                          array_slice($this->list, 0, $c));
            }
            
        }
        
    }
    
}
