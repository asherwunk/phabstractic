<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Cross Version Enumerator Implementation
 * 
 * This file contains the Enum class which when instantiated can define an
 * enumeration that can then be 'baked' into the appropriate namespace
 * (or global namespace) and accessed, once baked, as an enumeration
 * class/object of itself.
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
    
    $includes = array('/Features/ConfigurationTrait.php',
                      '/Data/Types/Exception/CodeGenerationException.php',
                      '/Data/Types/Exception/RuntimeException.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Features;
    use Phabstractic\Data\Types\Exception;
    
    /**
     * Enumeration Class - Creates and Defines OO-defined Enumeration
     * Classes/Objects
     * 
     * The Enum class which when instantiated can define an enumeration that 
     * can then be 'baked' into the appropriate namespace (or global namespace) 
     * and accessed, once baked, as an enumeration class/object of itself.  
     * 
     * NOTE: This class no longer uses the SPL Enum documented as of version 3.0
     * 
     * A static 'registry' makes sure that the programmer doesn't try to 
     * instantiate a fully qualified enumerator twice, and throws an error.
     * 
     * NOTE: If using the custom built class, to compare the value to defined
     *       enumerator constants, perform a string typecast first:
     *           (string) $myEnum == MyEnum::constant1
     *       If you have to compare integer values try
     *           intval( (string) $myEnum ) as well
     *       If you use these methods, it should not matter if the object is
     *       SplEnum or not. (SplEnum no longer supported)
     * 
     * CHANGELOG
     * 
     * 1.1:  added createInstance static function - April 11th, 2013
     * 1.2:  added \ identifier before ReflectionClass - April 11th, 2013
     * 1.3:  added namespace braces
     * 1.4:  fixed throw statements to include new operator
     * 1.5:  added case checking for options arrays
     * 1.6:  made class constants count()-able. - April 21st, 2013
     * 1.7:  fully qualified classes in eval code - April 21st, 2013
     * 1.8:  added support for Set Falcraft\Data\Types, used in enums, and
     *           constants - April 22nd, 2013
     * 1.9:  added exception handling to gracefully exit enum generation and
     *           return false added code niceties and a bit more
     *           documentation - May 3rd, 2013
     * 1.10:  used standard object for options - May 27th, 2013
     * 1.11:  Fixed up some loopholes and cracks - May 27th, 2013
     * 1.12:  Changed default to default to 0 when missing - August 6th, 2013
     * 1.13:  Removed standard object and set dependency
     *            (loops, duh) - February 2nd, 2014
     * 2.0:   Refactored to use the Configuration Feature
     *           (Features/Configuration.php) - April 7th, 2015
     * 2.0.1: Added enum list check in code generator - April 7th, 2015
     * 2.0.2: Added more flexible options in constructor - April 10th, 2015
     * 2.0.3: Ensured Default Values using static property - September 4th, 2015
     * 3.0:   reformatted for inclusion in phabstractic
     *        reverted constants to a non-associative array
     *        added value checking in generated class - July 8th, 2016
     * 3.0.1: refined value checking in generated class - July 17th, 2016
     * 
     * @version 3.0.1
     * 
     */
    class Enumeration
    {
        use Features\ConfigurationTrait;
        
        /**
         * Keeps track of already defined enumerations internally
         * 
         * @var Falcraft\Data\Types\Set
         */
        private static $enums;
        
        /**
         * The name of the class to be generated
         * 
         * @var string
         */
        private $className = '';
        
        /**
         * Whether the class has defined itself
         * 
         * @var bool
         */
        private $baked = false;
        
        /**
         * The elements, name => value, of the enumeration
         * 
         * @var array
         */
        private $constants = null;
        
        /**
         * The default enumerator element, when no element is specified on instantiation
         * 
         * @var string
         */
        private $default = '';
        
        /**
         * The namespace under which the enumerator class is to be defined.
         * 
         * @var string
         */
        private $namespace = '';
        
        /**
         * Generates the required class code and evaluates it
         * 
         * The function that pieces the generated code together and evaluates it
         * It defines a custom enumerator class, SplEnum is not supported from
         * version 3 onwards.  Use ->get() to explicitly get the value of
         * both SplEnum- (NOT SUPPORTED) and custom-derived classes.
         * 
         * NOTE: The eval'd code can throw an \UnexpectedValueException (root)
         * 
         * @param string $className The name of the generated class
         * @param array $values     The constant values in the form, identifier => value
         * 
         * @return bool             If enumerator was generated at all (without errors)
         * 
         * @throws \Phabstractic\Data\Types\Exception\CodeGenerationException
         *              if enum couldn't be created
         * @throws \Phabstractic\Data\Types\Exception\RuntimeException
         *              if enum class already exists
         * 
         */
        private function createEnum($className, array $values)
        {
            if (isset($this->namespace) && in_array($this->namespace . '\\' . $className, self::$enums)) {
                throw new Exception\RuntimeException(
                    '\\Phabstractic\\Data\\Types\\Enumeration->createEnum: ' .
                        $this->namespace . '\\' . $className .
                        ' Enumeration Already Defined');
            }
            
            // Start with blank code
            $classCode = '';
            
            /* Place namespace identifier at top of 'code', as eval statements
               operate outside of the namespace context of the code executing 
               the eval statement */
            if (isset($this->namespace) && $this->namespace) {
                $classCode .= 'namespace ' . $this->namespace . ";\n\n";
            }
            
            /* Create the class and its internal variable */
            $classCode .= "class $className implements \Countable {
                private \$value;\n";
            
            /* Version 3.0: Uses reflection to check values against constants */
            $classCode .= "    private \$reflector;\n";
            
            /* This creates the default default value (if no default value 
               is specified) as well as creates the constant definition 
               from the values passed to the function */
            $defaultTemp = 0;
            $counter = 0;
            foreach ($values as $val) {
            //foreach ($values as $identifier => $val) {
                $classCode .= "const $val = $counter;\n\t";
                // !$defaultTemp ? $defaultTemp = $identifier : null;
                $counter++;
            }
            
            /* If the default option has been defined, use that one,
               otherwise Use the default default we defined above */
            if (isset( $this->default ) && $this->default) {
                $classCode .= "\nconst __default = self::" .
                    $this->default . ";\n\n\t";
                $classCode .= "\npublic static \$__sdefault = self::" .
                    $this->default . ";\n\n\t";
            } else {
                $classCode .= "\nconst __default = 0;\n\n\t";
                $classCode .= "\npublic static \$__sdefault = 0;\n\n\t";
            }
                
            /* Build the rest of the custom object, somewhat self
               explanatory.  There is no 'set' method becuse if you want 
               to get a new enumerator value you would pass that value to a 
               new enumerator instance.  i.e. $aColor = new Color(Color::Red).
            
               The constructor function is the 'set' method then, and 
               checks to make sure the value provided for the new
               enumerator instance is available as a constant in the class 
               through the use of a ReflectionClass
            
               Throws UnexpectedValueException when the constant value is
                   not available */
            
            /* version 1.2: added \ qualifier before
               ReflectionClass - April 11th, 2013 */
            $classCode .= "public function __construct(\$initValue = null)
                {\n";
                    if ($this->default) {
                        $classCode .= "self::\$__sdefault = self::" .
                            $this->default . ";";
                    }
            $classCode .= "\n
                    \$this->reflector = new \\ReflectionClass(\$this);
                    if (\$initValue === null) {
                        \$this->value = self::\$__sdefault;
                    } elseif (\$this->check(\$initValue)) {
                        if ( is_string(\$initValue) )
                        {
                            \$this->value = \$this->reflector->getConstants()[\$initValue];
                        } elseif (is_int(\$initValue)) {
                            \$this->value = \$initValue;
                        }
                    } else {
                        throw new \UnexpectedValueException(\"Value \$initValue not a const in enum $className\");
                    }
                }
                
                // version 3.0: checks against the constants defined
                protected function check( \$value )
                {
                    if (in_array( \$value, array_keys(\$this->reflector->getConstants()), true))
                    {
                        return true;
                    }
                    
                    if (in_array( \$value, \$this->reflector->getConstants(), true)) {
                        return true;
                    }
                    
                    return false;
                }
                
                public function __toString()
                {
                    return (string) \$this->value;
                }
                
                public function get()
                {
                    return \$this->value;
                }
                
                // version 3.0: now checks against the constants defined
                public function set( \$value )
                {
                    if ( \$this->check( \$initValue ) )
                    {
                        \$this->value = \$this->reflector->getConstants()[\$initValue];
                    } else {
                        throw new \UnexpectedValueException(\"Value not a const in enum $className\");
                    }
                }\n";

            // How many enumerator categories are there?
            $classCode .= "public function count() {
                return count(\$this->reflector->getConstants());
            }
            
            static public function getConstants() {
                \$test = new $className();
                \$reflect = new \\ReflectionClass(\$test);
                return \$reflect->getConstants();
            }\n";
            
            // Close up the class definition
            $classCode .= '}';
            
            try {
                eval( $classCode );
            } catch (\Exception $e) {
                throw new Exception\CodeGenerationException(
                    '\\Phabstractic\\Data\\Types\\Enumeration->createEnum: ' .
                    'Unable to generate ' . $this->className . ' due to internal ' .
                    'error.'); // The enumerator couldn't be generated.
            }
            
            self::$enums[] = ( isset($this->namespace) ? $this->namespace : '' ) .
                '\\' . $this->className;
            return true; // The enumerator was generated.
        }
    
        /**
         * Enum Construction, Set Up Enum Parameters
         * 
         * This sets up the parameters for the enum to be generated.
         * It only generates the enum class when the option 'bake' is set to
         * true
         * 
         * Options:
         * 
         * default => default constant identifier when instantiating class
         * namespace => namespace to define this enumerator class in
         * bake => define the class immediately with the given parameters
         * 
         * @param string $className  The name of the generated class
         * @param array $values      The constant values in the form,
         *                               identifier => value
         * @param array|Zend\Config\Config|string $options  See above options
         * 
         */
        public function __construct($className, array $values, $options = array())
        {
            // version 2.1 of ConfigurationTrait handles configuraiton formatting
            $this->configure($options);
            
            if (!self::$enums) {
                 self::$enums = array();
            }
            
            // use the set function, helpful for error detection
            $this->setClassName($className);
            $this->constants = $values;
            if (isset($this->conf->default) && $this->conf->default) {
                $this->default = $this->conf->default;
            }
            
            if ($this->conf->namespace) {
                // use the set function, helpful for error detection
                $this->setNamespace($this->conf->namespace);
            }
        
            if (isset($this->conf->bake) && $this->conf->bake) {
                $this->bake();
            }
            
        }
        
        /**
         * Generates the enumerator class in the desired namespace.
         * 
         * Generates the enumerator class in the desired namespace using the
         * given parameters, in essence solidifying them in place. Once an
         * enum class is 'baked' it cannot be changed.
         * 
         * This class also pushes the qualified name onto a static stack so
         * that future enum classes defined through this method don't clash in 
         * name, allowing the class to raise a RangeException
         * 
         */
        public function bake()
        {
            if (!$this->baked) {
                if (!$this->createEnum($this->className, $this->constants)) {
                    return false;
                }
                
                $this->baked = true;
                self::$enums[] = $this->namespace . '\\' . $this->className;
                return true;
            }
            
        }
        
        /**
         * Sets the name of the class to be generated
         * 
         * Checks to make sure the proposed fully qualified name doesn't clash
         * with an already created enumerator object, raising a RangeException 
         * error
         * 
         * @param string $className The name of the class to be generated
         * 
         * @throws Exception\RuntimeException when the qualified name
         *             clashes with an already created enumerator class
         * @throws Exception\RuntimeException when the class has already 
         *             been baked
         * 
         */
        public function setClassname($className)
        {
            if (!$this->baked) {
                if (in_array($this->namespace . '\\' . $className, self::$enums)) {
                    throw new Exception\RuntimeException(
                        '\\Phabstractic\\Data\\Types\\Enumeration->setClassname:' .
                            $this->namespace . '\\' . $className .
                            ' Enumeration Already Defined');
                }
                
                $this->className = $className;
            } else {
                throw new Exception\RuntimeException(
                        '\\Phabstractic\\Data\\Types\\Enumeration->setClassname: ' .
                            $this->namespace . ' -> ' . $className .
                            ' Enumeration Already Baked');
            }
            
        }
        
        /**
         * Retrieve class name
         * 
         * @return string The class name of the typed object
         * 
         */
        public function getClassName()
        {
            return $this->className;
        }
        
        /**
         * Check to see if class has been defined or 'baked'
         * 
         * @return bool Whether the enumerator has been baked.
         * 
         */
        public function isBaked()
        {
            return $this->baked;
        }
        
        /**
         * Sets the enumerator elements (constants)
         * 
         * @param array $constants An array containing the constant
         *              names => values
         * 
         * @throws Exception\RuntimeException when the class has already
         *              been generated (baked)
         * 
         */
        public function setConstants(array $constants)
        {
            if (!$this->baked) {
                $this->constants = $constants;
            } else {
                throw new Exception\RuntimeException(
                    '\\Phrabstractic\\Data\\Types\\Enumeration->setConstants: ' .
                    $this->className . ' Enumeration Constants Already Baked');
            }
            
        }
        
        /**
         * Gets the enumerator elements (constants)
         * 
         * @return array Array of enumerator elements (constants)
         * 
         */
        public function getConstants()
        {
            return $this->constants;
        }
        
        /**
         * Add a single constant to the constant list
         * 
         * This method overrides any constant names already defined with the 
         * new proposed values
         * 
         * @param string $name The name of the constant
         * 
         * @throws Exception\RuntimeException if the class has already
         *             been baked
         * 
         */
        public function addConstant($name)
        {
            if (!$this->baked) {
                $this->constants[] = $name;
            } else {
                throw new Exception\RuntimeException(
                    '\\Phabstractic\\Data\\Types\\Enumeration->addConstant: ' .
                    $this->className . ' Enumeration Constants Already Baked');
            }
            
        }
        
        /**
         * Add multiple constants to the constant list
         * 
         * This method employs the array structure identifier => value.
         * Overrides any constant names already defined with the new proposed 
         * values
         * 
         * @param array $constants The array of constants to add to the list
         * 
         * @throws Exception\RuntimeException if the class has already
         *             been baked
         * 
         */
        public function addConstants(array $constants)
        {
            if ( !$this->baked ) {
               $this->constants = array_unique(array_merge($this->constants, $constants));
            } else {
                throw new Exception\RuntimeException(
                    '\\Phabstractic\\Data\\Types\\Enumeration->addConstants: ' .
                    $this->className . ' Enumeration Constants Already Baked');
            }
            
        }
        
        /**
         * Remove a constant from the constant list
         * 
         * This method removes a constant from the constant list only if the
         * class has not been generated, otherwise it throws an error
         * 
         * @param string $name Name of the constant
         * 
         * @throws Exception\RuntimeException if the class has already
         *             been baked
         * 
         */
        public function removeConstant($name)
        {
            if (!$this->baked) {
                $this->constants = array_unique(array_diff($this->constants, array($name)));
                //unset( $this->constants[] );
            } else {
                throw new Exception\RuntimeException(
                    '\\Phabstractic\\Data\\Types\\Enumeration->removeConstant: ' .
                    $this->className . ' Enumeration Constants Already Baked');
            }
            
        }
        
        /**
         * Set the default of the generated enumerator class
         * 
         * @param $name The IDENTIFIER of the default
         *              [constant keys] (i.e. Red, Apple, ...)
         * 
         * @return bool Successful?
         * 
         * @throws Exception\RuntimeException when the class has already 
         *             been baked
         * 
         */
        public function setDefault($name)
        {
            if (!$this->baked) {
                if (in_array($name, $this->constants)) {
                    $this->default = $name;
                    return true;
                } else {
                    /* silently set default to nothing if default is not defined
                       in constant list. Does not generate an error */
                    $this->default = '';
                    return false;
                }
                
            } else {
                throw new Exception\RuntimeException(
                    '\\Phabstractic\\Data\\Types\\Enumeration->setDefault: ' .
                    $this->className . ' Enumeration Default Already Baked');
            }
            
        }
        
        /**
         * Retrieve the default of the generated enumerator class
         * 
         * @return string The IDENTIFIER of the default [constant keys] (i.e. Red, Apple, ...)
         * 
         */
        public function getDefault()
        {
            return $this->default;
        }
        
        /**
         * Retrieve value of default of the enumerator class (identifier=>VALUE)
         * 
         * Note that no constant in an enumerated class can be null
         * 
         * @return mixed|null Any value that meets the constants requirements, 
         *                    or NULL if there is no default OR the default is
         *                    not in the constants list
         * 
         */
        public function getDefaultValue()
        {
            if (array_key_exists($this->default, $this->constants)) {
                return $this->constants[$this->default];
            } else {
                return null;
            }
            
        }
        
        /**
         * Sets up the namespace to be used when generating the enumeration class
         * 
         * This sets the namespace that will be used when the enumerator class
         * is generated. How?  Eval statements operate in the global namespace,
         * or basically a clean slate enabling us to put a namespace statement 
         * at the beginning of our generated code. Any namespace will do, if 
         * there is no namespace, you must access your enumerator using the 
         * global namespace.  ex: \Months::January, \Colors::Red, ...
         * 
         * @param string $namespace The namespace specified
         * 
         * @throws Exception\RuntimeException when the class/namespace
         *              has already been generated via Enum
         * @throws Exception\RuntimeException if the class has already
         *              been generated/baked.
         * 
         */
        public function setNamespace($namespace)
        {
            if (!$this->baked) {
                if (in_array($namespace . '\\' . $this->className, self::$enums)) {
                    throw new Exception\RuntimeException(
                        '\\Phabstractic\\Data\\Types\\Enumeration->setNamespace: ' .
                        $namespace . '\\' . $this->className .
                        ' Enumeration Already Defined');
                }
                
                $this->namespace = $namespace;
            } else {
                throw new Exception\RuntimeException(
                    '\\Phabstractic\\Data\\Types\\Enumeration->setNamespace: ' .
                    $this->namespace . '\\' . $this->className .
                    ' Enumeration Namespace Already Baked');
            }
            
        }
        
        /**
         * Retrieves the namespace for the generated enumerator
         * 
         * @return string The namespace for the generated enumerator
         * 
         */
        public function getNamespace()
        {
            return $this->namespace;
        }
        
        /**
         * Instantiate an instance of the generated class
         * 
         * The generated instance will be given the value parameter
         * It's impossible to instantiate a generated class with a null value, thus
         * no constant in the enumeration can be null
         * 
         * @param mixed $value Any constant friendly value found in the enumerator
         * 
         * @return object An instance of the generated enumerator class
         * 
         * @throws Exception\CodeGenerationException if instantiated witho a
         *             null value
         * 
         */
        public function getInstance($value)
        {
            // Set up the qualified name, with and without namespace (global otherwise = \)
            if ($this->namespace) {
                $qualifier = $this->namespace . '\\' . $this->className;
            } else {
                $qualifier = '\\' . $this->className;
            }
            
            if ($value == null) {
                throw new Exception\CodeGenerationException(
                    '\\Phabstractic\\Data\\Types\\Enumeration->getInstance: ' .
                    $this->className . ' Enumeration Instantiated Without Value');
            }
            
            if (!$this->baked) {
                $this->bake();
            }
            
            return new $qualifier($value);
        }
        
        /**
         * Encapsulate an apprioriate value inside an enumeration object
         * 
         * Ex: 404 could turn into HTTP\ResponseCode(404);
         * 
         * @param string $identifier The enum to use, as existing in the static variable
         * @param null $value The value to use in the newly created object
         * 
         * @return object
         * 
         */
        public static function createEnumeration($identifier, $value = null)
        {
            if (in_array($identifier, self::$enums)) {
                if ($value) {
                    return new $identifier($value);
                } else {
                    return new $identifier();
                }
                
            }
            
        }
    
        /**
         * Straight out define an enumerator class without instantiating
         * 
         * Takes care of the instantiation and defines the enumerator according to parameters
         * 
         * Added in version 1.1
         * 
         * @param string $className The name of the enumerator class
         * @param array $values     The constants values of the enumerator
         * @param array $options    See Above (__construct)
         * 
         */
        public static function createEnumerator(
            $className, 
            array $values,
            array $options = array()
        ) {
            $options['bake'] = true;
            $enum = new Enumeration($className, $values, $options);
        }
    }
}
