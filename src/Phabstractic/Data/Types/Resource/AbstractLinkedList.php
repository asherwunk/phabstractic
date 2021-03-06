<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Linked List Abstract Class
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
 * @subpackage Resource
 * 
 */
 
 
/**
 * Falcraft Libraries Data Types Resource Namespace
 * 
 */
namespace Phabstractic\Data\Types\Resource
{
    require_once(realpath( __DIR__ . '/../../../') . '/falcraftLoad.php');
    
    /*
     * Implements the same named interface
     */
    $includes = array(// we implement the linked list interface
                      '/Data/Types/Resource/LinkedListInterface.php',
                      // we type check against the list element interface
                      '/Data/Types/Resource/LinkedListElementInterface.php',);
    
    falcraftLoad($includes, __FILE__);
    
    /**
     * Linked List Abstract Class
     * 
     * Defines a basic linked list class, implements LinkedListInterface
     * 
     * This list offers us a chance to have a sequence of data tied together purely
     * by references.  This means that it does NOT utilize an array or array
     * operations, but instead relies on elements that connect to each other in a
     * sequential fashion.  This allows us to modify the contents of the list using
     * the elements themselves, or at least their references.
     * 
     * CHANGELOG
     * 
     * 1.0: Created AbstractedLinkedList - July 25th, 2016
     * 
     * @link https://en.wikipedia.org/wiki/Doubly_linked_list [English]
     * 
     * @abstract
     * 
     * @version 1.0
     * 
     */
    class AbstractLinkedList implements LinkedListInterface
    {
        
        /**
         * The sentinel element of the list
         * 
         * This is usually the 'beginning' of the list
         * 
         * @var Phabstractic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        protected $sentinelElement = null;
        
        /**
         * Retrieve 'sentinel' List Element
         * 
         * This is usually the first element in the list
         * 
         * @return Phabstarctic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        public function &getSentinelElement()
        {
            return $this->sentinelElement;
        }
        
        /**
         * Does element already exist in list?
         * 
         * This avoids infinite loops in element insertion
         * 
         * @param &Phabstractic\Data\Types\Resource\LinkedListElementInterface
         *              The element object to check
         * 
         * @return bool
         * 
         */
        private function isElementInList(&$element)
        {
            $current = $this->getSentinelElement();
            
            if ($current !== null) {
                while ($current !== null) {
                    if ($element === $current) {
                        return true;
                    }
                    
                    $current = &$current->getNextElement();
                }
                
            }
            
            return false;
        }
        
        /**
         * Insert An Element 'Before' Another Element
         * 
         * If &$element is null, insert at BEGINNING of list
         * 
         * @param Phabstractic\Data\Types\Resource\LinkedListElementInterface
         *          &$element The element we're talking about
         * @param Phabstractic\Data\Types\Resource\LinkedListElementInterface
         *          &$newElement The element we're going to insert
         * 
         * @return bool Successful?
         * 
         */
        public function insertElementBefore(
            LinkedListElementInterface &$newElement,
            LinkedListElementInterface &$element = null
        ) {
            // avoid infinite loops
            if ($this->isElementInList($newElement)) {
                return false;
            } else {
                if ($element === null) {
                    // if $element is null, we are inserting at the beginning of the list
                    if ($this->sentinelElement === null) {
                        // this list is empty
                        $this->sentinelElement = &$newElement;
                        $this->sentinelElement->nullNextElement();
                        $this->sentinelElement->nullPreviousElement();
                    } else {
                        // we need to go BEFORE the sentinel element
                        $this->insertElementBefore($newElement, $this->sentinelElement);
                    }
                } else {
                    /* otherwise we are inserting before an actual element
                       this can be the sentinelElement (see above) */
                    if ($element->getPreviousElement() !== null) {
                        $newElement->setPreviousElement($element->getPreviousElement());
                        $element->getPreviousElement()->setNextElement($newElement);
                    } else {
                        $newElement->nullPreviousElement();
                    }
                    
                    $newElement->setNextElement($element);
                    $element->setPreviousElement($newElement);
                    if ($newElement->getPreviousElement() === null) {
                        $this->sentinelElement = &$newElement;
                    }
                }
            }
            
            return true;
        }
        
        /**
         * Insert An Element 'After' Another Element
         * 
         * If &$element is null, insert at END of list
         * 
         * @param Phabstractic\Data\Types\Resource\LinkedListElementInterface
         *          &$element The element we're talking about
         * @param Phabstractic\Data\Types\Resource\LinkedListElementInterface
         *          &$newElement The element we're going to insert
         * 
         * @return bool Successful?
         * 
         */
        public function insertElementAfter(
            LinkedListElementInterface &$newElement,
            LinkedListElementInterface &$element = null
        ) {
            if ($this->isElementInList($newElement)) {
                return false;
            } else {
                if ($element === null) {
                    // if element is null, we are inserting at the end of the list
                    if ($this->sentinelElement === null) {
                        // this list is empty
                        $this->sentinelElement = &$newElement;
                        $this->sentinelElement->nullNextElement();
                        $this->sentinelElement->nullPreviousElement();
                    } else {
                        // find the last element
                        $element = &$this->sentinelElement;
                        while ($element->getNextElement() !== null) {
                            $element = &$element->getNextElement();
                        }
                        
                        $this->insertElementAfter($newElement, $element);
                    }
                } else {
                    // otherwise we are inserting after an actual element
                    $newElement->setPreviousElement($element);
                    
                    if ($element->getNextElement() !== null) {
                        $newElement->setNextElement($element->getNextElement());
                        $element->getNextElement()->setPreviousElement($newElement);
                    } else {
                        $newElement->nullNextElement();
                    }
                    
                    $element->setNextElement($newElement);
                }
            }
            
            return true;
        }
        
        /**
         * Remove An Element
         * 
         * @param Phabstractic\Data\Types\Resource\LinkedListElementInterface
         *          &$element The element we're talking about
         * 
         * @return bool Successful?
         * 
         */
        public function removeElement(LinkedListElementInterface &$element)
        {
            if ($this->isElementInList($element)) {
                if ($this->sentinelElement === $element) {
                    // we are removing the sentinel element
                    unset($this->sentinelElement); // dereference
                    
                    if ($element->getNextElement() !== null) {
                        $this->sentinelElement = $element->getNextElement();
                    } else {
                        $this->sentinelElement = null;
                    }
                    
                    return;
                }
                
                // otherwise, we have an element we need to get rid of
                $prev = &$element->getPreviousElement();  // might be null
                $next = &$element->getNextElement();
                
                if ($prev !== null) {
                    if ($next !== null) {
                        $prev->setNextElement($next);
                    } else {
                        $prev->nullNextElement();
                    }
                }
                
                if ($next !== null) {
                    if ($prev !== null) {
                        $next->setPreviousElement($prev);
                    } else {
                        $next->nullPreviousElement();
                    }
                }
                
                return true;
                
            } else {
                return false;
            }
            
        }
        
    }
    
}
