<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * A Set Interface
 * 
 * An instantiated Set is like an array, but only holds unique values without
 * really any keys to the set itself.  It's just a collection of unique
 * values, like a pool of variables.
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Data
 * @subpackage Types
 * 
 */
 
/**
 * Falcraft Libraries Data Type Resource Namespace
 * 
 */
namespace Phabstractic\Data\Types\Resource
{
    
    /**
     * Set Interface - Defines A Set Data Structure
     * 
     * A set is a collection of values that are unique.  In mathematical terms
     * it is an implementation of a finite set.  Usually you test if something
     * exists or doesn't exist inside the set.  You can also set up operations on
     * sets themselves, much like an array map.  You can also easily perform
     * unions, intersections, and many operations traditional to set theory and the
     * set data type.
     * 
     * @see Phabstractic\Data\Types\Set
     * 
     * CHANGELOG
     * 
     * 1.0: Created SetInterface - April 11th, 2015
     * 2.0: reformatted for inclusion in phabstractic - July 10th, 2016
     * 
     * @link http://en.wikipedia.org/wiki/Set_(abstract_data_type) [English]
     * 
     * @version 2.0
     * 
     */
    interface SetInterface
    {
        
        /**
         * Get the Set as Array
         * 
         * Return the set contents as an array
         * 
         * @return array The set represented as an array
         * 
         */
        public function getArray();
        
        /**
         * Get the Set's Array
         * 
         * Return the set's array by reference
         * 
         * @return array The set's internal array
         *
         */
        public function &getArrayReference();
        
        /**
         * Get the Set as a PLAIN Array
         * 
         * Return the set contents as an array without string keys
         * 
         * NOTE:  All elements are references to internal array elements
         * 
         * @return array The set represented as a plain array
         * 
         */
        public function getPlainArray();
        
        /**
         * If the value is in the set, this retrieves a reference to it
         * 
         * This only works if the set is considered unique
         * 
         * @param mixed $identifier The identifier returned by the add function
         * 
         * @return mixed;
         * 
         */
        public function &retrieveReference($identifier);
        
        /**
         * Add a value to the set, respects 'unique' option
         * 
         * @param mixed $value The value to add to the set.
         * 
         * @return string The internal datum identifier
         * 
         */
        public function add($value);
        
        /**
         * Add a value to the set as a reference, respects 'strict' option
         * 
         * @param mixed $value The value to add to the set.
         * 
         * @return string The internal datum identifier
         * 
         */
        public function addReference(&$value);
        
        /**
         * Remove a value from the set
         * 
         * Respects 'strict' option: the set must contain the value to be
         * removed.
         * 
         * @param mixed $value The value to be removed from the set.
         * 
         */
        public function remove($value);
        
        /**
         * Remove a value from the set
         * 
         * This uses a previously given identifier to remove an element
         * from the set.  If the identifier doesn't exist it returns false.
         * 
         * @param mixed $identifier The identifier to be removed from the set.
         * 
         */
        public function removeByIdentifier($identity);
        
        /**
         * Basic in_array wrapper for the set
         * 
         * @param mixed $value The value to check against the data
         * 
         */
        public function in($value);
        
        /**
         * Is the internal data array empty?
         * 
         * @return bool
         * 
         */
        public function isEmpty();
        
        /**
         * Basic count wrapper for the set
         * 
         * @return int Size of data array
         * 
         */
        public function size();
        
        /**
         * This allows us to use the Set data in a basic iterator
         * Being, \ArrayIterator
         * 
         * @return \ArrayIterator The iterator to use in a loop
         * 
         */
        public function iterate();
        
        /**
         * Synonym for getArray()
         * 
         * @return array The data array of the set
         * 
         */
        public function enumerate();
        
        /**
         * Pop the first/next value defined the set
         * 
         * This deletes the value from the set
         * 
         * @return mixed The value 'popped' from the Set
         * 
         */
        public function pop();
        
        /**
         * Eliminate Set data completely
         * 
         */
        public function clear();
        
        /**
         * Return a hash (unique) of the entire set
         * 
         * Derived from the Set data: "returns a hash value for the static set
         * S such that if equal(S1, S2) then hash(S1) = hash(S2)"
         * 
         * @link http://en.wikipedia.org/wiki/Set_(abstract_data_type)#Additional_operations [English]
         * 
         * @return string The hash of the set data
         * 
         */
        public function hash();
        
        /* Note that the following functions, exception being build, equal, 
           and fold return arrays, not sets.  To construct a set out of a
           returned value you must call the function inside another Set
           constructor.
         
           These static functions are offered as Set operators defined in the
           class */
        
        /**
         * Is S1 Equal to S2?
         * 
         * @static
         * 
         * @return bool
         * 
         */
        public static function equal(SetInterface $S1, SetInterface $S2);
        
        /**
         * Apply a function to each element of the set, reducing to a single function
         * 
         * "returns the value A|S| after applying Ai+1 := F(Ai, e) for each
         * element e of S."
         * 
         * @static
         * 
         * @link http://en.wikipedia.org/wiki/Set_(abstract_data_type)#Additional_operations [English]
         * 
         * @param callable $F The applicable function.
         * @param Set $S The Set to fold
         * 
         * @return mixed The value resulting from the 'fold' (array_reduce)
         * 
         */
        public static function fold($F, SetInterface $S);
        
        /**
         * Returns only elements that satisfy a 'predicate'
         * 
         * The predicate here is a function that returns true or false on any
         * given item "returns the subset containing all elements of S that
         * satisfy a given predicate P."
         * 
         * @static
         * 
         * @link http://en.wikipedia.org/wiki/Set_(abstract_data_type)#Additional_operations [English]
         * 
         * @param callable $F The applicable predicate function.
         * @param Set $S The Set to filter
         * 
         * @return array The filtered aray
         * 
         */
        public static function filter($F, SetInterface $S);
        
        /**
         * Apply a function to each element of the set
         * 
         * "returns the set of distinct values resulting from applying function
         * F to each element of S."
         * 
         * @static
         * 
         * @link http://en.wikipedia.org/wiki/Set_(abstract_data_type)#Additional_operations [English]
         * 
         * @param callable $F The applicable function.
         * @param Set $S The Set to map
         * 
         * @return array The resulting distinct set
         * 
         */
        public static function map($F, SetInterface $S);
        
        /**
         * Applies the given function, plus any additional arguments
         * ($args) past the given function argument to each
         * element of theset.
         * 
         * Note: Unlike Map, this operates on a reference of the set
         * 
         * @static
         * 
         * @param Falcraft\Data\Types\Set &$S The set
         * @param callable $F the applicablt function
         * @param mixed $D the user data
         * 
         * @return bool True on Success, False otherwise
         * 
         */
        public static function walk(SetInterface &$S, $F, $D);
        
        /**
         * Create a set from an array
         * 
         * Just hands back a Set created from a given array
         * "creates a set structure with values x1,x2,…,xn."
         * 
         * @static
         * 
         * @link http://en.wikipedia.org/wiki/Set_(abstract_data_type)#Static_sets [English]
         * 
         * @param array $values The values of the set (which are 'uniqued' in the construction)
         * 
         * @return Set
         * 
         */
        public static function build(array $values = array());
        
        /**
         * Set Union
         * 
         * "returns the union of sets S and T."
         * 
         * This function can take multiple arrays, sets, or values mixed
         * together
         * 
         * @static
         * 
         * @link http://en.wikipedia.org/wiki/Set_(abstract_data_type)#Core_set-theoretical_operations [English]
         * 
         * @param mixed $S,... Data to merge
         * 
         * @return array Unioned array
         * 
         */
        public static function union();
        
        /**
         * Set Intersection
         * 
         * "returns the intersection of sets S and T."
         * 
         * This function can take multiple arrays, Sets, or values mixed together
         * 
         * @static
         * 
         * @link http://en.wikipedia.org/wiki/Set_(abstract_data_type)#Core_set-theoretical_operations [English]
         * 
         * @param mixed $S,... Data to intersect
         * 
         * @return array Intersected array
         * 
         */
        public static function intersection();
        
        /**
         * Set Difference
         * 
         * "returns the difference of sets S and T."
         * 
         * This function can take multiple arrays, Sets, or values
         * mixed together
         * 
         * @static
         * 
         * @link http://en.wikipedia.org/wiki/Set_(abstract_data_type)#Core_set-theoretical_operations [English]
         * 
         * @param mixed $S,... Data to difference
         * 
         * @return array Difference array
         * 
         */
        public static function difference();
        
        /**
         * Set Filter
         * 
         * "a predicate that tests whether the set S is a subset of set T."
         * 
         * This function tests whether all elements of S are in T
         * 
         * @static
         * 
         * @link http://en.wikipedia.org/wiki/Set_(abstract_data_type)#Core_set-theoretical_operations [English]
         * 
         * @param Set $S The first set
         * @param Set $T The comparison/parent set
         * 
         * @return bool
         * 
         */
        public static function subset(SetInterface $S, SetInterface $T);
    }
    
}
