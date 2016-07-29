<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * The Event System Aware Interface
 * 
 * This file contains the FilterInterface.  Events are categorized with many
 * different parameters.  This interface is for objects that sort and apply
 * those events based on those parameters
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Event
 * 
 */

/**
 * Falcraft Libraries Event Resource Namespace
 * 
 */
namespace Phabstractic\Event\Resource
{
    require_once(realpath( __DIR__ . '/../../') . '/falcraftLoad.php');
    
    /* TrackerInterface.php - An event aware object contains a tracker that
       can be attached to */
    
    $includes = array('/Event/Resource/EventInterface.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Event\Resource as EventResource;
    
    /**
     * Filter Interface
     * 
     * Like the filter interface in data types, this offers a function for
     * seeing if a particular event meets the criteria to be applied presumedly
     * based on the properties of the event.
     * 
     * CHANGELOG
     * 
     * 1.0 Documented Filter Interface - October 7th, 2013
     * 2.0 Integrated into Primus2 - September 13th, 2015
     * 3.0: changed isApplicable to isEventApplicable
     *      reformatted for inclusion in phabstractic - July 29th, 2016
     * 
     * @version 3.0
     * 
     */
    interface FilterInterface
    {
        /**
         * Test Event Applicability
         * 
         * @return bool
         * 
         */
        public function isEventApplicable(EventResource\EventInterface $event);
        
    }
    
}
