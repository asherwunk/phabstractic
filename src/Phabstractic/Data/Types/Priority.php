<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Priority Class
 * 
 * This file implements the Priority class.  Used mainly in PriorityQueue.
 * 
 * A priority is a number specifying urgency assigned to a piece of data.
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
    $includes = array(// implement priority interface
                      '/Data/Types/Resource/PriorityInterface.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types\Resource as TypesResource;
    
    /**
     * Priority Class - Defines a basic priority
     * 
     * This associates a piece of data with a priority
     * 
     * The lower the priority the more urgent it is, is the idea
     * 
     * @TODO Allow priorities to set themselves apart from list
     * 
     * CHANGELOG
     * 
     * 1.0: Created Priority May 26th, 2013
     * 2.0: Refactored and re-formatted for inclusion
     *          in primus - April 11th, 2015
     * 3.0: eliminated configuraiton (no options)
     *      implemented setPriority as per version 3 of the interface
     *      pass data as reference in constructor
     *      reformatted for inclusion in phabstractic - July 21st, 2016
     * 
     * @link http://en.wikipedia.org/wiki/Priority_queue [English]
     * 
     * @version 3.0
     * 
     */
    class Priority implements TypesResource\PriorityInterface
    {
        /**
         * The data to be associated with the urgency
         * 
         * @var mixed
         */
        private $data = null;
        
        /**
         * The urgency of the data, less is more
         * 
         * @var int
         */
        private $priority = 0;
        
        /**
         * Priority class Constructor.
         * 
         * Set the initial priority and associate the data object with it.
         * 
         * NOTE: Data is passed and assigned as a reference in the constructor
         * 
         * @param mixed $data The data associated with the priority
         * @param int $priority The urgency of the priority, less is more
         * 
         */
        public function __construct(&$data, $priority = 0)
        {
            $this->data = &$data;
            $this->priority = $priority;
        }
        
        /**
         * Retrieve the data from the priority
		 * 
         * @return mixed
         * 
         */
        public function getData()
        {
            return $this->data;
        }
        
        /**
         * Retrieve the data as a reference from the priority
		 * 
         * @return mixed
         * 
         */
        public function &getDataReference()
        {
            return $this->data;
        }
        
        /**
         * Get the urgency of the priority object, less is more
		 * 
         * @return int
         * 
         */
        public function getPriority()
        {
            return $this->priority;
        }
        
        /**
         * Set the priority of the object
         * 
         * Generally, 0 is most urgent.
         * 
         * @return bool True if set
         * 
         */
        public function setPriority($priority)
        {
            $this->priority = $priority;
        }
        
        /**
         * Construct a priority
         * 
         * This is useful if you're not using a reference as data
         * 
         * @param mixed $data
         * @param int priority
         * 
         */
        public static function buildPriority($data, $priority = 0)
        {
            return new Priority($data, $priority);
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
                'priority' => $this->priority,
                'data' => $this->data,
            ];
        }
    }
}
