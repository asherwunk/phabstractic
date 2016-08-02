<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * The Restricted Set
 * 
 * This file contains the RestrictedSet class, pretty much the same as a set
 * except it can only contain certain data types.  Usually one data type, but
 * if constructed without any restrictions, acts as a normal Set without ability
 * to hold typed objects.
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
    $includes = array(// we are a configurable objects
                      '/Features/ConfigurationTrait.php',
                      '/Features/Resource/ConfigurationInterface.php',
                      // we inherit from Set
                      '/Data/Types/Set.php',
                      // we use resource static method
                      '/Data/Types/Restrictions.php',
                      // our 'restrictions' is really just a filtr
                      '/Data/Types/Resource/AbstractFilter.php',
                      // throws these exceptions
                      '/Data/Types/Exception/InvalidArgumentException.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types;
    use Phabstractic\Data\Types\Resource as TypesResource;
    use Phabstractic\Data\Types\Exception as TypesException;
    use Phabstractic\Features;
    use Phabstractic\Features\Resource as FeaturesResource;
    
    /**
     * Restricted Set Class - Defines A Restricted Set Data Structure
     * 
     * An instantiated Set is like an array, but only holds unique values without
     * really any keys to the set itself.  It's just a collection of unique
     * values, like a pool of variables.  This makes the Set only hold certain
     * values, usually one type.
     * 
     * NOTE:  You can only set the restrictions of this object in the constructor
     *        Which makes sense, what would you do half-way through if all of a
     *        sudden half your set doesn't qualify anymore?  Seems like a mess.
     * 
     * CHANGELOG
     * 
     * 1.0: Documented RestrictedSet - March 5th, 2013
     * 1.1: Fixed Options initialization - May  16th, 2013
     * 2.0: Refactored and re-formatted for inclusion in Primus - April 11th, 2015
     * 3.0: added option filter_class to specify alternate static function call
     *      reformatted for inclusion in phabstractic - July 201th, 2016
     * 3.0.1: implements configurationinterface - July 31st, 2016
     * 
     * @uses Phabstractic\Data\Types\Set inherits from
     * @uses Phabstractic\Data\Types\Restrictions default static class
     * 
     * @link http://en.wikipedia.org/wiki/Set_(abstract_data_type) [English]
     * 
     * @version 3.0
     * 
     */
    class RestrictedSet extends Set implements
        FeaturesResource\ConfigurationInterface
    {
        use Features\ConfigurationTrait;
        
        /**
         * The restrictions predicate structure for the allowed set variables
         * 
         * @var Phabstractic\Data\Types\Resources\AbstractFilter
         */
        private $restrictions;
        
        /**
         * Retrieve the Restrictions object/structure from the set
         * 
         * Useful for Reflection purposes
         * 
         * @return Phabstractic\Data\Types\Restrictions
         * 
         */
        public function getRestrictions()
        {
            return $this->restrictions;
        }
        
        /**
         * RestrictedSet Class Constructor
         * 
         * This sets up the restrictions for the values, then provides the
         * options to the parent set.  This is the only place to set the
         * restrictions for the set.
         * 
         * options - 'filter_class' =>
         *                      the class that the checkElements static function
         *                      is called on
         * 
         * @param array $values The values for the RestrictedSet
         * @param Phabstractic\Data\Types\Resource\AbstractFilter $restrictions
         *            The restrictions for the data type
         * @param array $options The options for the parent set.
         * 
         * @throws Phabstractic\Data\Types\Exception\InvalidArgumentException
         * 
         */
        public function __construct(
            array $values = array(),
            TypesResource\AbstractFilter $restrictions = null,
            $options = array()
        ) {
            $options = array_change_key_case($options);
            
            if (!isset($options['filter_class'])) {
                $options['filter_class'] = 'Phabstractic\\Data\\Types\\Restrictions';
            }
            
            $this->configure($options);
            
            $filterClass = $this->conf->filter_class;
            
            // If there are no restrictions given, build basic free form restrictions
            // Default doesn't allow Type::TYPED_OBJECT    
            if (!$restrictions) {
                $restrictions = $filterClass::getDefaultRestrictions();
            }
            
            $this->restrictions = $restrictions;
            
            /* Check input values for any illegal types
               If false, throw error because this is a constructor and can't
               really return nothing */
            if (!$filterClass::checkElements($values, $this->restrictions))
                throw new TypesException\InvalidArgumentException(
                    'Phabstractic\\Data\\Types\\RestrictedSet->__construct: ' .
                    'Illegal Value');
            
            // Construct set
            parent::__construct($values, $options);
        }
        
        /**
         * Add a value to the set
         * 
         * Runs the value by the restrictions, only triggering if set is strict
         * otherwise doing nothing and exiting the function
         * 
         * @param mixed $value Value to add to the set, must be of allowed type
         * 
         * @return string|false The identifier for the entry, false otherwise
         * 
         * @throws Phabstractic\Data\Types\Exception\InvalidArgumentException
         *              if 'strict' option is set and type is not in Restrictions
         * 
         */
        public function add($value)
        {
            $filterClass = $this->conf->filter_class;
            
            if (($valid = $filterClass::checkElements(
                    array($value),
                    $this->restrictions,
                    true)) == false) {
                if ($this->conf->strict) {
                    throw new TypesException\InvalidArgumentException(
                        'Phabstractic\\Data\\Types\\RestrictedSet->add: ' .
                        'Restricted Value');
                } else {
                    return false;
                }
            }
            
            // Passes restrictions, but safety check
            if ($valid) {
                return parent::add($value);
            }
            
        }
        
        /**
         * Add a value to the set as a reference
         * 
         * Runs the value by the restrictions, only triggering if set is strict
         * otherwise doing nothing and exiting the function
         * 
         * @param mixed $value Value to add to the set, must be of allowed type
         * 
         * @return string|false The datum identifier, false otherwise
         * 
         * @throws Exception\InvalidArgumentException if 'strict' option is set
         *             and type is not in Restrictions
         * 
         */
        public function addReference(&$value)
        {
            $filterClass = $this->conf->filter_class;
            
            if (($valid = $filterClass::checkElements(
                    array($value),
                    $this->restrictions,
                    true)) == false) {
                if ($this->conf->strict) {
                    throw new TypesException\InvalidArgumentException(
                        'Phabstractic\\Data\\Types\\RestrictedSet->add: ' .
                        'Restricted Value');
                } else {
                    return false;
                }
            }
            
            // Passes restrictions, but safety check
            if ($valid) {
                return parent::addReference($value);
            }
            
        }
        
        /**
         * Generates a RestrictedSet from an array
         * 
         * Default restrictions are all but TYPED_OBJECT
         * 
         * NOTE:  This method signature expands on the Set::build static
         *        function PHP allows you to do this with default arguments
         * 
         * @link http://en.wikipedia.org/wiki/Liskov_substitution_principle [English]
         * 
         * @see Phabstractic\Data\Types\Set::__construct()
         * 
         * @static
         * 
         * @param array $values The values to put in the Restricted Set
         * @param Phabstractic\Data\Types\Resource\AbstractFilter $restrictions
         *            The Restrictions to place on the generated set
         * @param array $options The restricted set options (See constructor)
         * 
         * @return Phabstractic\Data\Types\RestrictedSet Generated RestrictedSet
         * 
         */
        static public function build(
            array $values = array(),
            TypesResource\AbstractFilter $restrictions = null,
            array $options = array()
        ) {
            return new RestrictedSet($values, $restrictions, $options);
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
                'options' => array('unique' => $this->conf->unique,
                                   'strict' => $this->conf->strict,
                                   'reference' => $this->conf->reference,),
                'identityPrefix' => $this->identityPrefix,
                'restrictions' => $this->restrictions,
                'data' => $this->data,
            ];
        }
        
        /**
         * Calls an internal member function of a set member with arguments
         * 
         * Like map, only it calls a particular method on the given object,
         * this is possible because the set can be guaranteed to hold only
         * certain objects.  !! Do not attempt on a mixed set. !!
         * 
         * @param string $method The method name to call
         * @param array $args The arguments to pass to the method
         * @param Phabstractic\Data\Types\Set $S The set to act on
         * 
         */
        static public function mapInternal($method, $args, RestrictedSet $S)
        {
            foreach ($S->iterate() as $object) {
                if (method_exists($object, $method)) {
                    call_user_func_array(array($object, $method), $args);
                }
                
            }
            
        }
        
    }
    
}
