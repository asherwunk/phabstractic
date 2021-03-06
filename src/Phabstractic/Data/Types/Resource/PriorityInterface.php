<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Priority Interface
 * 
 * When figuring out what comes before or after, like in a priority queue,
 * the order might change.  This is a standardized method to queue the order
 * of things.  It assigns a piece of data to a number that represents its priority
 * in the list.
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
    /**
     * The basic priority interface
     * 
     * This is used mainly for the Priority based lists
     * 
     * This allows many different objects to be priority objects,
     * rather than messing with hard coded arrays, etc.
     * 
     * If you need data to be a priority you can easily use
     * the Priority class.
     *
     * CHANGELOG
     * 
     * 1.0: Created PriorityInterface - May 26th, 2013
     * 2.0: Refactored and re-formatted for inclusion
     *          in primus - April 11th, 2015
     * 3.0: added setPriority method for dynamic priority management
     *      reformatted for inclusion in phabstractic - July 21st, 2016
     * 
     * @link http://en.wikipedia.org/wiki/Priority_queue [English]
     * 
     * @version 3.0
     * 
     */
    interface PriorityInterface
    {
        /**
         * Retrieve the data associated with this priority
         * 
         * @return mixed $data The given data
         * 
         */
        public function getData();
        
        /**
         * Retrieve the data associated with this priority as a reference
         * 
         * @return mixed &$data The given data
         * 
         */
        public function &getDataReference();
        
        /**
         * Get the priority of the object
         * 
         * Generally, 0 is most urgent.
         * 
         * @return int The priority of the data
         * 
         */
        public function getPriority();
        
        /**
         * Set the priority of the object
         * 
         * Generally, 0 is most urgent.
         * 
         * @return bool True if set
         * 
         */
        public function setPriority($priority);
        
    }
    
}
