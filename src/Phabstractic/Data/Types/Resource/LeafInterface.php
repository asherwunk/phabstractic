<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Leaf Tree Structure Node Interface
 * 
 * This implements a Leaf structure interface.  A leaf structure is a recursive
 * list of further leaves (each connected leaf serves as a branch and leaf)
 * 
 * @copyright Copyright 2015 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Data
 * @subpackage Structures
 * 
 */

 /**
 * Falcraft Libraries Data Types (TaggedUnion) Namespace
 * 
 */
namespace Phabstractic\Data\Types\Resource
{
    /**
     * Leaf Interface - Defines A Branch/Leaf Structure Interface
     * 
     * CHANGELOG
     * 
     * 1.0: Created DirectoryInterface - February 8th, 2014
     * 1.1: Repurposed to LeafInterface - February 9th, 2014
     *         Documented LeafInterface fully - February 21st, 2014
     * 2.0: Integrated Leaf Interface into Primus - August 26th, 2015
     * 3.0: eliminated data methods
     *      reformatted for inclusion in phabstractic - August 1st, 2016
     * 
     * @version 3.0
     * 
     */
    interface LeafInterface
    {
        /**
         * Get a simple array of the connected leaf objects
         * 
         * @return array The leaf objects
         * 
         */
        public function getLeaves();
        
        /**
         * Add a leaf to this leaf, with optional LOCAL identifier
         * 
         * @param Phabstractic\Data\Types\Resource\LeafInterface $leaf
         *              The leaf to add
         * 
         * @return mixed The new leaf identity
         * 
         */
        public function addLeaf(LeafInterface $leaf);
        
        /**
         * Remove a leaf by its LOCAL identifier
         * 
         * @param Phabstractic\Data\Types\Resource\LeafInterface $leaf
         * 
         */
        public function removeLeaf(LeafInterface $leaf);
        
        /**
         * Does this leaf identifier exist in the local list?
         * 
         * @param Phabstractic\Data\Types\Resource\LeafInterface $leaf
         * 
         * @return mixed The leave's identity
         * 
         */
        public function isLeaf(LeafInterface $leaf);
        
    }
    
}
