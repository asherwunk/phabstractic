<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Lexicographic List - (Alphabetical)
 * 
 * This file contains the LexicographicList class.  This class uses the
 * AbstractSortedList to sort the list, which in turn uses the
 * AbstractRestrictedList to limit the list to objects that are strings.
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
    
    $includes = array(// this is configurable
                      '/Features/ConfigurationTrait.php',
                      // some methods return None
                      '/Data/Types/None.php',
                      // we inherit from the sorted list
                      '/Data/Types/Resource/AbstractSortedList.php',
                      // we use a supplied filter (restrictions)
                      '/Data/Types/Resource/FilterInterface.php',
                      // instantiate string only restrictions on default
                      '/Data/Types/Restrictions.php',
                      '/Data/Types/Type.php',
                      // we throw these errors
                      '/Data/Types/Exception/InvalidArgumentException.php',
                      '/Data/Types/Exception/RuntimeException.php',
                      '/Data/Types/Exception/RangeException.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Features;
    use Phabstractic\Data\Types\Exception as TypesException;
    use Phabstractic\Data\Types\Resource as TypesResource;
    use Phabstractic\Data\Types\Type;
    
    /**
     * Lexicographic List Class - Implements a Lexicographically Sorted List
     * 
     * This is a list that automatically sorts itself according to
     * alphabetical standards.
     * 
     * This object relies on AbstractSortedList and provides the sort
     * algorithm required
     * 
     * CHANGELOG
     * 
     * 1.0: Created LexicographicList - May 27th, 2013
     * 2.0: Refactored and re-formatted for inclusion
     *          in primus - April 12th, 2015
     * 3.0: allowed cmp function to check against restrictions
     *      reformatted for inclusion in phabstractic - July 21st, 2016
     * 
     * @version 3.0
     * 
     */
    class LexicographicList extends TypesResource\AbstractSortedList implements
        \ArrayAccess
    {
        use Features\ConfigurationTrait;
        
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
                                'Phabstractic\\Data\\Types\\LexicographicList->offsetSet: ' .
                                'Value not in restrictions' );
                        } else {
                            return null;
                        }
                    }
                    // encapsulate indexing logic in indexing function
                    $r = &$this->indexReference($key);
                    $r = $value;
                    usort($this->list, array($this, 'cmp'));
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
                    'Phabstractic\\Data\\Types\\LexicographicList->offsetSet: ' .
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
         */
        public function offsetGet($key) {
            usort($this->list, array($this, 'cmp'));
            
            if (!is_numeric($key)) {
                if ($this->conf->strict) {
                    throw new Exception\InvalidArgumentException(
                        'Phabstractic\\Data\\Types\\LexicographicList->offsetGet: ' .
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
            usort($this->list, array($this, 'cmp'));
            
            if (!is_numeric($key)) {
                return false;
            } else {
                unset($this->list[$key]);
                // reset keys
                $this->list = array_merge($this->list, array());
                usort($this->list, array($this, 'cmp'));
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
            usort($this->list, array($this, 'cmp'));
            
            if (!is_numeric($key)) {
                return false;
            } else {
                return isset($this->list[$key]);
            }
        }
        
        /**
         * Compare Two Strings, this is used by AbstractSortedList to sort the
         * elements of the list
         * 
         * @param string $l The first value to compare
         * @param string $r The second value to compare
         * 
         * @return int The required comparison results.
         * 
         * @throws \InvalidArgumentException if the values to compare are not
         *                                   of type string
         * 
         */
        protected function cmp( $l, $r )
        {
            // check against restrictions, v3
            if (!$this->restrictions->isAllowed(Type\getValueType($l)) ||
                    !$this->restrictions->isAllowed(Type\getValueType($r))) {
                throw new TypesException\InvalidArgumentException(
                    'Phabstractic\\Data\\Types\\LexicographicalList->cmp: ' .
                    'Comparison types not allowed');
            }
            
            // version 2:
            /* if (!is_string($l) || !is_string($r)) {
                throw new Exception\InvalidArgumentException(
                    'LexicographicalList->cmp: Comparison must be string');
            } */
            
            if ($l > $r) {
                return 1;
            } else if ($l < $r) {
                return -1;
            } else {
                return 0;
            }
            
        }
        
        /**
         * The Lexicographic List constructor
         * 
         * Accepts data, and the obligatory options parameter
         * 
         * Passes the required restrictions onto the parent class along with
         * the options, the default restrictions limit the data to basic string
         * but can be overriden with the restrictions parameter
         * 
         * This instantiates the class and sets the index
         * 
         * options - strict - do we report errors?
         * 
         * @param mixed $data The data to initialize the queue
         * @param Phabstractic\Data\Types\Resource\FilterInterface $restrictions
         * @param array $options The options to pass into the object
         * 
         */
        public function __construct(
            $data = null,
            TypesResource\FilterInterface $restrictions = null,
            $options = array()
        ) {
            $this->configure($options);
            
            if (!$restrictions) {
               $this->restrictions = new Restrictions(array(Type::BASIC_STRING));
            } else {
                $this->restrictions = $restrictions;
            }
            
            parent::__construct($data, $this->restrictions, $options);
        }
        
        /**
         * Returns the top value
         * 
         * Does not pop the value off the list
         * 
         * @return string|Phabstractic\Data\Types\None 'Top' value of list otherwise
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if no top exists
         * 
         */
        public function top()
        {
            if (!empty($this->list)) {
                $queue = $this->getList();
                return $queue[0];
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\LexicographicList->top: ' .
                        'called on empty list');
                } else {
                    return new None();
                }
                
            }
            
        }
        
        /**
         * Returns the top value as a reference
         * 
         * Does not pop the value off the list
         * 
         * @return string|Phabstractic\Data\Types\None
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if top is empty
         * 
         */
        public function &topReference()
        {
            parent::topReference();
            
            if (!empty($this->list)) { 
                return $this->list[0]; 
            } else {
                if ($this->conf->strict) {
                   throw new Exception\RangeException(
                       'Phabstractic\\Data\\Types\\LexicographicList: ' .
                       'top called on empty list');
                } else {
                    return new None();
                }
                
            }
        }
        
        /**
         * Wrapper function for top()
         * 
         * @return mixed
         * 
         */
        public function peek()
        {
            return $this->top();
        }
        
        /**
         * Wrapper function for topReference()
         * 
         * @return mixed
         * 
         */
        public function &peekReference()
        {
            return $this->topReference();
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
            usort($this->list, array($this, 'cmp'));
            
            if (!empty($this->list)) {
                return $this->list[count($this->list)-1];
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\LexicographicList->bottom: ' .
                        'called on empty list');
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
            usort($this->list, array($this, 'cmp'));
            
            if (!empty($this->list)) { 
                return $this->list[count($this->list)-1];
            } else {
                if ($this->conf->strict) {
                   throw new TypesException\RangeException(
                       'Phabstractic\\Data\\Types\\LexicographicList->' .
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
         * This automatically sorts the list after all other requirements
         * have been met
         * 
         * Remember AbstractRestrictedList is the parent of this abstract class
         * 
         * @return int|null Count of new list, null if restrictions not met
         * 
         */
        public function push() {
            $args = func_get_args();
            $exec = 'if ( parent::push( ';
            for ($a = 0; $a < count( $args ); $a++) {
                if ($a) {
                    $exec .= ', ';
                }
                
                $exec .= "\$args[$a] ";
            }
            
            $exec .= " ) ) { \array_push( \$this->list, ";
            for ($a = 0; $a < count( $args ); $a++) {
                if ($a) {
                    $exec .= ", ";
                }
                
                $exec .= "\$args[$a] ";
            }
            
            $exec .= " ); }";
            return eval($exec);
        }
        
        /**
         * Push a reference on to the list (fifo, lifo, etc)
         * 
         * This automatically sorts the list after all other requiresments have been met
         * 
         * @return int|null Count of new list, null if restrictions are not met
         * 
         */
        public function pushReference( &$a ) {
            if (parent::push($a)) {
                $this->list[] =& $a;
                return count($this->list);
            }
            
            $none = new None();
            return $none;
        }
        
        /**
         * Returns the data objects specified by the index request
         * 
         * Because this doesn't set references, the code had to be duplicated
         * 
         * @param int $i the given index
         * 
         * @return string|Phabstractic\Data\Types\None
         * 
         * @throws Phabstractic\Data\Types\Exception\RangException
         *              if index is out of range
         * 
         */
        public function index($i)
        {
            parent::index($i);
            
            if (!isset($this->list[$i])) {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\LexicographicList->index: ' .
                        'out of range');
                } else {
                    return new None();
                }
                
            } else {
                return $this->list[$i];
            }
            
        }
        
        /**
         * Returns the indexed data items
         * 
         * @param int $i the given index
         * 
         * @return string|Phabstractic\Data\Types\None
         * 
         * @throws Phabstractic\Data\Types\Exception\RangException
         *              if index is out of range
         * 
         */
        public function &indexReference($i)
        {
            parent::index($i);
            
            if (!isset($this->list[$i])) {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\LexicographicList->index: ' .
                        'out of range');
                } else {
                    $none = new None();
                    // must return value, not literal
                    return $none;
                }
                
            } else {
                return $this->list[$i];
            }
            
        }
        
        /**
         * Pop the item off the list
         * 
         * This truncates the list from the top, and returns its value
         * 
         * @return string|Falcraft\Data\Types\Null
         * 
         */
        public function pop()
        {
            parent::pop();
            
            $top = $this->top();
            if (!($top instanceof None)) {
                return array_shift($this->list);
            }
            
            return new None();
        }
        
        /**
         * Pop the item reference off the list as a reference
         * 
         * This truncates the list from the top, and returns its value as a
         * reference
         * 
         * @return string|Falcraft\Data\Types\Null
         * 
         */
        public function &popReference()
        {
            parent::popReference();
            
            $top = &$this->topReference();
            if (!($top instanceof None)) {
                array_shift($this->list);
                return $top;
            }
            
            $none = new None();
            // have to return value, not literal
            return $none;
        }
        
        /**
         * Exchange the two top elements of the list
         * 
         * @throws Phabstractic\Data\Types\Exception\RuntimeException
         * 
         */
        public function exchange() { 
            throw new TypesException\RuntimeException(
                'Phabstractic\\Data\\Types\\LexicographicList->exchange: ' .
                'Cannot exchange values');
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
         * Cannot roll a lexicographic list
         * 
         * @throws Exception\RuntimeException
         * 
         */
        public function roll( $i )
        {
            throw new TypesException\RuntimeException(
                'Phabstractic\\Data\\Types\\LexicographicList->roll: ' .
                'Cannot roll values');
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
                'list' => $this->list,
            ];
        }
    }
    
}
