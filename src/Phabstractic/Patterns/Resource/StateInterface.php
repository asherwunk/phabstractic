<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Observer/Publisher State Interface
 * 
 * This was originally conceived for use in an 'event' system utilizing
 * publisher/observer design pattern for primus/falcraft.  This interface
 * could also apply to anything that has a changing state, such as a
 * finite state machine.
 * 
 * Following is the original documentation from primus/falcraft
 * 
 * This is the state interface to be used in the observer/publisher pattern.
 * Pretty straight forward, you set a state and get a state.  The state can be
 * any valid php value.
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Patterns
 * @subpackage Events
 * 
 */

/**
 * Falcraft Libraries Pattern Implementations Resource Namespace
 * 
 */
namespace Phabstractic\Patterns\Resource
{
    
    /**
     * State Interface
     * 
     * This was originally conceived for use in an 'event' system utilizing
     * publisher/observer design pattern for primus/falcraft.  This interface
     * could also apply to anything that has a changing state, such as a
     * finite state machine.
     * 
     * Following is the original documentation from primus/falcraft
     * 
     * This is used to represent an acceptable state object for a publisher. 
     * Any object that implements this interface is ready to be used in the 
     * publisher/observer pattern implemented in phabstractic
     * 
     * The idea is that a publishing object (publisher) has some form of
     * state to it.  When that state changes significantly (in other words
     * when an observer might be interested) it triggers all its observers
     * passing itself with it's state.
     * 
     * CHANGELOG
     * 
     * 1.0: created StateInterface - August 16th, 2013
     * 2.0: recreated for inclusion in Primus - April 2nd, 2015
     * 3.0: reformatted for inclusion in phabstractic - July 26th, 2016
     * 
     * @version 3.0
     * 
     */
    interface StateInterface
    {
        
        /**
         * Retrieve the State Object Data
         * 
         * @return mixed
         * 
         */
        public function getState();
        
        /**
         * Sets Object's State Data
         * 
         * In an 'event' publisher/observer ecosystem, such
         *      as asherwunk/phabstractic/-/event:
         * 
         * Replaces the current state object's data.  In order for the
         * observer/publisher pattern to work it is recommended that this
         * function notifies all observers of the host object.
         * 
         * @param mixed $stateData
         * 
         */
        public function setState($stateData);
    }
    
}
