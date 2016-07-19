<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Restrictions Data Type
 * 
 * This file contains the Restrictions 'data type'.  The Restrictions type
 * actually acts as a 'predicate'.  You define what data types are allowed in
 * the Restriction and use the Restriction instantiation in other classes to
 * restrict data types of variables.
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
    
    $includes = array(// Uses types enum to differentiate between data types
                      '/Data/Types/Type.php',
                      // Uses a set to keep track of allowed types
                      '/Data/Types/Set.php',
                      // Allows an alternative setinterface object to replace
                      '/Data/Types/Resource/SetInterface.php',
                      // Inherits class and implements filter
                      '/Data/Types/Resource/AbstractFilter.php',
                      '/Data/Types/Resource/FilterInterface.php',
                      // Is configurable, and thus implements interface
                      '/Features/ConfigurationTrait.php',
                      '/Features/Resource/ConfigurationInterface.php',
                      // throws the following exceptions
                      '/Data/Types/Exception/RangeException.php',
                      '/Data/Types/Exception/RuntimeException.php',
                      '/Data/Types/Exception/UnexpectedValueException.php',
                      '/Data/Types/Exception/InvalidArgumentException.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Features;
    use Phabstractic\Features\Resource as FeaturesResource;
    use Phabstractic\Data\Types\Type;
    use Phabstractic\Data\Types\Resource as TypesResource;
    use Phabstractic\Data\Types\Exception as TypesException;
    
    /**
     * Restrictions Class - Defines a predicate data structure that defines an
     * allowed list of types
     * 
     * An instantiated Restrictions class acts like a big encapsulated if...else
     * clause by defining a set of types and/or classes that can be allowed in a
     * particular context.  This is useful when defining TaggedUnions, which can
     * take on a restricted number of data types, or a RestrictedSet which can
     * (theoretically) act like a TaggedUnion but is usually worked as one
     * particular type.
     * 
     * CHANGELOG
     * 
     * 1.0: Documented Restrictions - March 5th, 2013
     * 1.1: Added getDefaultRestriction static method - May 16th, 2013
     * 1.2: Added checkRestrictedValues static method - May 16th, 2013
     * 1.3: Added instanceof operator in allowed,
     *              allowing for polymorphism - May 16th, 2013
     * 1.4: Renamed getDefaultRestriction(s) for clarity and
     *              clarified exceptions - October 7th, 2013
     * 2.0: Refactored and reformatted for inclusion in Primus - April 11th, 2015
     * 2.1: Implemented interface and inherited abstract for
     *              better decoupling - April 11th, 2015
     * 2.2: Added comparison static function - September 5th, 2015
     * 3.0: changed isAllowed to work with v3 of enumeration type
     *      added dynamic setting of allowed classes and types
     *      let $filter decide strictness in checkElements
     *      reformatted for inclusion in phabstractic - July 13th, 2016
     * 
     * @uses Phabstractic\Data\Types\Set to define the allowed types and the allowed
     *                               classes, both of which are unique sets.
     * @uses Phabstractic\Data\Types\Type to access predefined data types and utility
     *                                functions
     * 
     * @version 3.0
     * 
     */
    class Restrictions extends TypesResource\AbstractFilter implements
        TypesResource\FilterInterface,
        FeaturesResource\ConfigurationInterface
    {
        use Features\ConfigurationTrait;
        
        /**
         * The list of allowed data types
         * 
         * NOTE: An object of a particular type has the 'flag' Type::TYPED_OBJECT
         *       and the classes are defined in the member $classes set.
         * 
         * @var Phabstractic\Data\Types\Resource\SetInterface
         * 
         */
        private $allowed;
        
        /**
         * The list of allowed classes and interfaces
         * 
         * When Type::TYPED_OBJECT is present in allowed member set then this set
         * defines what classes the object is allowed to be.
         * 
         * @var Phabstractic\Data\Types\Resource\SetInterface
         * 
         */
        private $classes;

        /**
         * Retrieve the allowed types as an array of values
         * 
         * These values correspond to the constants found in Type::
         * 
         * @return array Allowed types
         * 
         */
        public function getAllowedTypes()
        {
            return $this->allowed->getPlainArray();
        }
        
        /**
         * Retrieve the allowed classes as an array of values
         * 
         * These values correspond to the class names allowed for a
         * Type::TYPED_OBJECT
         * 
         * @return array Allowed classes
         * 
         */
        public function getAllowedClasses()
        {
            return $this->classes->getPlainArray();
        }
        
        /**
         * Is the given value type in the TYPE class?
         * 
         * Checks the values of the constants in the enumeration
         * 
         * @param int $type The constant value to test
         * 
         * @return bool
         *
         */
        private function isProperType($type) {
            $typeClass = $this->conf->type_class;
            
            /* Check to see if the allowed types are in the
               'allowed types' Type::constants */
            $consts = $typeClass::getConstants();
            if (!in_array($type, $consts)) {
                return false;
            }
            
            return true;
        }
        
        /**
         * Does the given class or interface exist?
         * 
         * @returns bool
         * 
         */
        private function classExists($class) {
            if (!class_exists($class, $this->conf->autoload)) {
                // checks interfaces too
                if (!interface_exists($class, $this->conf->autoload)) {
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Set TYPED_OBJECT or BASIC_OBJECT appropriately depending on classes
         * 
         */
        private function setBasicOrTyped() {
            $type_class = $this->conf->type_class;
            
            /* If there are classes passed to the constructor automatically
               enable Type::TYPED_OBJECT */
            if (!$this->allowed->in($type_class::TYPED_OBJECT) &&
                    !empty($this->getAllowedClasses())) {
                $this->allowed->add($type_class::TYPED_OBJECT);
            }
            
            /* If Type::TYPED_OBJECT is available and Type::BASIC_OBJECT is
               present, it messes up the predicate logic */
            if ($this->allowed->in($type_class::TYPED_OBJECT) &&
                    $this->allowed->in($type_class::BASIC_OBJECT)) {
                $this->allowed->remove($type_class::BASIC_OBJECT);
            }
            
        }
        
        /**
         * Set the allowed types
         * 
         * If strict option is set (see constructor) then throw an error
         * if type doesn't exist in TYPE class
         * 
         * @param array $allowed The allowed types
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         * 
         */
        public function setAllowedTypes(array $allowed) {
            foreach ($allowed as $type) {
                if (!$this->isProperType($type)) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\Restrictions->setAllowedTypes: ' .
                        'Given Enumerator Value Doesn\'t Exist - ' .
                        $type );
                }
            }
            
            $this->allowed = new Set(
                $allowed,
                array('strict' => $this->conf->strict_sets,
                      'unique' => true,)
            );
            
        }
        
        /**
         * Add allowed type to existing set
         * 
         * If strict option is set (see constructor) then throw an error
         * if type doesn't exist in TYPE class
         * 
         * @param int $type The type to add (from an enumeration)
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         * 
         */
        public function addAllowedType($type) {
            if (!$this->isProperType($type)) {
                throw new TypesException\RangeException(
                    'Phabstractic\\Data\\Types\\Restrictions->addAllowedType: ' .
                    'Given Enumerator Value Doesn\'t Exist - ' .
                    $type );
            }
            
            
            
            $this->allowed->add($type);
        }
        
        /**
         * Remove an allowed type from ane existing set
         * 
         * If strict option is set (see constructor) then throw an error
         * if type doesn't exist in TYPE class
         * 
         * @param int $type The type to remove (from an enumeration)
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         * 
         */ 
        public function removeAllowedType($type) {
            if (!$this->isProperType($type)) {
                throw new TypesException\RangeException(
                    'Phabstractic\\Data\\Types\\Restrictions->addAllowedType: ' .
                    'Given Enumerator Value Doesn\'t Exist - ' .
                    $type );
            }
            
            $this->allowed->remove($type);
        }
        
        /**
         * Set the allowed classes
         * 
         * @param array $classes The allowed classes (as strings)
         * 
         */
        public function setAllowedClasses(array $classes) {
            /* Check that each class defined in the classes input actually
               exists, does NOT autoload by default */
            foreach ($classes as $class) {
                if (!$this->classExists($class)) {
                    throw new TypesException\RuntimeException(
                        'Phabstractic\\Data\\Types\\Restrictions->setAllowedClasses: ' .
                        'Undefined Class Classification: ' . $class);
                }
            }
            
            $this->classes = new Set(
                $classes,
                array('strict' =>$this->conf->strict_sets,
                      'unique' => true,)
            );
            
            $this->setBasicOrTyped();
        }
        
        /**
         * Add a class to an existing set of allowed classes
         * 
         * @param string $class The FQN of the class
         * 
         */
        public function addAllowedClass($class) {
            /* Check that each class defined in the classes input actually
               exists, does NOT autoload by default */
            if (!$this->classExists($class)) {
                throw new TypesException\RuntimeException(
                    'Phabstractic\\Data\\Types\\Restrictions->addAllowedClass: ' .
                    'Undefined Class Classification: ' . $class);
            }
            
            $this->classes->add($class);
            
            $this->setBasicOrTyped();
        }
        
        /**
         * Remove a class from an existing set of allowed classes
         * 
         * @param string $class The FQN of the class
         * 
         */
        public function removeAllowedClass($class) {
            $this->classes->remove($class);
            
            $this->setBasicOrTyped();
        }

        /**
         * Restrictions Class Constructor
         * 
         * Takes the allowed types ($allowed), the allowed classes (optional),
         * and the options pertinent to the Restrictions class.  Normalizes and
         * sets up the predicate structures necessary to evaluate data types
         * from the input data.
         * 
         * Options: autoload - Do we autoload classes when checking for
         *                     their existence?
         *          allowed - Set Interface Dependency Injection (optional)
         *          classes - Set Interface Dependency Injection (optional)
         *          type_class - The class to instantiate for type checking
         *          strict_sets - pass strict on to sets
         * 
         * @param array $allowed (Required) the allowed data types as defined
         *                                  in Type::enumerator
         * @param array $classes The classes to be allowed if $allowed
         *                       contains Type::TYPED_OBJECT
         * @param array $options The options ('autoload') for the given
         *                       Restrictions object
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if data type doesn't match any Type:: constants
         * @throws Phabstractic\Data\Types\Exception\RuntimeException
         *              if a class doesn't exist to match a class in the
         *              $classes member
         */
        public function __construct(
            array $allowed,
            array $classes = array(),
            $options = array() )
        {
            
            $this->configure($options);
            
            // autoload must be boolean
            if (!isset($this->conf->autoload) || !is_bool($this->conf->autoload)) {
                $this->conf->autoload = false;
            }
            
            if (!isset($this->conf->type_class)) {
                $this->conf->type_class = 'Phabstractic\\Data\\Types\\Type';
            }
            
            if (!isset($this->conf->strict_sets)) {
                $this->conf->strict_sets = true;
            }

            /* $allowed = array_unique($allowed);
            $classes = array_unique($classes) */;
            
            $type_class = $this->conf->type_class;
            
            /* Check to see if the allowed types are in the
               'allowed types' Type::constants */
            $consts = $type_class::getConstants();
            foreach ($allowed as $type) {
                if (!in_array($type, $consts)) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\Restrictions->__construct: ' .
                        'Cannot Construct New Predicate With Enumerator Value ' .
                        $type );
                }
            }
            
            /* Make allowed types into a set, strictly, guaranteeing no
               duplicate types */
            if (isset($this->conf->allowed) && $this->conf->allowed) {
                if ($this->conf->allowed instanceof TypesResource\SetInterface) {
                    $this->allowed = &$this->conf->allowed;
                } else {
                    throw new TypesEXception\RuntimeException(
                        'Phabstractic\\Data\\Types\\Restrictions->__construct: ' .
                        'Cannot Construct New Predicate With Non Set Interface');
                }
            } else {
                $this->allowed = new Set(
                    $allowed,
                    array('strict' => $this->conf->strict_sets,
                          'unique' => true));
            }
            
            /* Check that each class defined in the classes input actually
               exists, does NOT autoload by default */
            foreach ($classes as $class) {
                if (!$this->classExists($class)) {
                    throw new TypesException\RuntimeException(
                        'Phabstractic\\Data\\Types\\Restrictions->__construct: ' .
                        'Undefined Class Classification: ' . $class);
                }
            }
            
            /* Make allowed classes into a set, strictly, gauranteeing no
               duplicate classes */
            if ($this->conf->classes) {
                if ($this->conf->classes instanceof TypesResource\SetInterface) {
                    $this->classes = &$this->conf->classes;
                } else {
                    throw new TypesException\RuntimeException(
                        'Phabstractic\\Data\\Types\\Restrictions->__construct: ' .
                        'Cannot Construct New Predicate With Non Set Interface');
                }
                
            } else {
                $this->classes = new Set(
                    $classes,
                    array('strict' =>$this->conf->strict_sets,
                          'unique' => true));
            }
            
            $this->setBasicOrTyped();
        }
        
        /**
         * Is a type allowed in this restriction?
         * 
         * This steps through a given type, be it Phabstractic\Data\Types\Type,
         * Integer, String, or Array.  If it's not a Type object, to specify
         * a particular typed class you provide array( type [Type, Integer,
         * String], className) because an array can be passed with one argument
         * to turn into a Type object it still converts the array[0] to a Type
         * object
         * 
         * For flexibility in type checking (if container object is strict
         * or not) we provide a strict option in the function in addition to the
         * object at constructor time.
         * 
         * NOTE: if Phabstractic\Data\Types\Type throws an \UnexpectedValueException,
         *       we just silently return false, unless strict parameter is true
         * 
         * @param Phabstractic\Data\Types\Type|array|int|string $type The type to
         *            check
         * @param bool $strict Should we throw errors based on match?
         * 
         * @return bool Allowed or not?
         * 
         * @throws Phabstractic\Data\Types\Exception\UnexpectedValueException
         *              if OPTION strict rethrows the type construction error
         * 
         */
        public function isAllowed($type, $strict = false)
        {
            $typeClass = $this->conf->type_class;
            $testClass = '';  /* we use this variable to store the class name
                                 of a TYPED_OBJECT below */
            
            // Is $type an actual enumerated instance?
            if (!($type instanceof Type)) {
                
                // No it isn't, groom the $type variable
                if (is_int($type)) {
                    try {
                        /* construct as number, same as constructing with
                           a constant, note that in v3 of Enumeration that the
                           integers associated with a type are automatically
                           generated, previous values may not work */
                        $type = new $typeClass($type);
                        
                    } catch (\UnexpectedValueException $e) {
                        // Remember Type enum eval'd code returns root Exception
                        if ($strict) {
                            throw new TypesException\UnexpectedValueException(
                                'Phabstractic\\Data\\Types\\Restrictions->isAllowed: ' .
                                'Incorrect integer given for type' );
                        }
                        
                        return false;
                    }
                    
                } else if (is_string($type)) {
                    /* construct from string (see Type.php)
                       EX: BASIC_INT, RESOURCE, FUNC ... */
                    $type = Type\stringToType($type);
                    if ($type == null) {
                        /* if we are strict, also throw an error here, just
                           like we do if it is an integer */
                        if ($strict) {
                            throw new TypesException\UnexpectedValueException(
                                'Phabstractic\\Data\\Types\\Restrictions->isAllowed: ' .
                                'Incorrect string given for type' );
                        }
                        
                        return false;
                    }
                    
                } else if (is_array($type)) {
                    // We are dealing with an object type
                    if (count($type) == 2) {
                        /* Is array[0] (type) actual enumeration instance
                           OR is it an integer that equals the enumeration type? */
                        if (
                            (($type[0] instanceof Type) &&
                                ($type[0]->get() == $typeClass::TYPED_OBJECT)) ||
                            (is_int($type[0]) && $type[0] == $typeClass::TYPED_OBJECT)) {
                            /* Did we get passed an actual object in our array?
                               If so... convert it to a class name */
                            $obj = $type[1];
                            
                            if (is_object($obj)) {
                                /* This allows you to pass the class name of an object
                                   or an actualy object itself as element[2], to see if
                                   that object would pass */
                                $testClass = get_class($obj);
                            } elseif (!is_string($obj)) {
                                // We only accept strings, not integers
                                if ($strict) {
                                    throw new TypesException\UnexpectedValueException(
                                        'Phabstractic\\Data\\Types\\Restrictions->isAllowed: ' .
                                        'Passed array has improper value (not string).');
                                } else {
                                    return false;
                                }
                            } else {
                                $testClass = $obj;
                            }
                            
                            /* NOTE! This code is obsolete with v3 of enumeration
                               data type, we can no longer store an
                               instance of the class in the enumeration as
                               the value must reflect only the enumerations
                               values. */
                            
                            /* $obj = $type[1];
                            // Object is not instance but string of class
                            if (!is_object( $obj)) {
                                $obj = new $obj();
                            }
                            
                            $type = new $typeClass(); // eliminate array
                            // Temporarily store object for use below
                            $type->set($obj); */
                        } else {
                            // array passed first element must be Type::TYPED_OBJECT
                            if ($strict) {
                                throw new TypesException\UnexpectedValueException(
                                    'Phabstractic\\Data\\Types\\Restrictions->isAllowed: ' .
                                    'TYPED_OBJECT Not present in array');
                            }
                            
                            return false;
                        }
                           
                    } else {
                        // we are given an array with unexpected length
                        throw new TypesException\UnexpectedValueException(
                            'Phabstractic\\Data\\Types\\Restrictions->isAllowed: ' .
                            'Passed array has more than two elements.');
                    }
                    
                } else {
                    return false;
                }
                
            }
            
            // Now $type is groomed to be a Type object, if not TYPED_OBJECT
            
            // check type or class as appropriate
            if ($testClass === '') {
                /* Not a typed object (post grooming)
                   Just check against the set of our allowed types */
                return $this->allowed->in($type->get());
            } else {
                /* v3.0: We now operate solely on a string of the class to check
                         against, not the actual instantiated object, as we did
                         in v1.3.  First we check to see if the class either
                         already exists, autloading if the autoload option is set.
                         Then we check to see if it is in the allowed_classes set.
                         If it isn't, we check to see if it derives from any class
                         in the allowed_classes set. */
                
                if (class_exists($testClass, $this->conf->autoload)) {
                    if (!($this->classes->in($testClass))) {
                        /* It is NOT in the allowed_classes, BUT... is the desired
                           class a subclass or implementation of a required type?
                           since v1.3 - */
                        foreach ($this->classes->iterate() as $class) {
                            // usable only since PHP 5.0.3
                            if (is_subclass_of($testClass, $class)) {
                                // derives from a required class/interface
                                return true;
                            }
                        }
                    } else {
                        // It IS in the allowed_classes set
                        return true;
                    }
                } else {
                    // class doesn't exist anywhere, if strict, throw error
                    if ($strict) {
                        throw new TypesException\UnexpectedValueException(
                            'Phabstractic\\Data\\Types\\Restrictions->isAllowed: ' .
                            'Given class doesn\'t exist.');
                    }
                }
            }
            
            // Default
            return false;
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
            $typeClass = $this->conf->type_class;
            
            $ret = array ('options' => array('autoload' => $this->conf->autoload,
                                   'allowed' => $this->conf->allowed,
                                   'classes' => $this->conf->classes,
                                   'type_class' => $this->conf->type_class,
                                   'strict_sets' => $this->conf->strict_sets,));
            
            $types = array();
            foreach ($this->allowed->iterate() as $value) {
                $type = new $typeClass($value);
                $types[] = $type->getConst();
            }
            
            $classes = $this->classes->getPlainArray();
            
            $ret['allowed'] = $types;
            $ret['classes'] = $classes;
            
            return $ret;
        }

        /**
         * Return a basic free form restrictions
         * 
         * Note: Cannot permit a typed object
         * 
         * @static
         * 
         * @return Phabstractic\Data\Type\Restrictions The restrictions object
         * 
         */
        public static function getDefaultRestrictions()
        {
            return new Restrictions( array( Type::BASIC_BOOL,
                                            Type::BASIC_INT,
                                            Type::BASIC_STRING,
                                            Type::BASIC_ARRAY,
                                            Type::BASIC_OBJECT,
                                            Type::BASIC_RESOURCE,
                                            Type::BASIC_NULL,
                                            Type::BASIC_CLOSURE,
                                            Type::BASIC_FUNCTION,
                                            Type::BASIC_FLOAT,
                                            Type::BASIC_CALLABLE,)
                                    );
        }
        
        /**
         * Checks values to see if they fit the restrictions defined for the
         * Restricted object
         * 
         * This goes through each value, grabs its type, and compares it
         * against the Restrictions
         * 
         * @static
         * 
         * @param array $values The values to check against the restrictions
         * @param Resource\FilterInerface The filter to run them through
         * @param bool $strict Throw errors?
         * 
         * @return bool Valid types?
         * 
         * @throws Phabstractic\Data\Types\Exception\InvalidArgumentException
         *      if the value is untypeable
         * 
         */
        public static function checkElements(
            array $values,
            Resource\FilterInterface $filter,
            $strict = null
        ) {
            foreach ($values as $value) {
                if (($typeEnum = Type\getValueType($value)) === false) {
                    throw new TypesException\InvalidArgumentException(
                        'Phabstractic\\Data\\Types\\Restrictions::checkElements: ' .
                        'Untypeable Value');
                }
                
                if ($filter->isAllowed($typeEnum, $strict) == false) {
                    return false;
                }
                
            }
            
            return true;
        }
        
        /**
         * Alias for checkElements()
         * 
         * Backwards compatibility
         * 
         * @param array $values The values to check against the restrictions
         * @param Resource\FilterInterface The filter to run them through
         * @param bool $strict Throw errors?
         * 
         * @return bool Valid types?
         * 
         * @throws Phabstractic\Data\Types\Exception\InvalidArgumentException
         *          if the value is untypeable
         * 
         */
        public static function checkRestrictedValues(
            array $values,
            $restrictions,
            $strict = null
        ) {
            return static::checkElements($values, $restrictions, $strict);
        }
        
        /**
         * Compare Restrictions
         * 
         * This allows restrictions to be compared despite their configurations
         * This comes up when trying to compare restrictions like so:
         * 
         * $this->restrictions == $that->restrictions
         * 
         * The above takes into count configurations, so this avoids that.
         * 
         * @param Phabstractic\Data\Types\Restrictions The first restriction
         * @param Phabstractic\Data\Types\Restrictions The second restriction
         * 
         * @return bool Equal?
         * 
         */
        public static function compare(Restrictions $R1, Restrictions $R2)
        {
            $types1 = $R1->getAllowedTypes();
            $types2 = $R2->getAllowedTypes();
            
            if ((!array_diff($types1, $types2) &&
                 !array_diff($types2, $types1))) {
                $classes1 = $R1->getAllowedClasses();
                $classes2 = $R2->getAllowedClasses();
                if ((!array_diff($classes1, $classes2) &&
                     !array_diff($classes2, $classes1))) {
                    return true;
                }
            }
            
            return false;
        }
        
    }
    
}
