<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * A Set Class
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
    
    /* This class contains a static function for returning unique values
       for an array that is more object compatible. */
    $includes = array(// this object is configurable
                      '/Features/ConfigurationTrait.php',
                      '/Features/Resource/ConfigurationInterface.php',
                      // this object differentiates between set elements using identity
                      '/Features/IdentityTrait.php',
                      // returns none data type when retrieving non-existent by reference
                      '/Data/Types/None.php',
                      // throws rangeexception on non-existent
                      '/Data/Types/Exception/RangeException.php',
                      // uses phabstractic's array utilities for stuff like udiff
                      '/Resource/ArrayUtilities.php',
                      // implements interface, of course
                      '/Data/Types/Resource/SetInterface.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Features;
    use Phabstractic\Features\Resource as FeaturesResource;
    use Phabstractic\Data\Types\Exception as TypesException;
    use Phabstractic\Data\Types\Resource as TypesResource;
    use Phabstractic\Resource as PhabstracticResource;
    
    /**
     * Set Class - Defines A Set Data Structure
     * 
     * A set is a collection of values that are unique.  In mathematical terms
     * it is an implementation of a finite set.  Usually you test if something
     * exists or doesn't exist inside the set.  You can also set up operations on
     * sets themselves, much like an array map.  You can also easily perform
     * unions, intersections, and many operations traditional to set theory and the
     * set data type.
     * 
     * CHANGELOG
     * 
     * 1.0:   Documented Set - March 5th, 2013
     * 1.1:   Added Closures to array set operations - October 7th, 2013
     * 2.0:   Refactored and included into Primus/Falcraft - April 10th, 2015
     * 3.0:   reorganized namespaces
     *        edited returnUniqueByReference calls to actually use references
     *        set only unique if unique option is set now !!!
     *        reformatted for inclusion in phabstractic - July 10th, 2016
     * 3.0.1: implements configurationinterface - July 13th, 2016
     * 
     * @link http://en.wikipedia.org/wiki/Set_(abstract_data_type) [English]
     * 
     * @version 3.0.1
     * 
     */
    class Set implements
        TypesResource\SetInterface,
        FeaturesResource\ConfigurationInterface
    {
        use Features\ConfigurationTrait;
        use Features\IdentityTrait;
        
        /**
         * Set Data
         * 
         * This is protected so that inheriting objects have access to the data
         * 
         * @var array $data The data of the set, implemented as an array
         * 
         */
        protected $data = array();
        
        /**
         * Get the Set as Array
         * 
         * Return the set contents as an array
         * 
         * @return array The set represented as an array
         * 
         */
        public function getArray()
        {
            return $this->data;
        }
        
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
        public function getPlainArray()
        {
            $r = array();
            foreach ($this->data as $key => $val) {
                $r[] = &$this->data[$key];
            }
            
            return $r;
        }
        
        /**
         * Get the Set's Array
         * 
         * Return the set's array by reference
         * 
         * @return array The set's internal array
         *
         */
        public function &getArrayReference()
        {
            return $this->data;
        }
        
        /**
         * Set Construction
         * 
         * Sets the data member to the given array.
         * 
         * NOTE: A set can contain any type of data, even mixed data.  This
         *       means that a set might contain integers, objects, and anything
         *       else storable in a variable all together simultaneously.
         * 
         * IMPORTANT: If keys are specified in the initial array then these
         *            keys are used as their identifications.
         * 
         * Options -
         * 
         *   'unique' => bool, do we check the incoming data for uniqueness?
         *                     If data is not unique an exception is raised.
         *   'strict' => bool, do we raise exceptions when the set is used
         *                     improperly, such as removing a value that doesn't
         *                     exist?
         *   'reference' => bool, do we add in constructor by reference?
         *                     defaults to true
         * 
         * @param array $values The values of the set
         * @param array|Zend\Config\Config|string $options See options above.
         * 
         */
        public function __construct(
            array $values = array(),
            $options = array()
        ) {
            // version 2.1 of ConfigurationTrait handles configuraiton formatting
            $this->configure($options);
            if (!isset($this->conf->reference)) {
                $this->conf->reference = true;
            }
            
            if (!isset($this->conf->unique)) {
                $this->conf->unique = false;
            }
            
            if ($this->conf->unique) {
                $this->checkUniqueValues($values);
                // more object compatible as opposed to array_unique SORT_REGULAR
                PhabstracticResource\ArrayUtilities::returnUniqueByReference($values);
            }
            
            // version 3 uses psuedo-FQN
            $this->identityPrefix = 'Phabstractic\\Data\\Types\\Set::Element';
            
            // v3: we should generate identifiers for the elements even in construction
            foreach ($values as $key => &$value) {
                if ($this->conf->reference) {
                    $this->addReference($values[$key]);
                } else {
                    $this->add($values[$key]);
                }
            }
            
        }
        
        /**
         * Change Element Identifier Prefix
         * 
         * @param string The new prefix after the pseudo FQN
         * 
         */
        public function setIdentifierPrefix($prefix) {
            $this->identityPrefix = $prefix;
        }
        
        /**
         * If the value is in the set, this retrieves a reference to it
         * 
         * This only works if the set is considered unique
         * 
         * @param mixed $identifier The identifier returned by the add function
         * 
         * @return mixed|Phabstractic\Data\Types\None;
         * 
         * @throws \Phabstractic\Data\Exception\RangeException if strict
         * 
         */
        public function &retrieveReference($identifier)
        {
            if (array_key_exists($identifier, $this->data)) {
                return $this->data[$identifier];
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\Set->retrieveReference: ' .
                        'Given Identifier Doesn\'t Exist');
                }
                
            }
            
            return new None();
            
        }
        
        /**
         * Check an array for unique values
         * 
         * Useful for 'strict' option to warn of incoming duplicate entries
         * 
         * @param array $values The array to check
         * 
         * @return bool Successful?
         * 
         * @throws \Phabstractic\Data\Types\Exception\RangeException
         *              When a value is not unique in the array if configured unique
         * 
         */
        private function checkUniqueValues(array $values = array())
        {
            $check = array();
            foreach ( $values as $value ) {
                if (in_array($value, $check, true)) {
                    /* it might seem prudent to use conf->strict, but we're
                       we might want to run this function if the conf->unique
                       option is set, but strict is not */
                    if ($this->conf->unique) {
                       throw new TypesException\RangeException(
                           'Phabstractic\\Data\\Types\\Set->checkUniqueValues: ' .
                           'Given Value Not Unique' );
                    } else {
                        return false;
                    }
                    
                } else {
                    $check[] = $value;
                }
                
            }
            
            return true;
        }
        
        /**
         * Add a value to the set, respects 'unique' option
         * 
         * @param mixed $value The value to add to the set.
         * 
         * @return string The internal datum identifier
         * 
         */
        public function add($value)
        {
            if ($this->conf->unique) {
                $this->checkUniqueValues(array_merge($this->data, array($value)));
            }
            
            $identity = $this->getNewIdentity();
            $this->data[$identity] = $value;
            
            if ($this->conf->unique) {
                PhabstracticResource\ArrayUtilities::returnUniqueByReference($this->data);
            }
            
            return $identity;
        }
        
        /**
         * Add a value to the set as a reference, respects 'unique' option
         * 
         * @param mixed $value The value to add to the set.
         * 
         * @return int The new count of the array
         * 
         */
        public function addReference(&$value)
        {
            if ($this->conf->unique) {
                $this->checkUniqueValues(array_merge($this->data, array($value)));
            }
            
            $identity = $this->getNewIdentity();
            $this->data[$identity] = &$value;
            
            if ($this->conf->unique) {
                PhabstracticResource\ArrayUtilities::returnUniqueByReference($this->data);
            }
            
            return $identity;
        }
        
        /**
         * Remove a value from the set
         * 
         * Respects 'strict' option: the set must contain the value to be
         *                           removed.
         * 
         * @param mixed $value The value to be removed from the set.
         * 
         * @throws \Phabstractic\Data\Types\Exception\RangeException
         *              if non-existent and strict is set
         * 
         */
        public function remove($value)
        {
            if ($this->conf->strict) {
                if (!in_array($value, $this->data)) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\Set->remove: Value Not In Set');
                }
            }
            
            
            /* This closure is more compatible with set values as opposed to
               built in comparison */
            $this->data = array_udiff($this->data, array($value),
                array('Phabstractic\\Resource\\ArrayUtilities', 'elementComparison'));
        }
        
        /**
         * Remove a value from the set
         * 
         * This uses a previously given identifier to remove an element
         * from the set.  If the identifier doesn't exist it returns false.
         * 
         * @param mixed $identifier The identifier to be removed from the set.
         * 
         */
        public function removeByIdentifier($identity)
        {
            if (array_key_exists($identity, $this->data)) {
                unset($this->data[$identity]);
                return true;
            }
            
            return false;
        }
        
        /**
         * Basic in_array wrapper for the set
         * 
         * @param mixed $value The value to check against the data
         * 
         */
        public function in($value)
        {
            return in_array($value, $this->data);
        }
        
        /**
         * Is the internal data array empty?
         * 
         * @return bool
         * 
         */
        public function isEmpty()
        {
            return !$this->data;
        }
        
        /**
         * Basic count wrapper for the set
         * 
         * @return int Size of data array
         * 
         */
        public function size()
        {
            return count($this->data);
        }
        
        /**
         * This allows us to use the Set data in a basic iterator
         * Being, \ArrayIterator
         * 
         * @return \ArrayIterator The iterator to use in a loop
         * 
         */
        public function iterate()
        {
            return new \ArrayIterator($this->data);
        }
        
        /**
         * Synonym for getArray()
         * 
         * @return array The data PLAIN array of the set
         * 
         */
        public function enumerate()
        {
            return $this->getPlainArray();
        }
        
        /**
         * Pop the first/next value defined the set
         * 
         * This deletes the value from the set
         * 
         * @return mixed The value 'popped' from the Set
         * 
         */
        public function pop()
        {
            return array_pop($this->data);
        }
        
        /**
         * Eliminate Set data completely
         * 
         */
        public function clear()
        {
            $this->data = array();
        }
        
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
        public function hash()
        {
            return md5(implode(',', $this->data));
        }
        
        /**
         * Debug Info (var_dump)
         * 
         * Display debug info
         * 
         * Requires PHP 5.6+
         * 
         */
        public function __debugInfo() {
            return [
                'options' => array('unique' => $this->conf->unique,
                                   'strict' => $this->conf->strict,
                                   'reference' => $this->conf->reference,),
                'identityPrefix' => $this->identityPrefix,
                'data' => $this->data,
            ];
        }
        
        /* Note that the following functions, exception being build, equal, 
           and fold return arrays, not sets.  To construct a set out of a
           returned value you must call the function inside another Set
           constructor.
         
           These static functions are offered as Set operators defined in the
           class */
        
        /**
         * Is S1 Equal to S2?
         * 
         * This static function converts the sets to plain arrays, but compares
         * them using the arrayUtilities function.
         * 
         * @static
         * 
         * @param \Phabstractic\Data\Types\Set S1 - the first set
         * @param \Phabstractic\Data\Types\Set S2 - the second set
         * 
         * @return bool
         * 
         */
        public static function equal(
            TypesResource\SetInterface $S1,
            TypesResource\SetInterface $S2
        ) {
            /* This closure is more compatible with set values than built
               in comparisons */
            if (!(array_udiff(
                    $S1->getPlainArray(),
                    $S2->getPlainArray(),
                    array('Phabstractic\\Resource\\ArrayUtilities', 'elementComparison')))) {
                return true;
            }
            
            return false;
        }
        
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
        public static function fold($F, TypesResource\SetInterface $S)
        {
            return array_reduce($S->getPlainArray(), $F);
        }
        
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
        public static function filter($F, TypesResource\SetInterface $S)
        {
            return array_filter($S->getPlainArray(), $F);
        }
        
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
        public static function map($F, TypesResource\SetInterface $S)
        {
            // More object compatible, rather than array_unique SORT_REGULAR
            return PhabstracticResource\ArrayUtilities::returnUnique(
                array_map($F, $S->getPlainArray()));
        }
        
        /**
         * Walk (non-recursively) the array
         * 
         * Applies the given function, plus any additional arguments
         * ($args) past the given function argument to each
         * element of the set.
         * 
         * Note: Unlike Map, this operates on a reference of the set
         * 
         * @static
         * 
         * @param Phabstractic\Data\Types\Set &$S The set
         * @param callable $F the applicablt function
         * @param $arg... The additional arguments to the applicable function
         * 
         * @return bool True on Success, False otherwise
         * 
         */
        public static function walk(TypesResource\SetInterface &$S, $F, $D)
        {
            return array_walk($S->getArrayReference(), $F, $D);
        }
        
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
        public static function build(array $values = array())
        {
            return new Set($values);
        }
        
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
        public static function union()
        {
            $result = array();
            foreach(func_get_args() as $arg) {
                if ($arg instanceof TypesResource\SetInterface) {
                    $result = array_merge($result, $arg->getPlainArray());
                } elseif (is_array($arg)) {
                    $result = array_merge($result, $arg);
                } else{
                    $result = array_merge($result, array($arg));
                }
                
            }
            
            // More object compatible rather than array_unique SORT_REGULAR
            return PhabstracticResource\ArrayUtilities::returnUnique($result);
        }
        
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
        public static function intersection()
        {
            // This closures more compatible with set values
            
            $result = null;
            foreach (func_get_args() as $arg) {    
                if ($arg instanceof TypesResource\SetInterface) {
                    if ($result === null) {
                        $result = $arg->getPlainArray();
                    } else {
                        $result = array_uintersect(
                                      $result,
                                      $arg->getPlainArray(),
                                      array(
                            'Phabstractic\\Resource\\ArrayUtilities', 'elementComparison')
                        );
                    }
                    
                } elseif (is_array($arg)) {
                    if ($result === null) {
                        $result = $arg;
                    } else {
                        $result = array_uintersect(
                                      $result,
                                      $arg,
                                      array(
                            'Phabstractic\\Resource\\ArrayUtilities', 'elementComparison')
                        );
                    }
                        
                } else {
                    if ($result === null) {
                        $result = array($arg);
                    } else {
                        $result = array_uintersect(
                                      $result,
                                      array($arg),
                                      array(
                            'Phabstractic\\Resource\\ArrayUtilities', 'elementComparison')
                        );
                    }
                    
                }
                
            }
            
            // More object compatible than array_unique SORT_REGULAR
            return PhabstracticResource\ArrayUtilities::returnUnique($result);
        }
        
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
        public static function difference()
        {
            // This closures are more compatible with set values
            
            $result = null;
            foreach (func_get_args() as $arg) {
                if ($arg instanceof TypesResource\SetInterface) {
                    if ($result === null) {
                        $result = $arg->getPlainArray();
                    } else {
                        $result = array_udiff(
                            $result,
                            $arg->getPlainArray(),
                            array('Phabstractic\\Resource\\ArrayUtilities', 'elementComparison')
                        );
                    }
                    
                } elseif (is_array($arg)) {
                    if ($result === null) {
                        $result = $arg;
                    } else {
                        $result = array_udiff(
                            $result,
                            $arg,
                            array('Phabstractic\\Resource\\ArrayUtilities', 'elementComparison')
                        );
                    }
                    
                } else {
                    if ($result === null) {
                        $result = array($arg);
                    } else {
                        $result = array_udiff(
                            $result,
                            array($arg),
                            array('Phabstractic\\Resource\\ArrayUtilities', 'elementComparison')
                        );
                    }
                    
                }
            }
            
            // More object compatible than array_unique SORT_REGULAR
            return PhabstracticResource\ArrayUtilities::returnUnique($result);
        }
        
        /**
         * Subset
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
        public static function subset(
            TypesResource\SetInterface $S,
            TypesResource\SetInterface $T
        ) {
            // This is more compatible value wise (objects)
            $sa = $T->getPlainArray();
            $ta = $S->getPlainArray();
            reset($sa);
            while (current($sa)) {
                foreach ($ta as $td) {
                    $c = false;
                    if (current($sa) === $td) {
                        $c = true;
                        next($sa);
                        break;
                    }
                    
                }
                
                if ($c == false) {
                    return false;
                }
                
            }
            
            return true;
        }
        
    }
    
    /* These did not work: (THE REASON FOR THE CLOSURES AND UNIQUE FUNCTION)
               return !array_diff($T->getArray(), $S->getArray());
               return !array_udiff($T->getArray(), $S->getArray(),
                    function($a,$b){if (is_object($a) && is_object($b)){if ($a 
                    === $b){return 0;} else {return -1;}} else {if ($a == $b){
                        return 0;} else {return -1;}}}); */
}
