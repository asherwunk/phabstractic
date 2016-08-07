<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * A Builder Generator To Inherit From
 * 
 * This file contains the Builder class which when instantiated can define an
 * builder interface and abstract class that can then be 'baked' into the
 * appropriate namespace (or global namespace) and accessed, once baked,
 * as an builder pattern.
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Patterns
 * @subpackage Builder
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
                      // stores builders in static set
                      '/Data/Types/Set.php',
                      // we eval with builderinterface
                      '/Patterns/Resource/BuilderInterface.php',
                      // throws these exceptions
                      '/Patterns/Exception/CodeGenerationException.php',
                      '/Patterns/Exception/RangeException.php',
                      '/Patterns/Exception/RuntimeException.php',);
                      
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types;
    use Phabstractic\Features;
    use Phabstractic\Features\Resource as FeaturesResource;
    use Phabstractic\Patterns\Exception as PatternsException;
    use Phabstractic\Patterns\Resource as PatternsResource;
    
    /**
     * Builder Class - Creates and Defines OO-defined Builder Interfaces/Objects
     * 
     * The Builder class which when instantiated can define a
     * builder interface and builder abstract class that can then be 'baked' into
     * the appropriate namespace (or global namespace) and accessed, once baked,
     * as a builder class to inherit from.
     * 
     * A static 'registry' makes sure that the programmer doesn't try to instantiate
     * a fully qualified BuilderInterface twice, and throws an error.
     * 
     * CHANGELOG
     * 
     * 1.0: created Builder - August 6th, 2016
     * 
     * @version 1.0
     * 
     */
    class Builder implements FeaturesResource\ConfigurationInterface {
        use Features\ConfigurationTrait;
        
        /**
         * Keeps track of already defined Builder interfaces/classes internally
         * 
         * @var Phabstractic\Data\Types\Set
         */
        private static $builders = null;
        
        /**
         * The name of the builder interface/class to be generated
         * 
         * @var string
         */
        private $builderName = '';
        
        /**
         * Whether the class has defined itself
         * 
         * @var bool
         */
        private $baked = false;
        
        /**
         * The abstract methods (turned into-> setMethodName)
         * 
         * @var Phabstractic\Data\Types\Set
         */
        private $methods = null;
        
        /**
         * The namespace under which the builder interface/class is to be defined
         * 
         * @var string
         */
        private $namespace = '';
        
        /**
         * Generates the required class code and evaluates it
         * 
         * The function that pieces the generated code together and evaluates it
         * 
         * Evaluations: $this->builderName
         *                  The name of the generated interface/classes
         *                  (is turned into BuilderNameBuildableInterface,
         *                   and AbstractBuilderNameBuildable,
         *                   and BuilderNameBuilder)
         *              $this->methods
         *                  The methods to put in the interface/classes
         *                  (are turned into setMethod)
         * 
         * @return bool if interfaces and classes are generated without errors
         * 
         * @throws Phabstractic\Patterns\Exception\CodeGenerationException
         *              if class couldn't be generated
         * 
         */
        private function createBuilder() {
            // Start with blank code
            $interfaceCode = '';
            $abstractClassCode = '';
            $builderClassCode = '';
            
            /* Place namespace identifier at top of 'code', as eval statements
               operate outside of the namespace context of the code executing
               the eval statement */
            if (isset($this->conf->namespace) && $this->conf->namespace) {
                $interfaceCode .= 'namespace ' . $this->conf->namespace . " {\n\n";
                $abstractClassCode .= 'namespace ' . $this->conf->namespace . " {\n\n";
                $builderClassCode .= 'namespace ' . $this->conf->namespace . " {\n\n";
            }
            
            $interfaceCode .= 'interface ' . ucfirst($this->builderName) .
                              'BuildableInterface {' . "\n";
            
            $abstractClassCode .= 'abstract class Abstract' .
                                  ucfirst($this->builderName) . 'Buildable ' .
                                  'implements \\';
            
            if (isset($this->conf->namespace) && $this->conf->namespace) {
                $abstractClassCode .= $this->conf->namespace . '\\';
            }
            
            $abstractClassCode .= ucfirst($this->builderName) .
                                  'BuildableInterface {' . "\n";
            
            $builderClassCode .= 'class ' . ucfirst($this->builderName) .
                                 'Builder implements \\Phabstractic\\Patterns' .
                                 '\\Resource\\BuilderInterface {' .
                                 "\nprivate \$" . $this->builderName . ";\n\npublic " .
                                 "function __construct() {\n\t\$this->" .
                                 $this->builderName . " = " .
                                 'new ' . ucfirst($this->builderName) . "();\n}\n\n";
            
            foreach ($this->methods->iterate() as $method) {
                $interfaceCode .= "\tpublic function set" . ucfirst($method) .
                                  "(\$method);\n";
                
                $abstractClassCode .= "\tabstract public function set" .
                                      ucfirst($method) . "(\$method);\n";
                
                $builderClassCode .= 'public function set' . ucfirst($method) .
                                     "(\$method) {\n\t" . '$this->' . $this->builderName .
                                     '->set' . ucfirst($method) . '($method);' .
                                     "\n\treturn \$this;\n}\n\n";
            }
            
            $interfaceCode .= "}\n";
            $abstractClassCode .= "}\n";
            
            $builderClassCode .= "public function getBuiltObject() {\n\t" .
                                 'return $this->' . $this->builderName . ";\n" .
                                 "}\n}\n";
            
            if (isset($this->conf->namespace) && $this->conf->namespace) {
                $interfaceCode .= '}';
                $abstractClassCode .= '}';
                $builderClassCode .= '}';
            }
            
            try {
                eval($interfaceCode);
                eval($abstractClassCode);
                eval($builderClassCode);
            } catch (\Exception $e) {
                throw new PatternsException\CodeGenerationException(
                    'Phabstractic\\Patterns\\Builder->createBuilder: ' .
                    'Unable to generate ' . $this->builderName .
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
        private function getFQN($builderName = '', $namespace = '') {
            return ($namespace ? $namespace : $this->namespace) . '\\Abstract' .
                        ucfirst(
                            ($builderName ? $builderName : $this->builderName)
                        ) . 'Buildable';
        }
        
        /**
         * Make sure self::factories exists
         * 
         */
        private function setUpBuilders() {
            if (self::$builders === null) {
                self::$builders = new Types\Set(
                    array(),
                    array('strict' => $this->conf->strict,
                          'uniqe' => true,));
            }
        }

        /**
         * Builder construction, Set Up builder parameters
         * 
         * This sets up the parameters for the builder to be generated
         * It only generates the class when the option 'bake' is set to true
         * 
         * Options: strict => throw errors
         *          bake => define the classes immediately with the given parameters
         *          namespace => define the namespace
         * 
         * @param string $builderName The name of the factory class, turned into
         *                            {BuilderName}BuildableInterface,
         *                            Abstract{BuilderName}Factory,
         *                            {BuilderName}Builder
         * @param array $methods The methods of the class, turned into makeMethod
         * @param array $options See above options
         * 
         */
        public function __construct(
            $builderName,
            array $methods = array(),
            array $options = array()
        ) {
            $this->configure($options);
            
            $this->setUpBuilders();
            
            // get FQN before to check against self::factories
            if (isset($this->conf->namespace) && $this->conf->namespace) {
                $this->setNamespace($this->conf->namespace);
            }
            
            $this->setBuilderName($builderName);
            
            // method names must be unique
            $this->methods = new Types\Set($methods,
                                           array('strict' => $this->conf->strict,
                                                 'unique' => true));
            
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
                if (!$this->createBuilder()) {
                    return false;
                }
                
                $this->baked = true;
                self::$builders->add($this->getFQN());
                
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
        public function setBuilderName($builderName)
        {
            if (!$this->baked) {
                if (self::$builders->in($this->getFQN($builderName))) {
                    throw new PatternsException\RangeException(
                        'Phabstractic\\Patterns\\Builder->setBuilderName: ' .
                        $this->namespace . '\\Abstract' . $builderName .
                        'Buildable already defined');
                }

                $this->builderName = $builderName;
            } else {
                throw new PatternsException\RuntimeException(
                    'Phabstractic\\Patterns\\Builder->setBuilderName: ' .
                    $this->namespace  . '\\Abstract' . $builderName .
                    'Buildable already generated');
            }
        }
        
        /**
         * Retrieve factory name
         * 
         * @return string The factory name of the typed object
         * 
         */
        public function getBuilderName()
        {
            return $this->builderName;
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
                    array('strict' => true,
                          'unique' => true,)
                );
            } else {
                throw new PatternsException\RuntimeException(
                    'Phabstractic\\Patterns\\Builder->setMethods: ' .
                    $this->namespace  . '\\Abstract' . $this->builderName .
                    'Buildable already baked');
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
                    'Phabstractic\\Patterns\\Builder->addMethod: ' .
                    $this->namespace  . '\\Abstract' . $this->builderName .
                    'Buildable already baked');
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
                    'Phabstractic\\Patterns\\Builder->addMethods: ' .
                    $this->namespace  . '\\Abstract' . $this->builderName .
                    'Buildable already baked');
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
                     'Phabstractic\\Patterns\\Builder->removeMethod: ' .
                     $this->namespace  . '\\Abstract' . $this->builderName .
                     'Buildable already baked');
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
                if (self::$builders->in($this->getFQN('', $namespace))) {
                    throw new PatternsException\RangeException(
                        'Phabstractic\\Patterns\\Builder->setNamespace: ' .
                        $namespace . '\\' . $this->builderName .
                        ' Buildable already defined');
                }
                
                $this->namespace = $namespace;
            } else {
                throw new PatternsException\RuntimeException(
                    'Phabstractic\\Patterns\\Builder->setNamespace: ' .
                    $this->namespace . '\\' . $this->builderName .
                    ' Buildable already baked');
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
                'name' => $this->builderName,
                'namespace' => $this->namespace,
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
        public static function buildBuilder(
            $builderName,
            array $methods,
            array $options = array()
        ) {
            // preserve other options
            $options['bake'] = true;
            
            $builder = new Builder(
                $builderName,
                $methods,
                $options
            );
            
            return $builder;
        }
        
    }
    
}
