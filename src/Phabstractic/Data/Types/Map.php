<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * A Map Class
 * 
 * This file contains the Map class.  A map is quite a formidable data type.
 * Traditionally an array in PHP is associative, using integers or strings as
 * hashes/keys.  This class allows the creation of an associate array where the
 * key can be any data type or object, and the value can be any data type or
 * object.
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
 * Falcraft Libraries Data Types Namespace
 * 
 */
namespace Phabstractic\Data\Types
{
    require_once(realpath( __DIR__ . '/../../') . '/falcraftLoad.php');
    
    // For object configuration purposes
    $includes = array(// we are configurable
                      '/Features/ConfigurationTrait.php',
                      // some methods return the None data type
                      '/Data/Types/None.php',
                      // use elementComparison in static functions
                      '/Resource/ArrayUtilities.php',
                      // we throw the following exceptions
                      '/Data/Types/Exception/RangeException.php',
                      '/Data/Types/Exception/InvalidArgumentException.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Features;
    use Phabstractic\Data\Types\Exception as TypesException;
    use Phabstractic\Resource as PhabstracticResource;
    
    /**
     * Map Class - Defines A Map Data Structure
     * 
     * A Map in this instance is a very versatile and complex data type.
     * In a traditional array in PHP, the keys are integers or strings,
     * as an indexed array is pretty much the same as an indexed one in the
     * language.  This cracks this concept wide open and allows keys to be
     * objects, resources, any valid comparable php value.  This means
     * that a Person object could be a key to an array of Courses.
     * 
     * Some of this functionality can be accomplished by simply creating
     * a private array in the 'enclosing' class, (e.g. Person->Courses),
     * but some functionality is unique to Maps themselves as opposed to arrays.
     * 
     * CHANGELOG
     * 
     * 1.0: Map Completed and Documented - March 8th, 2013
     * 1.1: Eliminated unnecessary Null objects - March 26th, 2013
     * 1.2: Provided closure's for static methods - October 7th, 2013
     * 2.0: Implemented in Primus - August 25th, 2015
     * 2.1: Fixed Typo in Remove - September 5th, 2015
     * 3.0: re-worked exists() to be compatible with arrayaccess methods
     *      reformatted and updated for includion in phabstractic - July 24th, 2016
     * 
     * @link http://en.wikipedia.org/wiki/Associative_array [English]
     * 
     * @version 3.0
     */
    class Map implements \Iterator, \ArrayAccess
    {
        use Features\ConfigurationTrait;
         
        /**
         * The default key index in an array defined element
         * 
         * EX:  Array( 0 => 'key', 1 => 'value' )
         * See __constructor
         * 
         */
        const KEY = 0;
        
        /**
         * The default value index in an array defined element
         * 
         * EX:  Array( 0 => 'key', 1 => 'value' )
         * See __constructor
         * 
         */
        const INDEX = 1;
        
        /**
         * The default separator in a string defined element
         * 
         * EX:  key=value
         * See __constructor
         * 
         */
        const SEPARATOR = '=';
        
        /**
         * The keys for the map
         * 
         * Defined as an array of ( key, indices );
         * 
         * @var array
         * 
         */
        private $keys = array();
        
        /**
         * The values for the map
         * 
         * Defined as a hopefully indexed array of values
         * 
         * @var array
         * 
         */
        private $values = array();
        
        
        /**
         * The internal index counter
         * 
         * This links a key to it's value in this way:
         * VAL[KEY[1]]
         * 
         * @var integer
         * 
         */
        private $index = 0;
        
        /* The \Iterator functions
         * 
         * These enable the Map to be used in foreach loops... very handy.
         * 
         * Note: This simply wraps around to the Keys array iterator.  This
         *       is so that if a key is unset later on, causing there to be
         *       a gap in the array index sequence, we can let PHP handle it.
         */
        
        /**
         * Reset the internal \Iterator counter
         * 
         */
        public function rewind()
        {
            reset($this->keys);
        }
        
        /**
         * Return the current \Iterator value
         * 
         * $this->pos is an index of the keys of the map, NOT it's values.
         * This means that any given key's actual value will be the self::VALUE
         * index in the values member.  Thus:
         * 
         * VALUES[ KEY[pos][INDEX]]
         * 
         * @return mixed The value currently being pointed to by \Iterator
         * 
         */
        public function current()
        {
            $current = current($this->keys);
            return $this->values[$current[self::INDEX]];
        }
        
        /**
         * Returns the key of the current \Iterator value
         * 
         * This just looks at the key data of the current \Iterator key
         * 
         * @return mixed The key currently being pointed to by \Iterator
         * 
         */
        public function key()
        {
            $current = current($this->keys);
            return $current[self::KEY];
        }
        
        /**
         * Advance the \Iterator index by one
         * 
         */
        public function next()
        {
            return next($this->keys);
        }
        
        /**
         * Are we currently pointing to a valid key?
         * 
         */
        public function valid()
        {
            if (current($this->keys)) {
                return true;
            }
            
            return false;
        }
        
        /**
         * Returns the keys of the map as an array.
         * 
         * Since the keys are stored in: KEYS[ I[SELF::KEY] ] we have
         * to step through and extract them into a separate array
         * 
         * @return array The key values of the map sans mapping
         * 
         */
        public function getKeys()
        {
            $keys = array();
            
            // Build an array of key values from the internal key data
            // KEYS[ I[SELF::KEY] ]
            foreach ($this->keys as $key) {
                $keys[] = $key[self::KEY];
            }
            
            return $keys;
        }
        
        /**
         * Returns the values of the map as an array
         * 
         * The values are stored in their own indexed array internally so,
         * this is a relatively straight-forward simple request.
         * 
         * @return array
         * 
         */
        public function getValues()
        {
            return $this->values;
        }
        
        /**
         * Increments the internal index
         * 
         * This increments the internal index so that a new key
         * has a place to store a new value.  We don't care if
         * previosu indices have been unset, we just need a new unique
         * number.
         * 
         */
        private function incrementIndex()
        {
            $this->index++;
        }
        
        /**
         * The Map Constructor
         * 
         * This constructor is a beast.  It takes several forms of input, but it
         * generally boils down to an array of values that will be turned into
         * key/value pairs, and an array of options.
         * 
         * The key/value pair is constructed according to the array elements
         * data type.
         * 
         * Currently the allowed data types are:
         *     stdClass (->key, and ->value)
         *     array ('key'/0, 'value'/1)
         *     string keySELF::SEPARATORvalue
         *     the actual key and value of the element otherwise
         * 
         * Currently available options:
         *     strict => whether to output errors or remain silent (and return
         *                  empties)
         *     typed => check the types of keys in comparisons (unique)
         * 
         * @param array $values The values, as outlined above, for the Map laid
         *                      out in key/value pairs
         * @param array $options The options for the Map as outlined above
         *                                         
         */
        public function __construct(
            array $values = array(),
            array $options = array()
        ) {
            $this->configure($options);
            
            foreach ($values as $k => $value) {
                /* The goal here is to put a value in key
                 * a value in val, and then associate those
                 * two together in the Map:
                 * 
                 * KEYS[i][ $key, x ], VALUES[x] = $val;
                 */
                $key = null;
                $val = null;
                
                if ($value instanceof \stdClass) {
                    $key = $value->key;
                    $val = $value->value;
                } elseif (is_array($value)) {
                    if (isset($value['key']) && isset($value['value'])) {
                        $key = $value['key'];
                        $val = $value['value'];
                    } else {
                        $key = $value[self::KEY];
                        $val = $value[self::INDEX];
                    }
                    
                } elseif (is_string($value)) {
                    if (strpos($value, self::SEPARATOR)) {
                        list($key, $val) = explode(self::SEPARATOR, $value, 2);
                    } else {
                        $key = $k;
                        $val = $value;
                    }
                    
                } else {
                    $key = $k;
                    $val = $value;
                }
                
                // KEYS[i][ $key, x ], VALUES[x] = $val;
                $this->keys[] = array($key, $this->index);
                $this->values[$this->index] = $val;
                $this->incrementIndex();
            }
        }
        
        /**
         * Check to see if a key already exists in the Map
         * 
         * @return bool True if key is unique
         * 
         */
        public function exists($key)
        {
            // numbers, whether strings or numbers are dual tested
            if (is_int($key) || is_numeric($key)) {
                if (in_array((int) $key, $this->getKeys(), true) ||
                        in_array((string) $key, $this->getKeys(), true)) {
                    return true;
                }
            } elseif (in_array($key, $this->getKeys(), true)) {
                return true;
            }
            
            return false;
        }
        
        /**
         * Add a value to the map with a key that previously did not exist
         * 
         * @param mixed $key
         * @param mixed $value
         * 
         * @return bool True on success (currently everything succeeds)
         * 
         */
        private function add($key, $value)
        {
            $this->keys[] = array($key, $this->index);
            $this->values[$this->index] = $value;
            $this->incrementIndex();
            return true;
        }
        
        /**
         * Return the internal index of a key
         * 
         * This value is the index of ->values as held by a ->key[]
         * 
         * @param mixed $key The key we are calculating the index for
         * 
         * @return integer|null The internal index, linking the map key to the
         *                          map value
         * 
         * @throws Phabstractic\Data\Types\eception\RangException
         *              when 'strict' and no such key exists
         * 
         */
        private function index($key)
        {
            if ($this->exists($key)) {
                foreach ($this->keys as $k) {
                    if ($this->conf->typed) {
                        if ($key === $k[self::KEY]) {
                            return $k[self::INDEX];
                        }
                        
                    } else {
                        if ($key == $k[self::KEY]) {
                            return $k[self::INDEX];
                        }
                        
                    }
                    
                }
                
            }
            
            if ($this->conf->strict) {
                throw new TypesException\RangeException(
                    'Phabstractic\\Data\\Types\\Map->index: Non-Existent Key');
            } else {
                // indices will never be null
                return null;
            }
            
        }
        
        /**
         * Replaces a value in the map of key
         * 
         * Checks to make sure the index exists before replacing it
         * 
         * @param mixed $key
         * @param mixed $value
         * 
         * @return bool Successful replacement?
         * 
         */
        private function replace($key, $value)
        {
            if (($index = $this->index($key)) !== null) {
                $this->values[$index] = $value;
                return true;
            }
            
            return false;
        }
        
        /**
         * The public set function, for setting keys and values
         * 
         * Given key will always override already existing key,
         * just like a normal PHP array
         * 
         * @param mixed $key
         * @param mixed $value
         * 
         * @return bool Operation successful?
         * 
         */
        public function set($key, $value)
        {
            if ($this->exists($key)) {
                return $this->replace($key, $value);
            } else {
                return $this->add($key, $value);
            }
            
        }
        
        /**
         * The 'unset' of the Map: remove value by key
         * 
         * In essence deletes the value of key from the Map
         * 
         * @param mixed $key The key to remove
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException if 'strict' and
         *              key doesn't exist
         * 
         */
        public function remove($key)
        {
            // Does the key exist?
            if ($this->exists($key)) {
                foreach ($this->keys as $i => $k) {
                    if ($this->conf->typed) {
                        if ($key === $k[self::KEY]) {
                            // Unset the associated value (tied together by index)
                            // KEYS[i] = ( key, x) VALUES[x] = val
                            unset($this->values[$k[$i][self::INDEX]]);
                            unset($this->keys[$i]);
                            return true;
                        }
                        
                    } else {
                        if ($key == $k[self::KEY]) {
                            // Unset the associated value (tied together by index)
                            // KEYS[i] = ( key, x) VALUES[x] = val
                            unset($this->values[$k[self::INDEX]]);
                            unset($this->keys[$i]);
                            return true;
                        }
                        
                    }
                    
                }
                
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\Map->remove: Non-Existent Key');
                } else {
                    return false;
                }
                
            }
            
        }
        
        /**
         * The 'get' function of the Map object
         * 
         * This technically 'finds' the value for a given key in the
         * morass of internal counters that is the Map object, so it
         * is named 'find', but it essentially 'gets' the associated value
         * 
         * $map->find($key) = $map[$key]
         * 
         * @param mixed $key The key to find the value for
         * 
         * @return mixed The value associated with the given key
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException if the key
         *      doesn't exist and 'strict' is set
         * 
         */
        public function find($key)
        {
            if ($this->exists($key)) {
                foreach ($this->keys as $k) {
                    if ($this->conf->typed) {
                        if ($key === $k[self::KEY]) {
                            return $this->values[$k[self::INDEX]];
                        }
                        
                    } else {
                        if ($key == $k[self::KEY]) {
                            return $this->values[ $k[self::INDEX] ];
                        }
                        
                    }
                    
                }
                
                // a map element might return null appropriately
                return new None();
                
            }
            
            if ($this->conf->strict) {
                throw new TypesException\RangeException(
                    'Phabstractic\\Data\\Types\\Map->find: Non-Existent Key');
            } else {
                return new None();
            }
            
        }
        
        // The ArrayAccess Functions
        
        /**
         * Set the offset in the map to the provided value
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
            if (!$key) {
                $counter = 0;
                while (in_array((string) $counter, $this->getKeys(), true) ||
                        in_array((int) $counter, $this->getKeys(), true)) {
                    $counter++;
                }
                
                $key = $counter;
            }
            
            return $this->set($key, $value);
        }
        
        /**
         * Retrieve the value in the map at the provided index
         * 
         * @param int $key The index to the map keys
         * 
         * @return mixed|Phabstractic\Data\Types\None The value at the list index
         * 
         */
        public function offsetGet($key) {
            return $this->find($key);
        } 
        
        /**
         * Unset the index and value on the map
         * 
         * Note: Like the unset method, this throws no error if the index
         *          doesn't exist.
         * 
         * @param int $key The index to the map keys
         * 
         * @return bool False if the index is improper, or not numeric, true otherwise
         * 
         */
        public function offsetUnset($key) {
            $this->remove($key);
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
            return $this->exists($key);
        }
        
        /**
         * Flatten into an array
         * 
         * Each array element tuple, the first element is the key, the second
         * the value.
         * 
         * @returns array
         * 
         */
        public function flatten() {
            $data = array();
            
            foreach ($this->keys as $key) {
                $data[] = array($key[self::KEY], $this->values[$key[self::INDEX]]);
            }
            
            return $data;
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
                'options' => array('strict' => $this->conf->strict,
                                   'typed' => $this->conf->typed,),
                'data' => $this->flatten(),
            ];
        }

        /* The following static functions exist as a sort of small
         * 'Map library' much like PHP arrays have their own set
         * of functions.  They're very basic but useful.
         */
        
        /**
         * Utility function for the static functions
         * 
         * This turns all the given arguments of a function into Maps
         * 
         * This simplified operations as they only have to act on Map objects
         * 
         * @static
         * 
         * @param array $args The arguments to turn into Map objects
         * 
         * @return array An array of Map objects constructed from the arguments
         * 
         */
        private static function mapArgs(array $args)
        {
            $mappedArgs = array();
            
            foreach ($args as $arg) {
                if (is_array($arg)) {
                    $mappedArgs[] = new Map($arg);
                } else if ($arg instanceof Map) {
                    $mappedArgs[] = $arg;
                } else {
                    if ($this->conf->strict) {
                        throw new TypesException\InvalidArgumentException(
                            'Phabstractic\\Data\\Types\\Map->mapArgs: ' .
                            'Function requires only Maps and Arrays');
                    } else {
                        $mappedArgs[] = new Map($arg);
                    }
                    
                }
                
            }

            return $mappedArgs;
        }
        
        /**
         * The difference between Map(s), takes a variable number of arguments (> 1)
         * 
         * @static
         * 
         * @param $maps,... A variable list of Map objects (or data that can be
         *                  turned into Map objects)
         * 
         * @return Map A difference map (containing all the entries from the
         *             first argument not present in others)
         * 
         * @throws Exception\InvalidArgumentException if less than two arguments
         *              are provided
         * 
         */
        public static function difference()
        {
            if (func_num_args() < 2) {
                throw new TypesException\InvalidArgumentException(
                    'Phabstractic\\Data\\Types\\Map->difference: ' .
                    'Difference requires 2 or more arguments');
            } else {
                $args = self::mapArgs(func_get_args());
                
                $arg0 = clone $args[0];
                unset($args[0]);
                foreach ($args as $arg) {
                    $sameKeys = array_uintersect(
                        $arg0->getKeys(),
                        $arg->getKeys(),
                        function($a,$b){
                            if (PhabstracticResource\ArrayUtilities::elementComparison($a,$b) == 0)
                                return 0;
                            else
                                return -1;
                        }
                    );
                    
                    foreach ($sameKeys as $k) {
                        $arg0->remove($k);
                    }
                    
                }
                
                return $arg0;
            }
            
        }
        
        /**
         * The intersection between Map(s), takes a variable number of
         * arguments (> 1)
         * 
         * @static
         * 
         * @param $maps,... A variable list of Map objects (or data that can be
         *                  turned into Map objects)
         * 
         * @return Map the intersection of the arguments: all the values
         *                  present in all the parameters
         * 
         * @throws Exception\InvalidArgumentException if less than two arguments
         *              are provided
         * 
         */
        public static function intersect()
        {
            if (func_num_args() < 2) {
                throw new TypesException\InvalidArgumentException(
                    'Phabstractic\\Data\\Types\\Map->intersect: ' .
                    'Intersect requires 2 or more arguments');
            } else {
                $args = self::mapArgs(func_get_args());
                
                $arg0 = clone $args[0];
                unset($args[0]);
                foreach($args as $arg) {
                    $diffKeys = array_udiff(
                        $arg0->getKeys(),
                        $arg->getKeys(),
                        function($a,$b){
                            if (PhabstracticResource\ArrayUtilities::elementComparison($a,$b) == 0)
                                return 0;
                            else
                                return -1;
                        }
                    );
                    
                    foreach ($diffKeys as $k) {
                        $arg0->remove($k);
                    }
                    
                }
                
                return $arg0;
            }
            
        }
        
        /**
         * Handy function that takes an array of keys (of any type), and
         * assigns them in a Map to $values
         * 
         * @static
         * 
         * @param array $keys The keys of the map
         * @param array $values The values to be associated with the
         *                      keys (in order)
         * @param bool  are we strict? (throw errors)
         * 
         * Note: Just like array_combine, the keys and values must be of the
         *       same length
         * 
         * @return false|Map returns false on failure (non-matching arrays)
         * 
         */
        public static function combine(array $keys, array $values, $strict = false)
        {
            if (count($keys) != count($values)) {
                return false;
            }
            
            $result = array();
            foreach ($keys as $k) {
                $val = each($values);
                $result[] = array( self::KEY => $k, self::INDEX => $val['value']);
            }
            
            return new Map($result, array('strict' => $strict));
        }
        
        /**
         * "returns an array using the values of the input array as keys and
         * their frequency in input as values."
         * 
         * This is where Maps really shine, as they can accept keys that are
         * not strings and integers
         * 
         * @static
         * 
         * @param Map $map The map to analyze
         * 
         * @return Map
         * 
         */
        public static function countValues(Map $map)
        {
            $result = new Map();
            foreach ($map as $val) {
                if (!$result->exists($val)) {
                   $result->set($val, 1);
                } else {
                   $result->set($val, $result->find($val) + 1);
                }
                
            }
            
            return $result;
        }
        
        /**
         * Merge two or more Maps, just like array_merge
         * 
         * @static
         * 
         * @param $maps,... A variable number of arguments, all to merge
         * 
         * @return Map
         * 
         * @throws Falcraft\Data\TypesException\InvalidArgumentException
         *              if less than two arguments are provided
         * 
         */
        public static function merge()
        {
            if (func_num_args() < 2) {
                throw new Exception\InvalidArgumentException(
                    'Phabstractic\\Data\\Types\\Map->merge: ' .
                    'Merge requires 2 or more arguments');
            } else {
                $args = self::mapArgs(func_get_args());
                
                $result = new Map();
                foreach ($args as $val) {
                    foreach ($val as $key => $ival) {
                        $result->set($key, $ival);
                    }
                    
                }
                
                return $result;
            }
            
        }
    }
}
