<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * This file contains the Structure class
 * 
 * This data structure mimics the C struct, sort of.  A structure has specific
 * fields to be taken care of or filled, some or all of these fields may have
 * restrictions placed upon them.  An object that falls under a particular
 * genus may need to implement a specific interface, but contain modifiable
 * members that need to be accessed generically.
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
    
    /* This class contains a static function for returning unique values
       for an array that is more object compatible. */
    $includes = array(// we are a configurable object
                      '/Features/ConfigurationTrait.php',
                      '/Features/Resource/ConfigurationInterface.php',
                      // we provide filters to tagged unions
                      '/Data/Types/Resource/FilterInterface.php',
                      // for our typed fields we use tagged unions
                      '/Data/Types/TaggedUnion.php',
                      // we return None on invalid key
                      '/Data/Types/None.php',
                      // we offer up the type of value in a field
                      '/Data/Types/Type.php',
                      // we throw these exceptions
                      '/Data/Types/Exception/InvalidArgumentException.php',
                      '/Data/Types/Exception/RangeException.php',
                      // for change value case
                      '/Resource/ArrayUtilities.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Features;
    use Phabstractic\Features\Resource as FeaturesResource;
    use Phabstractic\Data\Types\Resource as TypesResource;
    use Phabstractic\Data\Types\Type;
    use Phabstractic\Data\Types\Exception as TypesException;
    use Phabstractic\Resource as PhabstracticResource;
    
    /**
     * Structure class - Defines a structure with specific fields
     * 
     * Some objects need modifiable and runtime specific member
     * variables that can also be accessed with generic functions
     * 
     * Some fields may need to be restricted to specific types
     * Coding for such fields can be simplified by using tagged unions
     * 
     * CHANGELOG
     * 
     * 1.0: Created Structure - July 18th, 2013
     * 1.1: Updated Documentation - October 7th, 2013
     * 2.0: Updated file for Primus integration - August 25th, 2015
     * 3.0: pass fields as keys and initial values as values of the array
     *      reformatted for inclusion in phabstractic - July 20th, 2016
     * 3.0.1: implements configurationinterface - July 31st, 2016
     * 
     * @version 3.0.1
     * 
     */
    class Structure implements
        \ArrayAccess,
        FeaturesResource\ConfigurationInterface
    {
        use Features\ConfigurationTrait;
        
        /**
         * The associative array making up the member variables
         * 
         * Key = The identifier for the specific field
         * Value = The value for the specific field, may be a restricted tagged
         *         union
         * 
         * @var array
         * 
         */
        private $fields = array();
        
        /**
         * Defines a Structure
         * 
         * This is where you specify the keys of the given fields as well
         * as if any fields are restricted (tagged unions).  Passing
         * in a Restricitons object with tag (array) as an element of the array
         * is all that is needed to initialize a tagged union item.
         * 
         * Ex:  array('field1', 'field2', array($restrictions, 'field3'));
         * 
         * Options: strict - throw errors where applicable
         *          insensitive - make the keys (fields) insensitive
         *                        (only for construction) Only one key of a
         *                        particular value can then exist
         *          version2 - construct the fields using the algorithm in v2
         *                     false by default
         * 
         * 
         * @param array $allowed The field names as values,
         *                       arrays for tagged unions and restrictions
         * @param array $options The options for the object
         * 
         */
        public function __construct(
            array $allowed = array(),
            array $options = array()
        ) {
            
            if (!isset($options['insensitive'])) {
                $options['insensitive'] = true;
            }
            
            if (!isset($options['version2'])) {
                $options['version2'] = false;
            }
            
            $this->configure($options);
            
            if ( !$allowed ) {
                /* We haven't been given any information to
                   construct the structure */
                if ( $this->conf->strict ) {
                    throw new TypesException\InvalidArgumentException(
                                'Phabstractic\\Data\\Types\\Structure->__construct: ' .
                                'No Elements Given');
                }
                
                return;
            }
            
            if ($this->conf->version2 == false) {
                /* In the new version (version 3) we construct the fields from
                 * an array of indices (the field names) and values (the initial
                 * values of the fields, filters are turned into tagged unions)
                 */
                foreach ($allowed as $key => $val) {
                    if ($val instanceof TypesResource\FilterInterface) {
                        $this->fields[$key] = new TaggedUnion(
                            $val,
                            array('strict' => $this->conf->strict)
                        );
                        
                    } else {
                        $this->fields[$key] = $val;
                    }
                }
                
                if ($this->conf->insensitive) {
                    $this->fields = array_change_key_case($this->fields);
                }
                
            } else {
                // We are using the old version to construct the fields
                
                /* Since we are not using a map, and it wouldn't make sense
                 * to do so since having the value (tagged union) be the key
                 * would accomplish nothing, we generate an array with the keys
                 * being the specified tags.  These are merged in after the flip
                 * 
                 * EX: if value is array (restrictions, tagged unions, etc )
                 */
                $mergeArray = array();
                $arrayCount = count($allowed);
                
                if ($arrayCount) {
                    // go through every field
                    for ($i = 0; $i < $arrayCount; $i++) {
                        if (is_array($allowed[$i]) && count($allowed[$i]) == 2) {
                            // this field is a field name with a restrictions object
                            if ($allowed[$i][0] instanceof TypesResource\FilterInterface) {
                                $mergeArray[$allowed[$i][1]] =
                                    new TaggedUnion(
                                        $allowed[$i][0],
                                        array('strict' => $this->conf->strict)
                                    );
                            } else if ($allowed[$i][0] instanceof TaggedUnion) {
                                $mergeArray[$allowed[$i][1]] = $allowed[$i][0];
                            } else {
                                $mergeArray[$allowed[$i][1]] = $allowed[$i][0];
                            }
                            
                            // in preparation for the flip
                            unset($allowed[$i]);
                        }
                    }
                }
                
                if ($this->conf->insensitive) {
                    // make soon-to-be keys case insensitive
                    $allowed =
                        PhabstracticResource\ArrayUtilities::arrayChangeValueCase($allowed);
                }
                
                // If keys have been made insensitive, the last key value will be used
                $this->fields = array_flip($allowed);
                
                // Remove the values from fields (used to be keys)
                foreach ($this->fields as $k => $v) {
                    $this->fields[$k] = null;
                }
                
                // Merge in the alternative values (these couldn't be flipped)
                $this->fields = array_merge($this->fields, $mergeArray);
            }
        }
        
        /**
         * Returns the name of the fields allowed in the structure
         * 
         * @return array The names of the fields allowed in the structure
         * 
         */
        public function getElements()
        {
            if ($this->fields) {
                return array_keys($this->fields);
            }
            
            return array();
        }
        
        /**
         * This function compares field names in upper case and then uses the
         * defined field name
         * 
         * NOTE: This is only called if version2 is enabled
         * 
         * @param string $field
         * 
         * @return string $key The actual defined field name (case-sensitive)
         * 
         */
        private function denormalize($field)
        {
            foreach ($this->fields as $key => $anon) {
                if (strtoupper($field) == strtoupper($key)) {
                    return $key;
                }
                
            }
            
        }
        
        // The \ArrayAccess member functions
        
        /**
         * Set the offset in the list to the provided value
         * 
         * @param int $key The index to the list item
         * @param mixed $value The value to set to
         * 
         * @return intNew number of list elements
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if the value is not a stack index
         * 
         */
        public function offsetSet($key, $value)
        {
            try {
                $this->setElement($key, $value);
            } catch (TypesException\InvalidArgumentException $e) {
                if (strpos($e->getMessage(), 'TaggedUnion') === false) {
                    if ($this->conf->strict) {
                        throw new TypesException\RangeException(
                            'Phabstractic\\Data\\Types\\Structure->offsetSet: ' .
                            'OffsetSet key ' . $key . 'out of range.');
                    }
                } else {
                    throw $e;
                }
            }
            
        } 
        
        /**
         * Retrieve the value in the list at the provided index
         * 
         * @param int $key The index to the list item
         * 
         * @return mixed The value at the list index
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if the value is not a stack index
         * 
         */
        public function offsetGet($key)
        {
            try {
                return $this->getElement($key);
            } catch (TypesException\InvalidArgumentException $e) {
                throw new TypesException\RangeException(
                                'Phabstractic\\Data\\Types\\Structure->offsetGet: ' .
                                'OffsetSet key ' . $key . ' out of range.');
            }
            
        } 
        
        /**
         * Unset the index and value on the list
         * 
         * This isn't applicable in a structure so it is disabled
         * 
         * Note: Like the unset method, this throws no error if the index
         *          doesn't exist.
         * 
         * @param int $key The index to the list item
         * 
         * @return bool False if the index is improper, or not numeric, true
         *              otherwise
         * 
         */
        public function offsetUnset($key)
        { 
            return false;
        } 
        
        /**
         * Does the given key exist in the list?
         * 
         * Note: This method also returns false if the key is out of range
         * 
         * @param int $key The index into the stack
         * 
         * @return bool Existing?
         * 
         */
        public function offsetExists($key)
        {
            return array_key_exists($key, $this->fields);
        }
        
        /**
         * Checks to see if element identifier is present in structure
         * 
         * @param string Element identifier
         * 
         * @return bool
         * 
         * @throws Phabstractic\Data\Types\Exception\InvalidArgumentException
         *              if strict is enabled and the leement is not present
         * 
         */
        private function elementExists($element) {
            if (!array_key_exists($element, $this->fields)) {
                if ($this->conf->strict) {
                    throw new TypesException\InvalidArgumentException(
                                    'Phabstractic\\Data\\Trypes\\Structure' .
                                    '->elementExists: Element ' .
                                    'identifier not in allowed keys');
                } else {
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Returns the type being held in the structure field
         * 
         * @param string Element identifier
         * 
         * @return Phabstractic\Data\Types\Type|null The type of value held
         *
         */
        public function getElementType($element) {
            if ($this->conf->version2) {
                $element = $this->denormalize($element);
            }
            
            if (!$this->elementExists($element)) {
                return null;
            } else {
                if ($this->fields[$element] instanceof TaggedUnion) {
                    return $this->fields[$element]->getType();
                } else {
                    return Type\getValueType($this->fields[$element]);
                }
            }
        }
        
        /**
         * Returns the restrictions held by a given tagged union in the structure
         * 
         * Returns None() if there are no restrictions for that particular
         * field but returns plain null if the key is not in the fields and strict 
         * is not enabled
         * 
         * @param string $element the key for the particular field
         * 
         * @return null|Falcraft\Data\Types\Null|Falcraft\Data\Types\Restrictions
         *     The restrictions
         * 
         */
        public function getElementRestrictions($element)
        {
            if ($this->conf->version2) {
                $element = $this->denormalize($element);
            }
            
            if (!$this->elementExists($element)) {
                return null;
            } else {
                if ($this->fields[$element] instanceof TaggedUnion) {
                    return $this->fields[$element]->getRestrictions();
                } else {
                    return new None();
                }
            }
        }
        
        /**
         * Retrieve the value of a given element
         * 
         * Wraps tagged union functionality if need be
         * 
         * @param string $element The key/field name of the value
         * 
         * @return mixed|Falcraft\Data\Types\Null Null() if key not present
         * 
         */
        public function getElement($element)
        {
            if ($this->conf->version2) {
                $element = $this->denormalize($element);
            }
            
            if (!$this->elementExists($element)) {
                return new None();
            } else {
                if ($this->fields[$element] instanceof TaggedUnion) {
                    return $this->fields[$element]->get();
                }
                
                return $this->fields[$element];
            }
            
        }
        
        /**
         * Set a given field to a given value
         * 
         * Wraps tagged union functionality
         * 
         * @param string $element The field name
         * @param mixed $value The value to set the field to
         * 
         * @return bool True on success
         * 
         * @throws Phabstractic\Data\Types\Exception\InvalidArgumentException
         *              If key doesn't exist in fields
         * 
         */
        public function setElement($element, $value)
        {
            if ($this->conf->version2) {
                $element = $this->denormalize($element);
            }
            
            if (!$this->elementExists($element)) {
                return false;
            }
            
            if ($this->fields[$element] instanceof TaggedUnion) {
                $this->fields[$element]->set($value);
            } else {
                $this->fields[$element] = $value;
            }
            
            return true;
        }

        /**
         * Define all fields of the structure as null
         * 
         * Note:  This is why making Type::BASIC_NULL a
         *           settable state on TaggedUnion fields
         *           is a must unless you never clear
         * 
         */
        public function clear()
        {
            foreach($this->getElements() as $element) {
                $this->setElement($element, null);
            }
            
        }

        /**
         * Return internal array
         * 
         * @return array
         * 
         */
        public function toArray()
        {
            return $this->fields;
        }
        
        /**
         * Return internal array reference
         * 
         * @return &array
         * 
         */
        public function &arrayReference()
        {
            return $this->fields;
        }
        
    }
    
}
