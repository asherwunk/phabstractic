<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Restricted List - Concrete
 * 
 * This file contains a simple restricted list
 * 
 * @see Phabstractic\Data\Types\Resource\AbstractRestrictedList
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
                      // we inherit from AbstractRestrictedList
                      '/Data/Types/Resource/AbstractRestrictedList.php',
                      // we type check against FilterInterface
                      '/Data/Types/Resource/FilterInterface.php',
                      // some methods return none
                      '/Data/Types/None.php',
                      // we throw the following
                      '/Data/Types/Exception/InvalidArgumentException.php',
                      '/Data/Types/Exception/RangeException.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types\Resource as TypesResource;
    use Phabstractic\Data\Types\Exception as TypesException;
    use Phabstractic\Features;
    
    /**
     * Restricted List Class - Defines a basic list class with restrictions
     * 
     * Inherits from AbstractRestrictedList
     * 
     * CHANGELOG
     * 
     * 1.0: Created RestrictedList September 25th, 2013
     * 2.0: Refactored and re-formatted for inclusion
     *          in primus - April 11th, 2015
     * 2.1: Added additional strict conf checking - April 14th, 2015
     * 2.2: Changed offsetSet for better error cascades - April 14th, 2015
     * 3.0: reformatted for inclusion in phabstractic - July 21st, 2016
     * 
     * @uses Phabstractic\Data\Types\FList\AbstractRestrictedList
     * 
     * @version 3.0
     * 
     */
    class RestrictedList extends TypesResource\AbstractRestrictedList implements
        \ArrayAccess
    {
        use Features\ConfigurationTrait;
        
        /**
         * The RestrictedList Constructor
         * 
         * Takes the data, runs it through the restrictions via the parent class
         * if all is successful (nothing is thrown), creates the composited object
         * 
         * Options: strict - Do we raise exceptions when values are misaligned?
         * 
         * @param mixed $data The data to populate the internal member array
         * @param Phabstractic\Data\Types\Resource\FilterInterface $restrictions
         *            The predicate type object
         * @param array $options The options for the stack
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
        
        // The \ArrayAccess member functions
        
        /**
         * Set the offset in the list to the provided value
         * 
         * @param int $key The index to the list item
         * @param mixed $value The value to set to
         * 
         * @return int|null
         * 
         * @throws Phabstractic\Data\Types\Exception\InvalidArgumentException
         *              if the value doesn't meet restrictions
         * 
         */
        public function offsetSet($key, $value)
        {
            if (is_numeric($key)) {
                if ($this->offsetExists($key)) {
                    if (!$this->check($value)) {
                        if ($this->conf->strict) {
                            throw new TypesException\InvalidArgumentException(
                                'Phabstractic\\Data\\Types\\RestrictedList->offsetSet: ' .
                                'Value not in restrictions' );
                        } else {
                            return null;
                        }
                    }
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
                    'Phabstractic\\Data\\Types\\RestrictedList->offsetSet: offsetSet key ' .
                    $key . ' out of range.');
            }
            
        }
        
        /**
         * Retrieve the value in the list at the provided index
         * 
         * @param int $key The index to the list item
         * 
         * @return mixed|Phabstractic\Data\Types\None The value at the list index
         * 
         */
        public function offsetGet($key) {
            if (!is_numeric($key)) {
                if ($this->conf->strict) {
                    throw new Exception\InvalidArgumentException(
                        'Phabstractic\\Data\\Types\\RestrictedList->offsetGet: ' .
                        'Invalid argument key');
                } else {
                    return new None();
                }
                
            }
            
            // Can return Types\Null()
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
         * @return bool False if the index is improper, or not numeric, true otherwise
         * 
         */
        public function offsetUnset($key) {
            if (!is_numeric($key)) {
                return false;
            } else {
                unset($this->list[$key]);
                // reset keys
                $this->list = array_merge($this->list, array());
                return true;
            }
            
            return false;
        } 
        
        /**
         * Does the given key exist in the list?
         * 
         * Note: This method also returns false if the key is out of range
         * 
         * @param int $key The index into the queue
         * 
         * @return bool Existing?
         * 
         */
        public function offsetExists( $key ) {
            if (!is_numeric($key)) {
                return false;
            } else {
                return isset($this->list[$key]);
            }
        }
    
        /**
         * Retrieve the top value
         * 
         * Does not pop the value off the stack
         * 
         * @return mixed|Phabstractic\Data\Types\None if failure
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if list is empty and strict set
         * 
         */
        public function top()
        {
            if (!empty($this->list)) {
                return $this->list[count($this->list)-1];
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\RestrictedList->top: ' .
                        'Empty list');
                } else {
                    return new None();
                }
                
            }
        }
        
        /**
         * Retrieve the top value as a reference
         * 
         * Does not pop the value off the stack
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
                return $this->list[count($this->list)-1];
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\RestrictedList->topReference: ' .
                        'Empty list');
                } else {
                    $none = new None();
                    // return value, not literal
                    return $none;
                }
                
            }
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
                return $this->list[0];
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\RestrictedList->bottom: ' .
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
                return $this->list[0];
            } else {
                if ($this->conf->strict) {
                   throw new TypesException\RangeException(
                       'Phabstractic\\Data\\Types\\RestrictedList: ' .
                       'bottom called on empty queue.');
                } else {
                    $none = new None();
                    // must return reference, not literal
                    return $none;
                }
                
            }
            
        }
        
    
        /**
         * Push a value on to the list
         * 
         * @return int|null Count of new list, Null if restrictions not met
         * 
         * @throws \InvalidArgumentException If strict is enabled and value
         *             not in restrictions
         * 
         */
        public function push()
        {
            $arguments = func_get_args();
            $exec = 'if ( parent::push( ';
            for ($a = 0; $a < count( $arguments ); $a++) {
                if ($a) {
                    $exec .= ", ";
                }
                
                $exec .= "\$arguments[$a] ";
            }
            
            $exec .= ' ) ) { return array_push( $this->list, ';
            for ($a = 0; $a < count( $arguments ); $a++) {
                if ($a) {
                    $exec .= ", ";
                }
                
                $exec .= "\$arguments[$a] ";
            }
            
            $exec .= ' ); } else { return null; }';  
            return eval($exec);
        }
        
        /**
         * Push a reference on to the list (fifo, lifo, etc)
         * 
         * remember that AbstractRestrictedList is the parent object and holds
         * the filtering logic.
         * 
         * @return int|null Count of new list, Null if restrictions are not met
         * 
         */
        public function pushReference( &$a ) {
            $args = func_get_args();
            if (parent::pushReference($a)) {
                $this->list[] = &$a;
                return count($this->list);
            }
            
            return null;
        }

        /**
         * Returns the 'top' value and pops the value off the list
         * 
         * @return mixed The 'top' value of the list
         * 
         */
        public function pop() {
            if (!empty($this->list)) {
                return array_pop($this->list);
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
            array_pop($this->list);
            // Can return Types\None
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
         * @param integer $i The numerical index into the list
         * 
         * @return mixed|Phabstractic\Data\Types\None The value found at index of
         *              list: list[index]
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              If index is out of well... range.
         * 
         */
        public function index( $i ) {
            if (isset($this->list[$i])) {
                return $this->list[$i];
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\RestrictedList->index: ' .
                        'out of range');
                } else {
                    return new None();
                }
                
            }
            
        } 
        
        /**
         * Return the $i'th element of the list as a reference
         * 
         * @param integer $i The numerical index into the list
         * 
         * @return mixed|Phabstractic\Data\Types\None The value at the list's
         *              numerical index as a reference
         * 
         * @throws \RangeException If index is out of well... range.
         *
         */
        public function &indexReference( $i ) { 
            if (isset($this->list[$i])) {
                return $this->list[$i];
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\RestrictedList->index: ' .
                        'out of range');
                } else {
                    $none = new None();
                    // return value, not literal
                    return $none;
                }
                
            }
            
        }
    
        /**
         * Clear all the values from the list
         * 
         * IMPLEMENT: ListInterface
         * 
         */
        public function clear()
        {
            $this->list = array();
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
         */
        public function roll( $c ) {
            if (!is_int($c)) {
                if ($this->conf->strict) {
                    throw new TypesException\InvalidArgumentException(
                        'Phabstractic\\Data\\Types\\RestrictedList->roll: ' .
                        'Invalid argument to roll');
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
        
        /**
         * Debug Info (var_dump)
         * 
         * Display debug info
         * 
         * Requires PHP 5.6+
         * 
         */
        public function __debugInfo()
        {
            return [
                'options' => array('strict' => $this->conf->strict,),
                'restrictions' => $this->restrictions,
                'list' => $this->list,
            ];
        }
        
    }
    
}
