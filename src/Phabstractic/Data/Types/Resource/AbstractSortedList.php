<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Abstract Sorted List
 * 
 * This file contains the AbstractSortedList class.  This class defines an array
 * that is sorted using the protected user member function defined in the
 * implementation.
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
    $includes = array(// operates and inherits from restricted list
                      '/Data/Types/Resource/AbstractRestrictedList.php',
                      // type checks against this in constructor
                      '/Data/Types/Resource/FilterInterface.php',
                      // is configurable
                      '/Features/ConfigurationTrait.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Features;
    
    /**
     * Abstract Sorted List - Automatically Sorted List
     * 
     * This list depends on an implementation of a member function that
     * sorts the data.
     * 
     * CHANGELOG
     * 
     * 1.0: Created AbstractList - May 10th, 2013
     * 2.0: Refactored and re-formatted for inclusion
     *          in primus - April 11th, 2015
     * 3.0: reformatted for inclusion in phabstractic - July 21st, 2016
     * 
     * @link http://en.wikipedia.org/wiki/Stack_(data_structure) [English]
     * 
     * @version 3.0
     * 
     */
    abstract class AbstractSortedList extends AbstractRestrictedList
    {
        use Features\ConfigurationTrait;
        
        /**
         * The Sorted List constructor
         * 
         * Accepts data, and the obligatory options parameter
         * 
         * Passes the required restrictions onto the parent class along with
         * the options
         * 
         * NOTE:  In order to sort a list it MUST all be the same type, so
         *        restrictions are set up.
         * 
         * This instantiates the class and sets the index
         * 
         * @param mixed $data The data to initialize the queue
         * @param Phabstractic\Data\Types\Resource\FilterInterface
         *            The types filter
         * @param array $options The options to pass into the object
         * 
         */
        public function __construct(
            $data = null,
            FilterInterface $restrictions = null,
            $options = array()
        ) {
            $this->configure($options);
            
            parent::__construct($data, $restrictions, $options);
            
            // Auto sort according to comparison function provided by child
            usort($this->list, array($this, 'cmp'));
        }
    
        /**
         * The comparison function.
         * 
         * Returns -1 if $l is 'less than'/before $r
         *         0  if $l and $r are equal
         *         +1 if $l is 'greater than'/after $r
         */
        abstract protected function cmp($l, $r);
        
        /**
         * Returns the top value
         * 
         * This abstract class only sorts
         * 
         * @return string|Phabstractic\Data\Types\None 'Top' value of list otherwise
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if no top exists
         * 
         */
        public function top()
        {
            // Auto sort according to comparison function provided by child
            usort($this->list, array($this, 'cmp'));
            
        }
        
        /**
         * Returns the top value as a reference
         * 
         * This abstract class only sorts
         * 
         * @return string|Phabstractic\Data\Types\None
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if top is empty
         * 
         */
        public function &topReference()
        {
            // Auto sort according to comparison function provided by child
            usort($this->list, array($this, 'cmp'));
            
        }
        
        /**
         * Returns the data objects specified by the index request
         * 
         * This abstract class only sorts
         * 
         * @param int $i the given index
         * 
         * @return string|Phabstractic\Data\Types\None
         * 
         * @throws Phabstractic\Data\Types\Exception\RangException
         *              if index is out of range
         * 
         */
        public function index($i)
        {
            // Auto sort according to comparison function provided by child
            usort($this->list, array($this, 'cmp'));
            
        }
        
        /**
         * Returns the data objects specified by the index request
         * 
         * This abstract class only sorts
         * 
         * @param int $i the given index
         * 
         * @return string|Phabstractic\Data\Types\None
         * 
         * @throws Phabstractic\Data\Types\Exception\RangException
         *              if index is out of range
         * 
         */
        public function &indexReference($i)
        {
            // Auto sort according to comparison function provided by child
            usort($this->list, array($this, 'cmp'));
            
        }
        
        /**
         * Pop the item off the list
         * 
         * This abstract class only sorts
         * 
         * @return string|Falcraft\Data\Types\Null
         * 
         */
        public function pop()
        {
            // Auto sort according to comparison function provided by child
            usort($this->list, array($this, 'cmp'));
            
        }
        
        /**
         * Pop the item off the list
         * 
         * This abstract class only sorts
         * 
         * @return string|Falcraft\Data\Types\Null
         * 
         */
        public function &popReference()
        {
            // Auto sort according to comparison function provided by child
            usort($this->list, array($this, 'cmp'));
            
        }
        
        /**
         * Retrieve the list element of the list
         * 
         * @return array The current internal list member
         * 
         */
        public function getList()
        {
            // Auto sort according to comparison function provided by child
            usort($this->list, array($this, 'cmp'));
            
            return $this->list;
        }
        
    }
    
}
