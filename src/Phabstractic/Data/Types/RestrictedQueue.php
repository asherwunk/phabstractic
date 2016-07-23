<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * This file contains the Stack class, a form of a heap.
 * 
 * A stack is a form of a 'heap'.  It implements a LIFO (for last in, first out)
 * list of objects. This stack conforms to almost all of the PostScript stack
 * manipulators except for mark and cleartomark.
 * 
 * This stack is restricted to particular data types
 * 
 * @see Phabstractic\Data\Types\Resource\AbstractRestrictedList
 * @see Phabstractic\Data\Types\RestrictedList
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
    $includes = array(// we're configurable
                      '/Features/ConfigurationTrait.php',
                      // type check against FilterInterface
                      '/Data/Types/Resource/FilterInterface.php',
                      // inherit from RestrictedList
                      '/Data/Types/RestrictedList.php',
                      // some methods return None
                      '/Data/Types/None.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types\Resource as TypesResource;
    use Phabstractic\Data\Types\Exception as TypesException;
    use Phabstractic\Features;
    
    /**
     * Restricted Stack Class
     * 
     * Defines a Stack Data Structure, but type restricted
     * 
     * Inherits from Falcraft\Data\Types\Resource\AbstractRestrictedList but
     * uses composites an internal Queue and wraps function calls to this object
     * 
     * CHANGELOG
     * 
     * 1.0: Created RestrictedQueue May 16th, 2013
     * 2.0: Refactored and re-formatted for inclusion
     *      in Primus - April 14th, 2015
     * 2.1: Corrected Queue Errors - September 5th, 2015
     * 2.2: Added getList like RestrictedQueue - September 5th, 2015
     * 3.0: inherits from restrictedlist
     *      reformatted for inclusion in phabstractic - July 21st, 2016
     * 
     * @link http://en.wikipedia.org/wiki/Stack_(data_structure) [English]
     * 
     * @version 3.0
     * 
     */
    class RestrictedQueue extends RestrictedList implements \ArrayAccess
    {
        use Features\ConfigurationTrait;
        
        /**
         * The RestrictedStack Constructor
         * 
         * Takes the data, runs it through the restrictions via the parent
         * class if all is successful (nothing is thrown), then it sets up the
         * list data
         * 
         * Options: strict - Do we raise exceptions when values are misaligned?
         * 
         * @param mixed $data The data to populate the internal member array
         * @param Phabstractic\Data\Resource\FilterInterface $restrictions
         *              The predicate type object
         * @param array $options The options for the array
         * 
         */
        function __construct(
            $data = null,
            TypesResource\FilterInterface $restrictions = null,
            $options = array()
        ) {
            $this->configure($options);
            
            parent::__construct($data, $restrictions, $options);
        }
        
        /**
         * Retrieve the top value of the queue
         * 
         * This does not POP the value off the queue
         * 
         * @return mixed
         * 
         */
        public function top()
        {
            if (!empty($this->list))
            {
                // this part is different
                $stack = $this->getQueue();
                return $stack[0];
            } else {
                if ($this->conf->strict) {
                   throw new TypesException\RangeException(
                       'Phabstractic\\Data\\Types\\RestrictedQueue->top: ' .
                       'top called on empty queue.');
                } else {
                    return new None();
                }
                
            }
            
        }
        
        /**
         * Retrieve the top value as a reference
         * 
         * Does not pop the value off the queue
         * 
         * @return mixed|Phabstractic\Data\Types\None if failure
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if list is empty and strict set
         * 
         */
        public function &topReference()
        {
           if (!empty($this->list)) {
                return $this->list[0];
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\RestrictedQueue->topReference: ' .
                        'Empty queue');
                } else {
                    $none = new None();
                    // return value, not literal
                    return $none;
                }
                
            }
        }
        
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
        public function index($i)
        {
            if ($i > (count($this->list) - 1) || $i < 0)
            {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\RestrictedQueue->index: ' .
                        'Index out of range');
                }
                
            } else {
                return $this->list[$i];
            }
            
            return new None();
            
        }
        
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
        public function &indexReference($i)
        {
            if ($i > (count($this->list) - 1) || $i < 0)
            {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\RestrictedQueue->indexReference: ' .
                        'Index out of range');
                }
            } else {
                return $this->list[$i];
            }
            
            $none = new None();
            return $none;
        }
        
        /**
         * Retrieve the 'bottom' of the list
         * 
         * Note: Does not 'shift' the value off the list
         * 
         * @return mixed|Phabstractic\Data\Types\None 'Top' Value of List otherwise
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException if no top exists
         * 
         */
        public function bottom()
        { 
            if (!empty($this->list)) {
                $queue = $this->getQueue();
                return $queue[count($queue)-1];
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\RestrictedQueue->bottom: ' .
                        'called on empty queue.');
                } else {
                    return new None();
                }
                
            }
            
        }
        
        /**
         * Return the 'bottom' of the list as a reference
         * 
         * Note: Does not 'pop' the value off the list
         * 
         * @return mixed|Phabstractic\Data\Types\None 'Top' Values as Reference of
         *             List
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException if no top exists
         * 
         */
        public function &bottomReference() { 
            if (!empty($this->list)) { 
                return $this->list[count($this->list)-1]; 
            } else {
                if ($this->conf->strict) {
                   throw new TypesException\RangeException(
                       'Phabstractic\\Data\\Types\\RestrictedQueue->bottomReference: ' .
                       'bottom called on empty queue.');
                } else {
                    $none = new None();
                    // must return reference, not literal
                    return $none;
                }
                
            }
            
        }
        
        /**
         * Returns the 'top' value and pops the value off the list
         * 
         * @return mixed The 'top' value of the list
         * 
         */
        public function pop() {
            if (!empty($this->list)) {
                return array_shift($this->list);
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\RestrictedList->pop: ' .
                        'called on empty stack.');
                } else {
                    return new None();
                }
            }
        } 
        
        /**
         * Returns the 'top' value as reference, pops value off
         * 
         * @return mixed The 'top' value as reference of the list
         * 
         */
        public function &popReference() {
            $return = &$this->topReference();
            array_shift($this->list);
            // Can return Types\None
            return $return;
        }
        
        /**
         * Returns the queue as an array
         * 
         * Note: Returns internal array
         * 
         * @return array The internal queue array
         * 
         */
        public function getQueue()
        {
            return $this->list;
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
            // http://php.net/manual/en/function.array-unshift.php#40270
            array_unshift($this->list, '');
            $this->list[0] = &$m1;
            array_unshift($this->list, '');
            $this->list[0] = &$m2;
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
            array_unshift($this->list, $this->top());
            return $this;
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
         * NOTE: May not be object friendly, depending on array
         * 
         * @param int $i The amount to roll the list
         * 
         * @throws Phabstractic\Data\Types\Exception\InvalidArgumentException
         *              If argument isn't integer
         * 
         */
        public function roll($c)
        {
            if (!is_int($c)) {
                if ($this->conf->strict) {
                    throw new TypesException\InvalidArgumentException(
                        'Phabstractic\\Data\\Types\\RestrictedQueue->roll: ' .
                        'argument not integer');
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
            
            // rotate right
            if ($c > 0) { 
                $this->list = array_merge(array_slice($this->list, $c), 
                                          array_slice($this->list, 0, $c)); 
            } else if ( $c < 0 ) {
                // rotate left
                $c = abs($c);
                $this->list = array_merge(array_slice($this->list, $l - $c),
                                          array_slice($this->list, 0, $l - $c)); 
            }
            
            return true;
        }
        
    }
    
}
