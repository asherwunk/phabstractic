<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Linked List Interface
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
    $includes = array(// we type check against the list element interface
                      '/Data/Types/Resource/LinkedListElementInterface.php',);
    
    falcraftLoad($includes, __FILE__);

    /**
     * The Basic Linked List Interface
     * 
     * This allows for an array like structure that can
     * be broken up into parts and rearranged as it uses
     * links to its neighboring objects, which can be edited.
     * 
     * Basic operations are: InsertElement, RemoveElements
     * 
     * CHANGELOG
     * 
     * 1.0: Created LinkedListInterface - July 25th, 2016
     *  
     * @version 1.0
     * 
     */
    interface LinkedListInterface
    {
        /**
         * Retrieve 'sentinel' List Element
         * 
         * This is usually the first element in the list
         * 
         * @return Phabstarctic\Data\Types\Resource\LinkedListElementInterface
         * 
         */
        public function &getSentinelElement();
        
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
        );
        
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
        );
        
        /**
         * Remove An Element
         * 
         * @param Phabstractic\Data\Types\Resource\LinkedListElementInterface
         *          &$element The element we're talking about
         * 
         * @return bool Successful?
         * 
         */
        public function removeElement(LinkedListElementInterface &$element);
        
    }
    
}
