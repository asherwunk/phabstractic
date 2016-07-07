<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * A Simple Registry Implementation with Array Access
 * 
 * This file contains a general purpose straight forward registry pattern.
 * In fact, it's really just a paired down \ArrayObject implementation.
 * That's it.  The only advantage of having this code is if there were to be
 * special logic associated with the registry, we could put it here, instead
 * of having to rewrite arrays everywhere else through the project.
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Patterns
 * @subpackage Standard
 * 
 */

/**
 * Falcraft Libraries Pattern Implementations Namespace
 * 
 */
namespace Phabstractic\Patterns
{
    // loading function /falcraftLoad.php depends on registry
    $includes = array(  // returns None on non-existing element
                      '/../Data/Types/None.php',
                        // implements singleton trait
                      '/Resource/SingletonInterface.php',
                      '/Resource/SingletonTrait.php',
                        // throws a range exception on improper access
                      '/Exception/RangeException.php',);

    foreach ( $includes as $include )
    {
        if ( realpath( __DIR__ . str_replace( '/', DIRECTORY_SEPARATOR, $include ) ) === false )
            throw new \RuntimeException( "Patterns\Registry: include $include not found" );
        require_once( realpath( __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $include ) ) );
    }

    use Phabstractic\Data\Types;
    use Phabstractic\Patterns\Resource as PatternsResource;
    use Phabstractic\Patterns\Exception;


    /**
     * The Registry Class
     * 
     * Encapsulates all the necessary functions to implement a registery.
     * Truly, at the moment, implements a paired down \ArrayObject wrapper.
     * 
     * CHANGELOG
     * 
     * 1.0: Documented Registry - May 4th, 2013
     * 2.0: Reproduced Registry for Primus - April 2nd, 2015
     * 3.0: Reformatted for inclusion in phabstractic - July 7th, 2016
     * 
     * @uses \ArrayObject as parent class
     * @uses \Phabstractic\Patterns\Resource\SingletonInterface to identify itself
     * @uses \Phabstractic\Patterns\Resource\SingletonTrait to implement
     * 
     * @version 3.0
     * 
     */
    class Registry extends \ArrayObject implements PatternsResource\SingletonInterface
    {
        use PatternsResource\SingletonTrait;
        
        /**
         * Internal Registry Array
         * 
         * @var array The array under the hood
         * 
         */
        protected $registry = array();
        
        /**
         * \ArrayObject Flags
         * 
         * @var mixed (??)
         * 
         */
        private $flags = null;
        
        /**
         * The Iterator Class
         * 
         * (functionality undocumented here)
         * 
         * @var \ArrayIterator
         * 
         */
        private $iteratorClass = null;
        
        /**
         * For array compatibility
         * 
         */
        public function __construct()
        {
            
        }
        
        /**
         * Registry Class Construction
         * 
         * This constructs the registry array with the given default values
         * 
         * @param array $array The array values to use
         * @param mixed $flags The array 'flags' (such as \ArrayObject::ARRAY_AS_PROPS)
         * @param string $iteratorClass The iterator class for the array, default \ArrayObject's default iterator
         * 
         */
        protected function init(
            array $data = array(),
            $flags = parent::ARRAY_AS_PROPS,
            $iteratorClass = '\\ArrayIterator')
        {
            parent::__construct($data, $flags, $iteratorClass);
            $this->registry = $data;
        }
        
        /**
         * Set A Registry Value
         * 
         * Doesn't necessarily set a reference
         * 
         * @param string|int $index The registry associated key
         * @param mixed $value The registry value
         * 
         */
        public function set($index, $value)
        {
            $this->registry[$index] = $value;
        }
        
        /**
         * Explicitly Set A Registry Reference
         * 
         * For sure sets a reference in the registry
         * 
         * @param string|int $index The registry associated key
         * @param mixed &$value The reference to the registry value
         * 
         */
        public function setReference($index, &$value)
        {
            $this->registry[$index] =& $value;
        }
        
        /**
         * Retrieve Registry Value
         * 
         * May not return reference.  This returns types\Null instead of null,
         * this allows registry values to be the native null value and still
         * make a distinction.
         * 
         * @param string|int $index The index to retrieve
         * @return mixed The registry value, \Phabstractic\Data\Types\None otherwise
         * 
         */
        public function get($index)
        {
            if (isset($this->registry[$index])) {
                return $this->registry[$index];
            } else {
                return new Types\None();
            }
            
        }
        
        /**
         * Explicitly Retrieve Value Refernce
         * 
         * This will for sure return a reference to the registry value at index
         * This returns types\Null instead of null, this allows registry values
         * to be the native null value and make a distinction.
         * 
         * @param string|int $index The index to retrieve
         * @return mixed The registery value reference, \Phabstractic\Data\Types\None otherwise
         * 
         */
        public function &getReference($index)
        {
            if (isset($this->registry[$index])) {
                return $this->registry[$index];
            } else {
                // cannot return a direct instance as above
                $null = new Types\None();
                return $null;
            }
            
        }
        
        /* The rest of the functions here are basic array functions handed down
         * by the \ArrayObject.  Documenting them intensely seems silly.
         * 
         * So here they are.
         */
        
        public function offsetExists($index)
        {
            return array_key_exists($index, $this->registry);
        }
        
        public function offsetGet($index)
        {
            if ($this->offsetExists($index)) {
                return $this->registry[$index];
            } else {
                throw new Exception\RangeException(
                    'Patterns\Registry->offsetGet: Index Undefined');
            }
            
        }
        
        public function offsetSet($index, $value)
        {
            return ($this->registry[$index] = $value);
        }
        
        public function offsetUnset($index)
        {
            if (isset($this->registry[$index])) {
                unset($this->registry[$index]);
            }
            
        }
        
        public function asort()
        {
            return null;
        }
        
        public function append($value)
        {
            return null;
        }
        
        public function count()
        {
            return count($this->registry);
        }
        
        public function exchangeArray($array)
        {
            return null;
        }
        
        public function getArrayCopy()
        {
            return null;
        }
        
        public function getFlags()
        {
            return $this->flags;
        }
        
        public function getIterator()
        {
            return null;
        }
        
        public function getIteratorClass()
        {
            return $this->iteratorClass;
        }
        
        public function ksort()
        {
            return null;
        }
        
        public function natcasesort()
        {
            return null;
        }
        
        public function natsort()
        {
            return null;
        }
        
        public function uasort($cmp_function)
        {
            return null;
        }
        
        public function uksort($cmp_function)
        {
            return null;
        }
    }
}
