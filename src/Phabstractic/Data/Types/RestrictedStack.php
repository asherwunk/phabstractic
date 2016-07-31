<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * This file contains the Stack class, a form of a heap.
 * 
 * A stack is a form of a 'heap'.  It implements a LIFO (for last in, first out)
 * list of objects. This stack conforms to almost all of the PostScript stack
 * manipulators except for mark and cleartomark.
 * 
 * This stack is restricted to particular data types
 * 
 * @see Phabstractic\Data\Types\Resource\AbstractRestrictedList
 * @see Phabstractic\Data\Types\RestrictedList
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
    $includes = array(// we're configurable
                      '/Features/ConfigurationTrait.php',
                      '/Features/Resource/ConfigurationInterface.php',
                      // type check against FilterInterface
                      '/Data/Types/Resource/FilterInterface.php',
                      // inherit from RestrictedList
                      '/Data/Types/RestrictedList.php',
                      // some methods return None
                      '/Data/Types/None.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types\Resource as TypesResource;
    use Phabstractic\Data\Types\Exception as TypesException;
    use Phabstractic\Features;
    use Phabstractic\Features\Resource as FeaturesResource;
    
    /**
     * Restricted Stack Class
     * 
     * Defines a Stack Data Structure, but type restricted
     * 
     * Inherits from Falcraft\Data\Types\Resource\AbstractRestrictedList but
     * uses composites an internal Queue and wraps function calls to this object
     * 
     * CHANGELOG
     * 
     * 1.0: Created RestrictedQueue May 16th, 2013
     * 2.0: Refactored and re-formatted for inclusion
     *      in Primus - April 14th, 2015
     * 2.1: Corrected Queue Errors - September 5th, 2015
     * 2.2: Added getList like RestrictedQueue - September 5th, 2015
     * 3.0: inherits from restrictedlist
     *      reformatted for inclusion in phabstractic - July 21st, 2016
     * 3.0.1: implements configurationinterface - July 31st, 2016
     * 
     * @link http://en.wikipedia.org/wiki/Stack_(data_structure) [English]
     * 
     * @version 3.0.1
     * 
     */
    class RestrictedStack extends RestrictedList implements
        \ArrayAccess,
        FeaturesResource\ConfigurationInterface
    {
        use Features\ConfigurationTrait;
        
        /**
         * The RestrictedStack Constructor
         * 
         * Takes the data, runs it through the restrictions via the parent
         * class if all is successful (nothing is thrown), then it sets up the
         * list data
         * 
         * Options: strict - Do we raise exceptions when values are misaligned?
         * 
         * @param mixed $data The data to populate the internal member array
         * @param Phabstractic\Data\Resource\FilterInterface $restrictions
         *              The predicate type object
         * @param array $options The options for the array
         * 
         */
        function __construct(
            $data = null,
            TypesResource\FilterInterface $restrictions = null,
            $options = array()
        ) {
            $this->configure($options);
            
            parent::__construct($data, $restrictions, $options);
        }
        
        /**
         * Retrieve the top value of the queue
         * 
         * This does not POP the value off the queue
         * 
         * @return mixed
         * 
         */
        public function top()
        {
            if (!empty($this->list))
            {
                // this part is different
                $stack = $this->getStack();
                return $stack[0];
            } else {
                if ($this->conf->strict) {
                   throw new TypesException\RangeException(
                       'Phabstractic\\Data\\Types\\RestrictedStack->top: ' .
                       'top called on empty stack.');
                } else {
                    return new None();
                }
                
            }
            
        }
        
        /**
         * Return the $i'th element of the list
         * 
         * @param integer $i The numerical index into the list
         * 
         * @return mixed The value found at index of list: list[index]
         * 
         * @throws \RangeException If index is out of well... range.
         * 
         */
        public function index($i)
        {
            if ($i > ($l = count($this->list) - 1) || $i < 0)
            {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\RestrictedStack->index: ' .
                        'Index out of range');
                }
                
            } else {
                return $this->list[$l - $i];
            }
            
            return new None();
            
        }
        
        /**
         * Return the $i'th element of the list as a reference
         * 
         * @param integer $i The numerical index into the list
         * 
         * @return mixed The value at the list's numerical index as a reference
         * 
         * @throws \RangeException If index is out of well... range.
         *
         */
        public function &indexReference($i)
        {
            if ($i > ($l = count($this->list) - 1) || $i < 0)
            {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\RestrictedStack->indexReference: ' .
                        'Index out of range');
                }
            } else {
                return $this->list[$l - $i];
            }
            
            $none = new None();
            return $none;
        }
        
        /**
         * Returns the queue as an array
         * 
         * Note: Returns internal array
         * 
         * @return array The internal queue array
         * 
         */
        public function getStack() { 
            return array_reverse($this->list);
        } 
        
    }
}
