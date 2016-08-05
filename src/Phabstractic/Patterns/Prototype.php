<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * A Prototype Class
 * 
 * JavaScript has an elaborate prototyping system for its objects and functions.
 * This is an effort to partially duplicate the system seen in JavaScript. The
 * idea is that you instantiate a Prototype, add your own properties and
 * functions, register that prototype into an array using a string, and create 
 * a chain of prototypes using the __parent property. If a method or property 
 * doesn't exist in a Prototype, it checks the __parent property to see if it 
 * exists, recursively. If you change the property value of a parent prototype 
 * it's reflected in it's "children", likewise, if you set a similarly named 
 * property value on a "child" prototype, it overrides the __parent property 
 * value.
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Patterns
 * @subpackage Prototypes
 * 
 */
 
/**
 * Falcraft Libraries Patterns Namespace
 * 
 */
namespace Phabstractic\Patterns
{
    
    require_once(realpath(__DIR__ . '/../') . '/falcraftLoad.php');
    
    $includes = array(// we use two options, strict and prefix
                      '/Features/ConfigurationTrait.php',
                      '/Features/Resource/ConfigurationInterface.php',
                      // Keeps track of prototypes in registry automatically
                      '/Features/IdentityTrait.php',
                      // we throw a runtime error if property doesn't exist
                      '/Patterns/Exception/RuntimeException.php',);
    
    falcraftLoad($includes, __NAMESPACE__ . __FILE__);
    
    use Phabstractic\Features\Resource as FeaturesResource;
    use Phabstractic\Features;
    use Phabstractic\Patterns\Exception as PatternsException;

    /**
     * Prototype Class - Defines a JavaScript Like Prototype
     * 
     * In order for a prototype to work in the hierarchy correctly, we must
     * chain prototype objects together using __parent. If you don't use
     * a base prototype object, you must use something with an interface that
     * will be compatible. It is thus necessary to always use new Prototype(),
     * ::fromRegistry($key), ::fromPrototype($prototype).
     * 
     * You can register prototypes into a 'global' registry using a strings. 
     * If you 'add' a registry entry with the same key, the old entry
     * is overwritten.
     * 
     * CHANGELOG
     * 
     * 1.0: Created Prototype - November, 23th, 2015
     * 2.0: eliminated map for registry, made it swappable
     *      eliminated local static methods
     *      reformatted for inclusion in phabstractic - August 4th, 2016
     * 
     * @version 2.0
     * 
     */
    class Prototype extends \StdClass implements
        FeaturesResource\ConfigurationInterface
    {
        use Features\ConfigurationTrait;
        use Features\IdentityTrait;
        
        /**
         * The 'global' prototype registry
         * 
         * Defined as an array of (key, indices)
         * 
         * @var array
         * 
         */
        protected static $registry = array();
        
        /**
         * The unique identifier for this prototype
         * 
         * See Features\Identity
         * 
         * @var string
         * 
         */
        protected $identity = '';
        
        /**
         * Set the registry object
         * 
         * This enables us to swap out a map if we want it
         * 
         * @param \ArrayAccess $newRegistry The new registry
         * 
         */
        public static function setRegistry(\ArrayAccess &$newRegistry)
        {
            self::$registry = &$newRegistry;
        }
        
        /**
         * Retrieve a Prototype from the 'global' registry
         * 
         * @param mixed $key
         * 
         * @return Falcraft\Patterns\Prototype
         * 
         */
        public static function getFromRegistry($key)
        {
            if (array_key_exists($key, self::$registry)) {
                return self::$registry[$key];
            }
            
            return null;
        }
        
        /**
         * Add a value to the map with a key that previously did not exist
         * 
         * @param Falcraft\Patterns\Prototype $prototype
         * @param mixed $key
         * 
         * @return result of setting operation
         * 
         */
        public static function addToRegistry($prototype, $key = '')
        {
            if (!$key) {
                $key = $prototype->getIdentifier();
            }
            
            return self::$registry[$key] = $prototype;
        }
        
        /**
         * Remove prototype from 'global' registry using key
         * 
         * @param mixed $key
         * 
         * @return bool
         * 
         */
        public static function removeFromRegistry($key)
        {
            unset(self::$registry[$key]);
            
            return true;
        }
        
        /**
         * Create a Prototype whose __parent is a prototype from the registry
         * 
         * @param mixed $key
         * 
         * @return Falcraft\Patterns\Prototype the new prototype
         * 
         */
        public static function fromRegistry($key, $options = array())
        {
            $ret = new Prototype($options);
            $ret->__parent = self::getFromRegistry($key);
            return $ret;
        }
        
        /**
         * Create a Prototype whose __parent is given prototype
         * 
         * @param Falcraft\Patterns\Prototype $prototype
         * 
         * @return Falcraft\Patterns\Prototype the new prototype
         * 
         */
        public static function fromPrototype(
            $parentPrototype,
            $options = array()
        ) {
            $ret = new Prototype($options);
            $ret->__parent = $parentPrototype;
            return $ret;
        }
        
        /**
         * The Prototype Constructor
         * 
         * The constructor itself establishes the options that a particular
         * prototype might have. If you wish to change a prototype's
         * configuration call $prototype->configure($options) You can pass an
         * array, or a a Zend Conf object.
         * 
         * The key/value pair is constructed according to the array elements
         * data type.
         * 
         * Currently available options:
         * strict => whether to output errors or remain silent
         * 
         * @param array $options The options for the Map as outlined above
         * 
         */
        public function __construct($options = array())
        {
            $options = array_change_key_case($options);
            
            if (!isset($options['prefix'])) {
                $options['prefix'] = 'Prototype';
            }
            
            // Configure the object, and identify it
            $this->configure($options);
            
            $this->identityPrefix = $this->conf->prefix;
            
            $this->identity = $this->getNewIdentity();
        }
        
        /**
         * Retrieve the identifier of this prototype
         * 
         * NOTE: There is no setIdentity, as the identity should be unique
         * 
         * @return string
         * 
         */
        public function getIdentifier()
        {
            return $this->identity;
        }
        
        /**
         * Magical Call Function
         * 
         * This uses call_user_func_array to call the closures set in the
         * object. It overrides the error handling temporarily so that it can
         * catch warnings about non-existing properties. If it can't find a
         * property, it looks up the __parent chain to see if it can resolve.
         * 
         * If it can't find anything, it reports an error.
         * 
         * It's important that at the end that it deregisters its handler for
         * normal function
         * 
         * NOTE: This catches a specific RunTime Exception instance, and should
         * pass all others... hopefully.
         * 
         * @param string $name of desired function
         * @param array $arguments of desired function
         * 
         * @return mixed Result of user function
         * 
         */
        public function __call($name, $arguments)
        {
            set_error_handler(
                function () use ($name, $arguments) {
                    if (isset($this->__parent)) {
                        try {
                            $ret = call_user_func_array(
                                array($this->__parent, $name),
                                $arguments
                            );
                        } catch (PatternsException\RuntimeException $e) {
                            restore_error_handler();
                            throw $e;
                        }
                        restore_error_handler();
                        return $ret;
                    } else if ($this->conf->strict) {
                        restore_error_handler();
                        throw new PatternsException\RuntimeException(
                            'Phabstractic\\Patterns\\Prototype->__call(): ' .
                            'Non-Existent Property/Function'
                        );
                    }
                }, 
                E_WARNING
            );
            
            $ret = call_user_func_array($this->$name, $arguments);
            restore_error_handler();
            return $ret;
        }
        
        /**
         * Magical Get Function
         * 
         * This one is simpler than the magical call function. This simply 
         * searches up the __parent chain looking for a property until it runs
         * out of parents.
         * 
         * NOTE: This is useful because it will also return the function
         * objects associated with a key (closures) if you want access
         * to the closure itself.
         * 
         * @param $var name of variable
         * 
         * @return mixed
         * 
         */
        public function __get($var)
        {
            if (isset($this->$var)) {
                return $this->$var;
            } else if (isset($this->__parent)) {
                return $this->__parent->$var;
            } else {
                if ($this->conf->strict) {
                    throw new PatternsException\RuntimeException(
                        'Phabstractic\\Patterns\\Prototype->__get(): ' .
                        'Non-Existent Property'
                    );
                }
            }
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
            $properties = get_object_vars($this);
            
            unset($properties['identityPrefix']);
            unset($properties['conf']);
            
            return [
                'options' => array('strict' => $this->conf->strict,
                                   'prefix' => $this->conf->prefix,),
                'properties' => $properties,
            ];
        }
    }
    
}
