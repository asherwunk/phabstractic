<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Linked List Class
 * 
 * This list offers us a chance to have a sequence of data tied together purely
 * by references.  This means that it does NOT utilize an array or array
 * operations, but instead relies on elements that connect to each other in a
 * sequential fashion.  This allows us to modify the contents of the list using
 * the elements themselves, or at least their references.
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
    
    // For object configuration purposes
    $includes = array(// we are configurable
                      '/Features/ConfigurationTrait.php',
                      '/Features/Resource/ConfigurationInterface.php',
                      // we inherit from AbstractLinkedList
                      '/Data/Types/Resource/AbstractLinkedList.php',
                      // we implement LinkedListInterface
                      '/Data/Types/Resource/LinkedListInterface.php',
                      // we typecheck against LinkedListElement
                      '/Data/Types/LinkedListElement.php',
                      // we throw the following exceptions
                      '/Data/Types/Exception/RangeException.php',
                      '/Data/Types/Exception/OutOfRangeException.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Features;
    use Phabstractic\Features\Resource as FeaturesResource;
    use Phabstractic\Data\Types\Resource as TypesResource;
    use Phabstractic\Data\Types\Exception as TypesException;
    
    /**
     * * Linked List Class
     * 
     * This list offers us a chance to have a sequence of data tied together purely
     * by references.  This means that it does NOT utilize an array or array
     * operations, but instead relies on elements that connect to each other in a
     * sequential fashion.  This allows us to modify the contents of the list using
     * the elements themselves, or at least their references.
     * 
     * This extends the abstract version with some special PHP functionality
     * including __debugInfo, ArrayAccess methods, Countable, and Iteration over the list.
     * 
     * CHANGELOG
     * 
     * 1.0: Created LinkedList data type - July 25th, 2016
     * 1.0.1: implemented configurationinterface - July 31st, 2016
     * 
     * @link https://en.wikipedia.org/wiki/Doubly_linked_list [English]
     * 
     * @version 1.0
     * 
     */
    class LinkedList extends TypesResource\AbstractLinkedList implements
        TypesResource\LinkedListInterface,
        \ArrayAccess,   // access the list like so: $list[indice]
        \Iterator,      // run the list through loops
        \Countable,      // tell us how many elements there are
        FeaturesResource\ConfigurationInterface
    {
        use Features\ConfigurationTrait;
        
        /**
         * The current item in the list
         * 
         * This is important for Iteration.  We step through the list one
         * element at a time in the iterator functions, but we have to
         * remember what element we were last at
         * 
         * @var Phabstractic\Data\Types\LinkedListElement
         * 
         */
        private $currentElement = null;
        
        // Countable Interface Methods
        
        /**
         * Count - Countable Interface Method
         * 
         * This counts all the elements in a list to achieve an accurate count
         * 
         * @return int The new count
         * 
         */
        public function count() {
            $numberOfElements = 0;
            
            if ($this->sentinelElement !== null) {
                $current = &$this->sentinelElement;
                $numberOfElements++;  // count the first element
                while ($current->getNextElement() !== null) {
                    $current = &$current->getNextElement();
                    $numberOfElements++;
                }
                
            }
            
            return $numberOfElements;
        }
        
        /* The \Iterator Interface Methods
         * 
         * These enable the LinkedList to be used in foreach loops
         *
         */
        
        /**
         * Return the current \Iterator value
         * 
         * This is the current element pointed to by the object
         * 
         * @return Phabstractic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        public function current()
        {
            return $this->currentElement;
        }
        
        /**
         * Returns the key of the current \Iterator value
         * 
         * The LinkedList at this time has no keys (though that could change)
         * so this returns null
         * 
         * @return mixed The key currently being pointed to by \Iterator
         * 
         */
        public function key()
        {
            return 'Phabstractic\\Data\\Types\\LinkedList::currentElement';
        }
        
        /**
         * Advance the \Iterator index by one
         * 
         */
        public function next()
        {
            $this->currentElement = &$this->currentElement->getNextElement();
        }
        
        /**
         * Reset the internal \Iterator counter
         * 
         */
        public function rewind()
        {
            // sentinelElement is in abstract parent
            $this->currentElement = &$this->sentinelElement;
        }
        
        /**
         * Are we currently pointing to a valid key?
         * 
         */
        public function valid()
        {
            if ($this->currentElement !== null) {
                return true;
            }
            
            return false;
        }
        
        /**
         * Return The List Element At Index
         * 
         * @param $index
         * 
         * @return &Phabstractic\Data\Types\Resource\LinkedListElementInterface
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if the index is larger than the list, or the list is empty
         * 
         */
        private function &getElementAtIndex($index, $strict = false)
        {
            // count from the beginning zero
            $i = 0;
            $null = null;
            
            if ($this->sentinelElement !== null) {
                $current = &$this->sentinelElement;
                while ($i != $index && $current->getNextElement() !== null) {
                    $current = &$current->getNextElement();
                    $i++;
                }
                
                if ($i != $index) {
                    if ($strict) {
                        // we're strict, so throw a range exception
                        throw new TypesException\RangeException(
                            'Phabstractic\\Data\\Types\\LinkedList->getElementAtIndex: ' .
                            'List smaller than index');
                    } else {
                        return $null;
                    }
                } else {
                    return $current;
                }
            } else {
                if ($strict) {
                    // we're strict, so throw a range exception
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\LinkedList->getElementAtIndex: ' .
                        'LinkedList is empty');
                } else {
                    return $null;
                }
            }
        }
        
        // The ArrayAccess Functions
        
        /**
         * Set the offset in the map to the provided value
         * 
         * @param int $key The index to the list item
         * @param mixed $value The value to set to
         * 
         * @return bool is successful?
         * 
         */
        public function offsetSet($key, $value)
        {
            // value must be a linkedlistelement
            if (!($value instanceof LinkedListElement)) {
                // exit quietly
                return false;
            }
            
            if (!$key) {
                return $this->insertElementAfter($value);
            }
            
            $current = &$this->getElementAtIndex($key);
            
            if ($current !== null) {
                // replace the current value
                $prev = $current->getPreviousElement();
                $next = $current->getNextElement();
                
                if ($prev !== null) {
                    $value->setPreviousElement($prev);
                    $prev->setNextElement($value);
                } else {
                    $value->nullPreviousElement();
                }
                
                if ($next !== null) {
                    $value->setNextElement($next);
                    $next->setPreviousElement($value);
                } else {
                    $value->nullNextElement();
                }
                
                $current->nullPreviousElement();
                $current->nullNextElement();
                
            } else {
                // exit quietly
                return false;
            }
            
            return true;
        }
        
        /**
         * Retrieve the value in the list at the provided index
         * 
         * @param int $key the element counting from zero
         * 
         * @return mixed|null The value at the list index
         * 
         */
        public function offsetGet($key) {
            return $this->getElementAtIndex($key, $this->conf->strict);
        } 
        
        /**
         * Unset the index and value on the list
         * 
         * Note: Like the unset method, this throws no error if the index
         *          doesn't exist.
         * 
         * @param int $key the element counting from zero
         * 
         * @return bool False if the index is improper, or not numeric, true otherwise
         * 
         */
        public function offsetUnset($key) {
            $current = &$this->getElementAtIndex($key);
            
            if ($current !== null) {
                return $this->removeElement($current);
                return true;
            }
            
            return false;
        } 
        
        /**
         * Does the given key exist in the map?
         * 
         * Note: This method also returns false if the key is out of range
         * 
         * @param int $key The index into the map
         * 
         * @return bool Existing?
         * 
         */
        public function offsetExists($key) {
            $count = $this->count();
            
            if ($key <= $count) {
                return true;
            }
            
            return false;
        }
        
        /**
         * The LinkedList Constructor
         * 
         * This instantiates an empty list, or optionally you can specify
         * a LinkedListElement to set as the sentinel, or starting, element.
         * 
         * Currently available options:
         *     strict => whether to output errors or remain silent
         * 
         * @param $element The first element of the list
         * @param array $options The options for the list as outlined above
         *                                         
         */
        public function __construct(
            LinkedListElement $sentinel = null,
            array $options = array()
        ) {
            $this->configure($options);
            
            if ($sentinel !== null) {
                $this->sentinelElement = &$sentinel;
            }
        }
        
        /**
         * Flatten the Linked List Into Array
         * 
         * This pulls the $data properties of the list elements in sequential
         * order and puts them into an array for easy reference
         * 
         * @return array
         * 
         */
        public function flatten() {
            $ret = array();
            
            // we can iterate over ourselves
            foreach ($this as $element) {
                $ret[] = $element->getData();
            }
            
            return $ret;
        }
        
        /**
         * Retrieve element based on data
         * 
         * Returns first element whose data matches the input parameter
         * 
         * @param mixed $data The data to match
         * 
         * @return Phabstractic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        public function &findElement($data) {
            foreach ($this as $element) {
                if ($data == $element->getData()) {
                    return $element;
                }
            }
            
            return null;
        }
        
        // SPL Defined Functions For Compatibility
        
        /**
         * Add Element At Index
         * 
         * "Insert the value newval at the specified index"
         * 
         * @param int $index the index of the element to insert before
         * @param &Phabstractic\Data\Types\LinkedListElement the element to insert
         * 
         * @return bool Is successful?
         * 
         * @throws Phabstractic\Data\Types\Exception\OutOfRangeException
         * 
         */
        public function add($index, LinkedListElement &$newelement) {
            try {
                $current = $this[$index];
                
                if ($current !== null) {
                    $this->insertElementBefore($newelement, $current);
                    return true;
                }
            } catch (TypesException\RangeException $e) {
                throw new TypesException\OutOfRangeException(
                    'Phabstractic\\Data\\Types\\LinkedList->add: ' .
                    'Index out of range');
            }
            
            return false;
            
        }
        
        /**
         * Retrieve The Beginning Of The List
         * 
         * "Peeks at the node from the beginning of the doubly linked list"
         * 
         * @return Phabstractic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        public function bottom() {
            return $this->getSentinelElement();
        }
        
        /**
         * Retrieve The Beginning Of The List As Reference
         * 
         * "Peeks at the node from the beginning of the doubly linked list"
         * 
         * @return &Phabstractic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        public function &bottomReference() {
            return $this->getSentinelElement();
        }
        
        /**
         * Retrieve The End Of The List
         * 
         * "Peeks at the node from the end of the doubly linked list"
         * 
         * @return Phabstractic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        public function top() {
            $current = $this->getSentinelElement();
            
            if ($current !== null) {
                while ($current->getNextElement() !== null) {
                    $current = &$current->getNextElement();
                }
            }
            
            return $current;
        }
        
        /**
         * Retrieve The End Of The List As Reference
         * 
         * "Peeks at the node from the end of the doubly linked list"
         * 
         * @return &Phabstractic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        public function &topReference() {
            $current = $this->getSentinelElement();
            
            if ($current !== null) {
                while ($current->getNextElement() !== null) {
                    $current = &$current->getNextElement();
                }
            }
            
            return $current;
        }
        
        /**
         * Check If List Is Empty
         * 
         * "Checks whether the doubly linked list is empty."
         * 
         * @return bool
         * 
         */
        public function isEmpty() {
            if ($this->getSentinelElement()) {
                return true;
            }
            
            return false;
        }
        
        /**
         * Pop An Element Off The End
         * 
         * "Pops a node from the end of the doubly linked list"
         * 
         * @return Phabstractic\Data\Types\Resource\LinkedListElementInterface
         *
         */
        public function pop() {
            $top = &$this->topReference();
            
            if ($top !== null) {
                $this->removeElement($top);
            }
            
            return $top;
        }
        
        /**
         * Pop An Element Off The End
         * 
         * "Pops a node from the end of the doubly linked list"
         * 
         * @return Phabstractic\Data\Types\Resource\LinkedListElementInterface
         *
         */
        public function &popReference() {
            $top = &$this->topReference();
            
            if ($top !== null) {
                $this->removeElement($top);
            }
            
            return $top;
        }
        
        /**
         * Move to previous entry
         * 
         */
        public function prev()
        {
            $this->currentElement = &$this->currentElement->getPreviousElement();
        }
        
        /**
         * Push Element at End
         * 
         * "Pushes an element at the end of the doubly linked list"
         * 
         * @param Phabstractic\Data\Types\LinkedListElement $element
         * 
         */
        public function push(&$element) {
            return $this->insertElementAfter($element);
        }
        
        /**
         * Get The Beginning Of The List
         * 
         * "Shifts a node from the beginning of the doubly linked list"
         * 
         * @return Phabstractic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        public function shift() {
            $ret = &$this->getSentinelElement();
            
            if ($ret !== null) {
                $this->removeElement($ret);
            }
            
            return $ret;
        }
        
        /**
         * Get The Beginning Of The List As Reference
         * 
         * "Shifts a node from the beginning of the doubly linked list"
         * 
         * @return Phabstractic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        public function &shiftReference() {
            $ret = &$this->getSentinelElement();
            
            if ($ret !== null) {
                $this->removeElement($ret);
            }
            
            return $ret;
        }
        
        /**
         * Unshift Element at Beginning
         * 
         * "Prepends the doubly linked list with an element"
         * 
         * @param Phabstractic\Data\Types\LinkedListElement $element
         * 
         */
        public function unshift(&$element) {
            return $this->insertElementBefore($element);
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
                'data' => $this->flatten(),
            ];
        }
        
    }
    
}
