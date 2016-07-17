<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Filter Data Type
 * 
 * Contains the AbstractFilter construction.  This defines a basic
 * implementation for the static function ::checkElements() that is open
 * to being overriden in a child object
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Data
 * @subpackage Resource
 * 
 */
 
/**
 * Falcraft Libraries Data Types Resource Namespace
 * 
 */
namespace Phabstractic\Data\Types\Resource
{
    require_once(realpath( __DIR__ . '/../../../') . '/falcraftLoad.php');
    
    /* This class contains a static function for returning unique values
       for an array that is more object compatible. */
    $includes = array('/Data/Types/Exception/InvalidArgumentException.php',
                      // implements filter interface
                      '/Data/Types/Resource/FilterInterface.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types\Exception as TypesException;
    use Phabstractic\Data\Types\Resource as TypesResource;
    
    /**
     * Filter Abstract Class - Defines a predicate data structure that defines an
     * allowed list
     * 
     * Allows values and types to be checked against internal predicate logic.
     * 
     * CHANGELOG
     * 
     * 1.0: Created AbstractFilter - April 11th, 2015
     * 2.0: reformatted for inclusion in phabstractic
     *      changed checkElements for loop to use correct value
     *      changed checkElements to strictly check for false
     *      changed checkElements to pass on strict, as wel as throw
     *      implements FilterInterface - July 13th, 2016
     * 
     * @version 2.0
     * 
     */
    abstract class AbstractFilter implements TypesResource\FilterInterface
    {
        
        /**
         * Is value allowd 'through' this filter?
         * 
         * This steps the given value through the filtering process.
         * 
         * For flexibility in checking (if container object is strict
         * or not) we provide a strict option in the function rather than the
         * object at constructor time.
         * 
         * @param mixed $type The type to check
         * @param bool $strict Should we throw errors?
         * 
         * @return bool Allowed or not?
         * 
         * @throws Phabstractic\Data\Types\Exception\InvalidArgumentException
         *          CAN throw this exception
         * 
         */
        abstract public function isAllowed($type, $strict = false);
        
        /**
         * Checks values to see if they fit through the filter
         * 
         * This goes through each value, and compares it against the fiter logic
         * 
         * @static
         * 
         * @param array $values The values to check against the restrictions
         * @param Resource\FilterInerface The filter to run them through
         * @param bool $strict Throw errors?
         * 
         * @return bool Valid types?
         * 
         */
        public static function checkElements(
            array $values,
            TypesResource\FilterInterface $filter,
            $strict = false)
        {
            foreach ($values as $value) {
                if ($filter->isAllowed($value, $strict) === false) {
                    if ($strict) {
                        throw new TypesException\InvalidArgumentException(
                            'Phabstractic\\Data\\Types\\Resource\\' .
                            'AbstractFilter->checkElements: Illegal Value');
                    }
                    
                    return false;
                }
                
            }
            
            return true;
        }
        
    }
    
}
