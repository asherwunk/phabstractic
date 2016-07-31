<?php 

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Queue Class (FIFO Stack)
 * 
 * This file contains the Queue class, a form of a heap.  It implements
 * a First-In-First-Out (FIFO) data structure list of objects.
 * 
 * This queue is based on the "ps stack" class available at PHP Classes 
 *     that I have authored. (phpclasses.org - Asher Wolfstein)
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
    
    $includes = array(// we inherit from AbstractList and implement ListInterface
                      '/Data/Types/Resource/AbstractList.php',
                      // we are configurable
                      '/Features/ConfigurationTrait.php',
                      '/Features/Resource/ConfigurationInterface.php',
                      // returns None on some methods
                      '/Data/Types/None.php',
                      // throws these exceptions
                      '/Data/Types/Exception/RangeException.php',
                      '/Data/Types/Exception/InvalidArgumentException.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types\Resource as TypesResource;
    use Phabstractic\Data\Types\Exception as TypesException;
    use Phabstractic\Features;
    use Phabstractic\Features\Resource as FeaturesResource;
    
    /**
     * Queue Class - Defines a Queue Data Structure
     * 
     * A queue is a basic First In First Out structure.  The
     * The first thing you put in it, is the first thing you get from it.
     * 
     * CHANGELOG
     * 
     * 1.0: Created Queue - May 10th, 2013
     * 1.1: Updated Documentation,
     *          Added Throw Clarification - October 7th, 2013
     * 2.0: Refactored and re-formatted for inclusion
     *          in primus - April 11th, 2015
     * 2.1: Added strict checking to errors and error
     *          proofed roll - April 14th, 2015
     * 3.0: Added and refactored for inclusion in primus2 - August 31st, 2015
     * 3.1: Refined array functions, fixed count function - September 4th, 2015
     * 4.0: reformatted for inclusion in phabstractic - July 21st, 2016
     * 4.0.1: implements configurationinterface - July 31st, 2016
     * 
     * @link http://en.wikipedia.org/wiki/Queue_(data_structure) [English]
     * 
     * @version 4.0.1
     * 
     */
    class Queue extends TypesResource\AbstractList implements
        \ArrayAccess,
        FeaturesResource\ConfigurationInterface
    {
        use Features\ConfigurationTrait;
        
        /**
         * Override the constructor so that we have our own configurations
         * 
         * @param mixed $data The list data
         * @param array $options The class options
         * 
         */
        public function __construct($data = null, $options = array())
        {
            $this->configure($options);
            
            parent::__construct($data);
            
        }
        
        /* The \ArrayAccess member functions
         *
         * NOTE: Indices into this data structure are relative to the 'bottom' of
         *       of the stack.  So an indice of 1 refers to the second item to
         *       the bottom of the stack, indice of 2 one 'above' that, etc.
         */
        
        // The \ArrayAccess member functions
        
        /**
         * Set the offset in the list to the provided value
         * 
         * @param int $key The index to the list item
         * @param mixed $value The value to set to
         * 
         * @return int|Phabstractic\Data\Types\None New number of list elements
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if the value is not a queue index
         * 
         */
        public function offsetSet($key, $value)
        { 
            if (is_numeric($key)) { 
                if ($this->offsetExists($key)) {
                    // incorporate indexing logic
                    $r =& $this->indexReference($key);
                    $r = $value; 
                    return count($this->list);
                }
                
            }
            
            // If the key is empty: queue[], push value
            if (!$key) {
                return $this->push($value);
            }
            
            if ($this->conf->strict) {
                throw new TypesException\RangeException(
                    'Phabstractic\\Data\\Types\\Queue->offsetSet: ' .
                    'offsetSet key ' . $key . ' out of range.');
            }
        }
        
        /**
         * Retrieve the value in the list at the provided index
         * 
         * @param int $key The index to the list item
         * 
         * @return mixed|Phabstractic\Data\Types\None The value at the list index
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if the value is not a queue index
         * 
         */
        public function offsetGet($key)
        { 
            if (!is_numeric($key)) {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Dat\\Type\\Queue->offsetGet: ' .
                        'Invalid Argument Key');
                }
            }
            
            // can also return Types\None
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
        public function offsetUnset($key)
        { 
            if (is_numeric($key)) { 
                if ($key > (count($this->list) - 1) || $key < 0)
                { 
                    return false; 
                } else { 
                    unset($this->list[$key]);
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
         * @param int $key The index into the queue
         * 
         * @return bool Existing?
         * 
         */
        public function offsetExists($key)
        { 
            if (is_numeric($key)) { 
                if ($key > (count($this->list) - 1) || $key < 0) {
                    return false; 
                } else {
                    return isset($this->list[$key]);
                }
                
            }
            
        }
        
        /**
         * Retrieve the 'top' of the list
         * 
         * Note: Does not 'pop' the value off the list
         * 
         * @return mixed|Phabstractic\Data\Types\None 'Top' Value of List otherwise
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException if no top exists
         * 
         */
        public function top()
        { 
            if (!empty($this->list)) {
                $queue = $this->getQueue();
                return $queue[0];
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\Queue->top: ' .
                        'called on empty queue.');
                } else {
                    return new None();
                }
                
            }
            
        }
        
        /**
         * Return the 'top' of the list as a reference
         * 
         * Note: Does not 'pop' the value off the list
         * 
         * @return mixed|Phabstractic\Data\Types\None 'Top' Values as Reference of
         *             List
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException if no top exists
         * 
         */
        public function &topReference() { 
            if (!empty($this->list)) { 
                return $this->list[0]; 
            } else {
                if ($this->conf->strict) {
                   throw new TypesException\RangeException(
                       'Phabstractic\\Data\\Types\\Queue: ' .
                       'top called on empty queue.');
                } else {
                    $none = new None();
                    // have to return reference
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
                $queue = $this->getQueue();
                return $queue[count($queue)-1];
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\Queue->bottom: ' .
                        'called on empty queue.');
                } else {
                    return new None();
                }
                
            }
            
        }
        
        /**
         * Return the 'top' of the list as a reference
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
                       'Phabstractic\\Data\\Types\\Queue: ' .
                       'top called on empty queue.');
                } else {
                    $none = new None();
                    // must return reference, not literal
                    return $none;
                }
                
            }
            
        }
        
        /**
         * Push a value on to the list - LIFO
         * 
         * @return int Count of new list
         * 
         */
        public function push()
        {
            $arguments = func_get_args(); 
            $exec = 'array_push( $this->list'; 
            for ($a = 0; $a < count($arguments); $a++) { 
                $exec .= ', $arguments[' . $a . ']'; 
            } 
             
            $exec .= ' );';
            
            return eval($exec);
        }
        
        /**
         * Push a reference on to the list - LIFO
         * 
         * @return int Count of new list
         * 
         */
        public function pushReference(&$a)
        {
            return $this->list[] = &$a;
        }
        
        /**
         * Returns the 'top' value and pops the value off the list
         * 
         * @return mixed|Phabstractic\Data\Types\None The 'top' value of the list
         * 
         * @throws Exception\RangeException if the top doesn't exist
         * 
         */
        public function pop()
        {
            if (!($this->top() instanceof None)) {
                return array_shift($this->list);
            }
            
            return new None();
        }
        
        /**
         * Returns the 'top' value as reference, pops value off
         * 
         * @return mixed|Phabstractic\Data\Types\None The 'top' value as reference
         *             of the list
         * 
         */
        public function &popReference()
        {
            $return = &$this->topReference();
            
            array_shift($this->list);
            // Can return Types\None()
            return $return;
        }
        
        /**
         * Return the $i'th element of the list
         * 
         * @param integer $i The numerical index into the list
         * 
         * @return mixed|Phabstractic\Data\Types\None The value found at index of
         *             list: list[index]
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              If index is out of well... range.
         * 
         */
        public function index($i)
        {
            if ($i > (count($this->list) - 1) || $i < 0) {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\Queue->index: ' .
                        'out of range');
                } else {
                    return new None();
                }
                
            } else {
                return $this->list[$i];
            }
            
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
         * Return the $i'th element of the list as a reference
         * 
         * @param integer $i The numerical index into the list
         * 
         * @return mixed|Phabstractic\Data\Types\None The value at the list's
         *             numerical index as a reference
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              If index is out of well... range.
         *
         */
        public function &indexReference($i)
        {
            if ($i > (count($this->list) - 1) || $i < 0) {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\Queue->indexReference: ' .
                        'index out of range');
                } else {
                    $none = new None();
                    // must return reference, not literal
                    return $none;
                }
                
            } else {
                return $this->list[$i];
            }
            
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
                        'Phabstractic\\Data\\Types\\Queue->roll: ' .
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
                'list' => $this->list
            ];
        }
    }
    
}
