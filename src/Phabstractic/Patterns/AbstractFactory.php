<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * A factory to inherit from
 * 
 * This file contains the AbstractFactory class which when instantiated can define an
 * abstract factory that can then be 'baked' into the appropriate namespace (or global
 * namespace) and accessed, once baked, as an abstract factory pattern.
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Patterns
 * @subpackage Factory
 * @category DesignPatterns
 * 
 */

/**
 * Falcraft Libraries Pattern Implementations Namespace
 * 
 */
namespace Phabstractic\Patterns
{
    require_once(realpath(__DIR__ . '/../') . '/falcraftLoad.php');
    
    /*
     * Configuration.php - Inherits options ability
     * 
     * Set.php - This uses a set to store stuff
     */
    
    $includes = array(// is configurable
                      '/Features/ConfigurationTrait.php',
                      '/Features/Resource/ConfigurationInterface.php',
                      // stores factorires in static set
                      '/Data/Types/Set.php',
                      // throws these exceptions
                      '/Patterns/Exception/CodeGenerationException.php',
                      '/Patterns/Exception/RangeException.php',
                      '/Patterns/Exception/RuntimeException.php',);
                      
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types;
    use Phabstractic\Features;
    use Phabstractic\Features\Resource as FeaturesResource;
    use Phabstractic\Patterns\Exception as PatternsException;
    
    /**
     * AbstractFactory Class - Creates and Defines OO-defined AbstractFactory Classes/Objects
     * 
     * The AbstractFactory class which when instantiated can define an abstract factory
     * that can then be 'baked' into the appropriate namespace (or global namespace) and
     * accessed, once baked, as an AbstractFactory class of itself.
     * 
     * A static 'registry' makes sure that the programmer doesn't try to instantiate
     * a fully qualified AbstractFactory twice, and throws an error.
     * 
     * CHANGELOG
     * 
     * 1.0  created AbstractFactory - May 27th, 2013
     * 2.0: Integrated into Primus2 - August 30th, 2015
     * 3.0: configuration information is now in one place, the constructor
     *      refactored createAbstractFactory to access object properties
     *      clarified all exceptions as coming from patterns/exception
     *      reformatted for inclusion into phabstractic - July 20th, 2016
     * 
     * @version 3.0
     * 
     */
    class AbstractFactory implements FeaturesResource\ConfigurationInterface {
        use Features\ConfigurationTrait;
        
        /**
         * Keeps track of already defined AbstractFactory classes internally
         * 
         * @var Phabstractic\Data\Types\Set
         */
        private static $factories = null;
        
        /**
         * The name of the factory to be generated
         * 
         * @var string
         */
        private $factoryName = '';
        
        /**
         * Whether the class has defined itself
         * 
         * @var bool
         */
        private $baked = false;
        
        /**
         * The constants, name => value, of the enumeration
         * 
         * @var array
         */
        private $constants = null;
        
        /**
         * The abstract methods (turned into-> makeMethodName)
         * 
         * @var Phabstractic\Data\Types\Set
         */
        private $methods = null;
        
        /**
         * The namespace under which the abstract factory class is to be defined
         * 
         * @var string
         */
        private $namespace = '';
        
        /**
         * Generates the required class code and evaluates it
         * 
         * The function that pieces the generated code together and evaluates it
         * 
         * Evaluations: $this->factoryName
         *                  The name of the generate class
         *                  (is turned into AbstractFactoryNameFactory)
         *              $this->methods
         *                  The methods to put in the factory
         *                  (are turned into makeMethodName)
         *              $this->constants
         *                  The constants to put in the factory
         *                  (are turned uppercase)
         * 
         * @return bool if abstract factory class is generated without errors
         * 
         * @throws Phabstractic\Patterns\Exception\CodeGenerationException
         *              if class couldn't be generated
         * 
         */
        private function createAbstractFactory() {
            // Start with blank code
            $classCode = '';
            
            /* Place namespace identifier at top of 'code', as eval statements
               operate outside of the namespace context of the code executing
               the eval statement */
            if (isset($this->conf->namespace) && $this->conf->namespace) {
                $classCode .= 'namespace ' . $this->conf->namespace . "{\n\n";
            }
            
            /* turns factoryName into Abstract{FactoryName}Factory so,
               Book becomes AbstractBookFactory */
            $classCode .= 'abstract class Abstract' . ucfirst($this->factoryName) .
                          'Factory {' . "\n";
            
            foreach ($this->constants as $identifier => $val) {
                // Any class constants (transformed into uppercase)
                $classCode .= 'const ' . strtoupper($identifier) . " = $val;\n\t";
            }
            
            foreach ($this->methods->iterate() as $method) {
                // make the abstract methods, method is turned into makeMethod
                $classCode .= 'abstract public function make' .
                              ucfirst($method)  . "();\n\t";
            }
            
            if (isset($this->conf->namespace) && $this->conf->namespace) {
                $classCode .= '}';
            }
            
            $classCode .= '}';
            
            try {
                eval($classCode);
            } catch (\Exception $e) {
                throw new PatternsException\CodeGenerationException(
                    'Phabstractic\\Patterns\\AbstractFactory->createAbstractFactory: ' .
                    'Unable to generate ' . $this->factoryName .
                    ' due to an internal error.');
            }
            
            return true;  // The abstract factory was generated
        }
        
        /**
         * Construct FQN for Factory based on properties
         * 
         * @param string optional factory name
         * 
         * @return string
         * 
         */
        private function getFQN($factoryName = '', $namespace = '') {
            return ($namespace ? $namespace : $this->namespace) . '\\Abstract' .
                        ucfirst(
                            ($factoryName ? $factoryName : $this->factoryName)
                        ) . 'Factory';
        }
        
        /**
         * Make sure self::factories exists
         * 
         */
        private function setUpFactories() {
            if (self::$factories === null) {
                self::$factories = new Types\Set(
                    array(),
                    array('strict' => $this->conf->strict,
                          'uniqe' => true,));
            }
        }

        /**
         * AbstractFactory construction, Set Up abstract factory parameters
         * 
         * This sets up the parameters for the abstractfactory to be generated
         * It only generas the class when the option 'bake' is set to true
         * 
         * Options: strict => throw errors
         *          namespace => namespace to define this enumerator in
         *          bake => define the class immediately with the given parameters
         * 
         * @param string $factorName The name of the factory class, turned into
         *                           Abstract{FactoryName}Factory
         * @param array $methods The methods of the class, turned into makeMethod
         * @param array $constants The constants if any defined for the class ($key = $value)
         * @param array $options See above options
         * 
         */
        public function __construct(
            $factoryName,
            array $methods = array(),
            array $constants = array(),
            array $options = array()
        ) {
            $this->configure($options);
            
            $this->setUpFactories();
            
            // get FQN before to check against self::factories
            if (isset($this->conf->namespace) && $this->conf->namespace) {
                $this->setNamespace($this->conf->namespace);
            }
            
            $this->setFactoryName($factoryName);
            
            // method names must be unique
            $this->methods = new Types\Set($methods,
                                           array('strict' => $this->conf->strict,
                                                 'unique' => true));
            
            // constants must be unique
            $this->constants = array_unique($constants);
            
            if (isset($this->conf->bake) && $this->conf->bake) {
                $this->bake();
            }
        }
        
        /**
         * Generates the abstract factory class in the desired namespace
         * 
         * Generates the abstract factory class in the desired namespace
         * using the given parameters, in essence solidifying them in place.
         * Once an abstract factory class is 'baked' it cannot be changed.
         * 
         * This function also pushes the qualified name onto a static set
         * so that future abstract factories defined through this method
         * don't clash in name, allowing the class ro raise a RangeException
         * 
         * @return bool Success or failure?
         * 
         */
        public function bake()
        {
            if (!$this->baked) {
                if (!$this->createAbstractFactory()) {
                    return false;
                }
                
                $this->baked = true;
                self::$factories->add($this->getFQN());
                
                return true;
            }
        }
        
        /**
         * Sets the name of the class to be generated
         * 
         * Checks to make sure the proposed fully qualified name doesn't clash
         * with an already created enumerator object, raising a RangeException error
         * 
         * Remember, factoryName becomes Abstract{FactoryName}Factory
         * 
         * @param string $factoryName The name of the class to be generated
         * 
         * @throws Phabstractic\Patterns\Exception\RangeException
         *              when the qualified name clashes with an already
         *              created enumerator class
         * @throws Phabstractic\Patterns\Exception\RuntimeException
         *              when the class has already been baked
         * 
         */
        public function setFactoryName($factoryName)
        {
            if (!$this->baked) {
                if (self::$factories->in($this->getFQN($factoryName))) {
                    throw new PatternsException\RangeException(
                        'Phabstractic\\Patterns\\AbstractFactory->setFactoryName: ' .
                        $this->namespace . '\\Abstract' . $factoryName .
                        'Factory already defined');
                }

                $this->factoryName = $factoryName;
            } else {
                throw new PatternsException\RuntimeException(
                    'Phabstractic\\Patterns\\AbstractFactory->setFactoryName: ' .
                    $this->namespace  . '\\Abstract' . $factoryName .
                    'Factory already generated');
            }
        }
        
        /**
         * Retrieve factory name
         * 
         * @return string The factory name of the typed object
         * 
         */
        public function getFactoryName()
        {
            return $this->factoryName;
        }
        
        /**
         * Check to see if class has been defined or 'baked'
         * 
         * @return bool Whether the abstract factory has been baked.
         * 
         */
        public function isBaked()
        {
            return $this->baked;
        }
        
        /**
         * Sets any desired constants to be defined in the abstract factory
         * 
         * @param array $constants An array containing the constant names => values
         * 
         * @throws Phabstractic\Patterns\Exception\RuntimeException
         *              when the class has already been generated (baked)
         * 
         */
        public function setConstants(array $constants)
        {
            if (!$this->baked) {
                $this->constants = $constants;
            } else {
                throw new PatternsException\RuntimeException(
                    'Phabstractic\\Patterns\\AbstractFactory->setConstants: ' .
                    $this->namespace  . '\\Abstract' . $factoryName .
                    'Factory already baked');
            }
        }
        
        /**
         * Gets the abstract factory elements (constants)
         * 
         * @return array Array of abstract factory constants
         * 
         */
        public function getConstants()
        {
            return $this->constants;
        }
        
        /**
         * Add a single constant to the constant list
         * 
         * This method overrides any constant names already defined with the new proposed values
         * 
         * @param string $name The name of the constant
         * @param mixed $value May be anything a constant can be defined as
         * 
         * @throws Phabstractic\Patterns\Exception\RuntimeException
         *              if the class has already been baked
         * 
         */
        public function addConstant($name, $value)
        {
            if (!$this->baked) {
                $this->constants[$name] = $value;
            } else {
                throw new PatternsException\RuntimeException(
                    'Phabstractic\\Patterns\\AbstractFactory->addConstant: ' .
                    $this->namespace  . '\\Abstract' . $factoryName .
                    'Factory already baked');
            }
        }
        
        /**
         * Add multiple constants to the constant list
         * 
         * This method employs the array structure identifier => value.
         * Overrides any constant names already defined with the new proposed values
         * 
         * @param array The array of constants to add to the list
         * 
         * @throws Phabstractic\Patterns\Exception\RuntimeException
         *              if the class has already been baked
         * 
         */
        public function addConstants(array $constants) {
            if (!$this->baked) {
                $this->constants = array_merge($this->constants, $constants);
            } else {
                throw new PhabstracticException\RuntimeException(
                    'Phabstractic\\Patterns\\AbstractFactory->addConstants: ' .
                    $this->namespace  . '\\Abstract' . $factoryName .
                    'Factory already baked');
            }
        }
        
        /**
         * Remove a constant from the constant list
         * 
         * This method removes a constant from the constant list only if the class
         * has not been generated, otherwise it throws an error
         * 
         * @param string Name of the constant
         * 
         * @throws Phabstractic\Patterns\Exception\RuntimeException
         *              if the class has already been baked
         * 
         */
        public function removeConstant($name)
        {
            if (!$this->baked) {
                unset($this->constants[$name]);
            } else {
                 throw new PatternsException\RuntimeException(
                     'Phabstractic\\Patterns\\AbstractFactory->removeConstant: ' .
                     $this->namespace  . '\\Abstract' . $factoryName .
                     'Factory already baked');
            }
        }
        
        /**
         * Sets the abstract factory methods
         * 
         * Remember, method names are transformed into makeMethod
         * 
         * @param array $methods An array containing the method names
         * 
         * @throws Phabstractic\Patterns\Exception\RuntimeException
         *              when the class has already been generated (baked)
         * 
         */
        public function setMethods(array $methods)
        {
            if (!$this->baked) {
                $this->methods = new Types\Set(
                    $methods,
                    array('strict' => $this->conf->strict,
                          'unique' => true,)
                );
            } else {
                throw new PatternsException\RuntimeException(
                    'Phabstractic\\Patterns\\AbstractFactory->setMethods: ' .
                    $this->namespace  . '\\Abstract' . $this->factoryName .
                    'Factory already baked');
            }
        }
        
        /**
         * Gets the abstract factory methods
         * 
         * @return array Array of abstract factory methods
         * 
         */
        public function getMethods()
        {
            return $this->methods->getPlainArray();
        }
        
        /**
         * Add a single method to the method list
         * 
         * This method overrides any method names already defined with the new proposed values
         * 
         * Remember: method becomes makeMethod
         * 
         * @param string $method The name of the method
         * 
         * @throws Phabstractic\Patterns\Exception\RuntimeException
         *              if the class has already been baked
         * 
         */
        public function addMethod($method)
        {
            if (!$this->baked) {
                $this->methods->add($method);
            } else {
                throw new PatternsException\RuntimeException(
                    'Phabstractic\\Patterns\\AbstractFactory->addMethod: ' .
                    $this->namespace  . '\\Abstract' . $factoryName .
                    'Factory already baked');
            }
        }
        
        /**
         * Add multiple methods to the method list
         * 
         * This method employs the array structure identifier => value.
         * Overrides any method names already defined with the new proposed values
         * 
         * @param array $methods The array of methods to add to the list
         * 
         * @throws Phabstractic\Patterns\Exception\RuntimeException
         *              if the class has already been baked
         * 
         */
        public function addMethods(array $methods)
        {
            if ( !$this->baked ) {
                foreach ($methods as $method) {
                    $this->methods->add($method);
                }
            } else {
                throw new PatternsException\RuntimeException(
                    'Phabstractic\\Patterns\\AbstractFactory->addMethods: ' .
                    $this->namespace  . '\\Abstract' . $factoryName .
                    'Factory already baked');
            }
        }
        
        /**
         * Remove a method from the method list
         * 
         * This method removes a method from the method list only if the class
         * has not been generated, otherwise it throws an error
         * 
         * Remember: NOT makeMethod, just method
         * 
         * @param string $method Name of the method
         * 
         * @throws Phabstractic\Patterns\Exception\RuntimeException
         *              if the class has already been baked
         * 
         */
        public function removeMethod($method)
        {
            if ( !$this->baked ) {
                $this->methods->remove($method);
            } else {
                 throw new PatternsException\RuntimeException(
                     'Phabstractic\\Patterns\\AbstractFactory->removeMethod: ' .
                     $this->namespace  . '\\Abstract' . $factoryName .
                     'Factory already baked');
            }
        }
        
        /**
         * Sets up the namespace to be used when generating the abstract factory class
         * 
         * This sets the namespace that will be used when the enumerator class is generated
         * How?  Eval statements operate in the global namespace, or basically a clean slate
         * enabling us to put a namespace statement at the beginning of our generated code
         * Any namespace will do, if there is no namespace, you must access your abstract factory
         * using the global namespace.  ex: \Months::January, \Colors::Red, ...
         * 
         * @param string $namespace The namespace specified
         * 
         * @throws Phabstractic\Patterns\Exception\RangeException
         *              when the class/namespace has already been generated via Enum
         * @throws Phabstractic\Patterns\Exception\RuntimeException
         *              if the class has already been generated/baked.
         * 
         */
        public function setNamespace($namespace)
        {
            if (!$this->baked) {
                if (self::$factories->in($this->getFQN('', $namespace))) {
                    throw new PatternsException\RangeException(
                        'Phabstractic\\Patterns\\AbstractFactory->setNamespace: ' .
                        $namespace . '\\' . $this->factoryName .
                        ' Factory already defined');
                }
                
                $this->namespace = $namespace;
            } else {
                throw new PatternsException\RuntimeException(
                    'Phabstractic\\Patterns\\AbstractFactory->setNamespace: ' .
                    $this->namespace . '\\' . $this->className .
                    ' Factory already baked');
            }
        }

        /**
         * Retrieves the namespace for the generated abstract factory
         * 
         * @return string The namespace for the generated abstract factory
         */
        public function getNamespace()
        {
            return $this->namespace;
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
                'baked' => $this->baked,
                'name' => $this->factoryName,
                'namespace' => $this->namespace,
                'constants' => $this->constants,
                'methods' => $this->methods->getPlainArray()
            ];
        }
        
        /**
         * Straight out define the abstract factory class without instantiating
         * 
         * Takes care of the instantiation and defines the abstract factory according to parameters
         * 
         * @param string $factoryName The name of the abstract factory class (remember Abstract{FactoryName}Factory)
         * @param array $methods The methods to be defined in the factory
         * @param array $constants The constants to be defined in the factory
         * @param array $options See (__construct)
         * 
         */
        public static function buildAbstractFactory(
            $factoryName,
            array $methods,
            array $constants = array(),
            array $options = array()
        ) {
            // preserve other options
            $options['bake'] = true;
            
            $factory = new AbstractFactory(
                $factoryName,
                $methods,
                $constants,
                $options
            );
            
            return $factory;
        }
        
    }
    
}
