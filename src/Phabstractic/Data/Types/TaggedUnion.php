<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * The Tagged Union
 * 
 * This file contains the Tagged Union Class.  Here is a value that can only
 * hold specific types of values, but may be any one of these types of values
 * can be stored at any one time.  This creates a form of "data structure
 * polymorphism" where a value is of a specific type, rather than a is_a
 * relationship.
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
    
    $includes = array(// Uses restrictions predicate to limit data types
                      '/Data/Types/Type.php',
                      '/Data/Types/Resource/FilterInterface.php',
                      // Throws the following exceptions
                      '/Data/Types/Exception/InvalidArgumentException.php',
                      '/Data/Types/Exception/RuntimeException.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types\Type;
    use Phabstractic\Data\Types\Resource as TypesResource;
    use Phabstractic\Data\Types\Exception as TypesException;
    
    /**
     * Tagged Union Class - Defines A Dynamic Tagged Union
     * 
     * An instantiated TaggedUnion is a 'variable' that can hold only specifically
     * defined data types, such as string, integer, resource, or object.  One
     * TaggedUnion for instance may only hold strings, and objects of a
     * particular class.  Code can detect what type a given TaggedUnion's value
     * is at run time, thus enabling a form of 'data structure polymorphism'.
     * 
     * NOTE: since v3.0 restrictions aren't only configurable at construction
     * 
     * CHANGELOG
     * 
     * 1.0: Created TaggedUnion - April 21st, 2013
     *      Documented TaggedUnion - May 5th, 2013
     * 1.1: public function getValue( $value ) -> getValue() - May 5th, 2013
     * 2.0: Refactored and reformatted for Primus - April 11th, 2015
     * 3.0: eliminated need for restrictions to only be defined at
     *      construction, reformatted for inclusion in phabstractic - July 18th, 2016
     * 
     * @link http://en.wikipedia.org/wiki/Tagged_union [English]
     * 
     * @uses Phabstractic\Data\Types\Restrictions to define data type restrictions
     * 
     * @version 3.0
     * 
     */
    class TaggedUnion {
        
        /**
         * The type of the data at any given moment.
         * 
         * @var Phabstractic\Data\Types\Type
         * 
         */
        private $type = null;
        
        /**
         * The type restrictions placed on the tagged union
         * 
         * NOTE: v3.0 dynamic beyond being set at construction now
         * 
         * @var Phabstractic\Data\Types\Restrictions
         * 
         */
        private $restrictions = null;
        
        /**
         * The actual value of the TaggedUnion at any given moment.
         * 
         * @var mixed
         * 
         */
        private $value = null;
        
        /**
         * Get the restrictions
         * 
         * Returns the restrictions object that this tagged union is using
         * 
         * @return Phabstractic\Data\Types\Restrictions
         * 
         */
        public function getRestrictions()
        {
            return $this->restrictions;
        }
        
        /**
         * Set the restrictions
         * 
         * Allows any type of object that has an isAllowed method
         * 
         * @param Phabstractic\Data\Types\Resource\FilterInterface
         * 
         */
        public function setRestrictions(TypesResource\FilterInterface $restrictions)
        {
            $this->restrictions = $restrictions;
        }
        
        /**
         * Retrieve the actual value of the Tagged Union
         * 
         * @return mixed
         * 
         */
        public function getValue()
        {
            return $this->get();
        }
        
        /**
         * Retrieve the type at the moment.
         * 
         * @return Phabstractic\Data\Types\Type
         * 
         */
        public function getType()
        {
            return $this->type;
        }
        
        /**
         * Defines a TaggedUnion
         * 
         * OBSOLETE: This is the only place where you can define the data type
         * restrictions of the tagged union.
         * 
         * Since v3 you can specify restrictions dynamically using setRestrictions
         * 
         * NOTE:  $this->type will be of Type::BASIC_NULL until set
         *        regardless of restrictions.
         * 
         * @uses Phabstractic\Data\Types\Type\stringToType
         * 
         * @param Phabstractic\Data\Types\Resource\FilterInterface $restrictions
         *          The data restrictions
         * 
         */
        public function __construct(TypesResource\FilterInterface $restrictions)
        {
            $this->type = Type\stringToType('BASIC_NULL');
            $this->restrictions = $restrictions;
            
        }

        /**
         * Sets the value, making sure it is allowed by the restrictions
         * 
         * Checks the value type of the given value, if it's allowed
         * then sets both the type member and value member.  Does not
         * return true/false/null because those are values that may be
         * passed.  Throws errors instead if there's a problem setting
         * the value.
         * 
         * @uses Phabstractic\Data\Types\Type\getValueType
         * 
         * @param mixed $value The value to set
         * 
         * @throws Phabstractic\Data\Types\Exception\RuntimeException
         *              if the given value is untypeable
         * @throws Phabstractic\Data\Types\Exception\InvalidArgumentException
         *              if the given value is not allowed
         * 
         */
        public function set( $value )
        {
            // Get the values type as a Phabstractic\Data\Types\Type object.
            // getValueType returns false on failure, which is possible
            // since it normally returns an object of Falcraft\Data\Types\Type
            // I can't think of a value that is untypeable at this moment.
            if (($typeEnum = Type\getValueType($value)) === false) {
                throw new TypesException\RuntimeException(
                    'Phabstractic\Data\Types\TaggedUnion->set: Untypeable Value');
            }
            
            if ($this->restrictions->isAllowed($typeEnum) == false) {
                throw new TypesException\InvalidArgumentException(
                    'Phabstractic\Data\Types\TaggedUnion->set: Illegal Value');
            }
            
            $this->type = $typeEnum;
            $this->value = $value;
        }
        
        /**
         * Get the value of the TaggedUnion
         * 
         * @return mixed The value of the TaggedUnion.
         * 
         */
        public function get()
        {
            return $this->value;
        }
        
        /**
         * Shortcut to return value of Tagged Union
         * 
         * @return mixed
         *
         */
        public function __invoke() {
            return $this->get();
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
                'restrictions' => $this->restrictions,
                'type' => $this->type,
                'value' => $this->value
            ];
        }
        
    }

}
