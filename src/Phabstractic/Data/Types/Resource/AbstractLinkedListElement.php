<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Linked List Element Abstract Class
 * 
 * This establishes an abstract class to inherit from with the necessary linked
 * list functions defined, getNext, getPrevious, etc.
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
    $includes = array(// we implement the list element interface
                      '/Data/Types/Resource/LinkedListElementInterface.php',);
    
    falcraftLoad($includes, __FILE__);
    
    /**
     * Linked List Element Abstract Class
     * 
     * Defines a basic linked list element class, implements LinkedListElementInterface
     * 
     * The idea behind this element is that it provides a reference to an
     * element 'ahead' of itself, and a reference 'behind' itself.  This gives
     * us the ability to define a list where you can edit its contents through
     * the nodes themselves as opposed to operations on an array.
     * 
     * CHANGELOG
     * 
     * 1.0: Created AbstractList - May 10th, 2013
     * 1.1: Eliminated todo, and other obselete/incorrect documentation - October 7th, 2013\
     * 2.0: Integrated into Primus - August 26th, 2015
     * 3.0: renamed item to element in method names
     *      added element to private properties to distinguish class
     *      reformatted for inclusion in phabstractic - July 25th, 2016
     * 
     * @link https://en.wikipedia.org/wiki/Doubly_linked_list [English]
     * 
     * @abstract
     * 
     * @version 3.0
     */
    abstract class AbstractLinkedListElement implements LinkedListElementInterface 
    {
        /**
         * The next element in the series
         * 
         * @var null|Phabstractic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        protected $nextElement = null;
        
        /**
         * The previous element in the series
         * 
         * @var null|Phabstractic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        protected $previousElement = null;
        
        /**
         * Get the next element reference
         * 
         * @return Phabstractic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        public function &getNextElement()
        {
            return $this->nextElement;
        }
        
        /**
         * Set the next element reference
         * 
         * @param Phabstractic\Data\Types\Resource\LinkedListElementInterface &$next
         * 
         */
        public function setNextElement(LinkedListElementInterface &$next)
        {
            $this->nextElement = &$next;
        }
        
        /**
         * Set the next element pointer to null
         * 
         * This does not nullify the existing linked item
         * 
         */
        public function nullNextElement()
        {
            // If next is a reference, unreference it
            unset($this->nextElement);
            $this->nextElement = null;
        }
        
        /**
         * Get the previous element reference
         * 
         * @return Phabstractic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        public function &getPreviousElement()
        {
            return $this->previousElement;
        }
        
        /**
         * Set the previous element reference
         * 
         * @param Phabstractic\Data\Types\Resoruce\LinkedListElementInterface &$previous
         * 
         */
        public function setPreviousElement(LinkedListElementInterface &$previous)
        {
            $this->previousElement = &$previous;
        }
        
        /**
         * This nulls the previous pointer for this element
         * 
         * Doesn't actually nullify the referenced object
         * 
         */
        public function nullPreviousElement()
        {
            // If a reference, unreference
            unset($this->previousElement);
            $this->previousElement = null;
        }
        
    }
}