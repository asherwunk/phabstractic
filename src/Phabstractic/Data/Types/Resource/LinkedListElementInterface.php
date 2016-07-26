<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * This file contains the LinkedListInterface
 * 
 * This defines basic operations specific to a list item that points to an
 * element ahead of itself, and an element 'behind' itself.  This is usually
 * done in a Doubly Linked List.
 * 
 * This structure is unique because one item can keep track of where it is in
 * the list by keeping track of its neighbors, rather than using an array
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projcts/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Data
 * @subpackage Structures
 * 
 */

/**
 * Falcraft Libraries Data Types Resource Namespace
 * 
 */
namespace Phabstractic\Data\Types\Resource
{
    /**
     * The basic LinkedListInterface
     * 
     * This allows for an array like structure that can
     * be broken up into parts and rearranged as it uses
     * links to its neighboring objects, which can be edited.
     * 
     * CHANGELOG
     * 
     * 1.0: Created LinkedListInterface - May 26th, 2013
     * 2.0: Integrated with Primus - August 26th, 2015
     * 3.0: renamed methods to use element instead of item
     *      reformatted for inclusion in phabstractic - July 25th, 2016
     *  
     * @version 3.0
     * 
     */
    interface LinkedListElementInterface
    {
        
        /**
         * Retrieve the next item in the list
         * 
         * If null, at end of list
         * 
         * @return reference The next item in the list
         * 
         */
        public function &getNextElement();
        
        /**
         * Retrieve the previous item in the list
         * 
         * If null, at beginning of list
         * 
         * @return reference The previous item in the list
         * 
         */
        public function &getPreviousElement();
        
        /**
         * Set the next item in the list for this object
         * 
         * @param reference $next The next item
         * 
         */
        public function setNextElement(LinkedListElementInterface &$next);
        
        /**
         * Set the previous item in the list for this object
         * 
         * @param reference $prev The given previous item
         * 
         */
        public function setPreviousElement(LinkedListElementInterface &$prev);
        
        /**
         * Null out the next item in the list for this object
         * 
         * NOTE: (can't pass null as reference)
         * 
         */
        public function nullNextElement();
        
        /**
         * Null out the previous item in the list for this object
         * 
         * NOTE: (can't pass null as reference)
         * 
         */
        public function nullPreviousElement();
    }
    
}
