<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Linked List Element Basic Concrete Class
 * 
 * This establishes a concrete class that inherits AbstractLinkedListElement
 * The abstract class currently has no abstract methods, but may in the future,
 * so all we really have to implement is an association of data using a data
 * property
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
    $includes = array(// we inherit from abstract
                      '/Data/Types/Resource/AbstractLinkedListElement.php',
                      // we implement interface
                      '/Data/Types/Resource/LinkedListElementInterface.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types\Resource as TypesResource;
    
    /**
     * LinkedList Basic Concrete Class
     * 
     * Defines a basic linked list class, implements ListInterface
     * 
     * The idea behind this element is that it provides a reference to an
     * element 'ahead' of itself, and a reference 'behind' itself.  This gives
     * us the ability to define a list where you can edit its contents through
     * the nodes themselves as opposed to operations on an array.
     * 
     * CHANGELOG
     * 
     * 1.0: Created LinkedListElement - July 25th, 2016
     * 
     * @link https://en.wikipedia.org/wiki/Doubly_linked_list [English]
     * 
     * @version 1.0
     * 
     */
    class LinkedListElement extends TypesResource\AbstractLinkedListElement implements
        TypesResource\LinkedListElementInterface
    {
        /**
         * The data associated with the linked list element
         * 
         * @var mixed
         * 
         */
        private $data = null;
        
        /**
         * Get the data associated with this element
         * 
         * @return mixed
         * 
         */
        public function getData()
        {
            return $this->data;
        }
        
        /**
         * Get the data associated with this element as a reference
         * 
         * @return mixed
         * 
         */
        public function &getDataReference()
        {
            return $this->data;
        }
        
        /**
         * Set the data associated with this element
         * 
         * @param mixed $data
         * 
         */
        public function setData($data)
        {
            $this->data = $data;
        }
        
        /**
         * Set the data associated with this element as a reference
         * 
         * @param mixed &$data
         * 
         */
        public function setDataReference(&$data)
        {
            $this->data = &$data;
        }
        
        /**
         * Linked List Element Constructor
         * 
         * Sets the data associated with the linked list element as well as next
         * and previous references if given
         * 
         * @param mixed $data  The data to associate
         * @param &Phabstractic\Data\Types\Resource\LinkedListElementInterface
         *          $previous
         * @param &Phabstractic\Data\Types\Resource\LinkedListElementInterface
         *          $next
         * 
         */
        public function __construct(
            &$data,
            TypesResource\LinkedListElementInterface &$previous = null,
            TypesResource\LinkedListElementInterface &$next = null
        ) {
            $this->data = &$data;
            
            if ($previous !== null) {
                $this->setPreviousElement($previous);
            }
            
            if ($next !== null) {
                $this->setNextElement($next);
            }
            
        }
        
        /**
         * Build a Linked List Element
         * 
         * @param mixed $data  The data to associate
         * @param &Phabstractic\Data\Types\Resource\LinkedListElementInterface
         *          $previous
         * @param &Phabstractic\Data\Types\Resource\LinkedListElementInterface
         *          $next
         * 
         */
         public static function buildElement(
            $data,
            TypesResource\LinkedListElementInterface &$previous = null,
            TypesResource\LinkedListElementInterface &$next = null
        ) {
            return new LinkedListElement($data, $previous, $next);
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
                'data' => $this->data,
                'previous' => $this->previousElement,
                'next' => $this->nextElement,
            ];
        }
    }
    
}