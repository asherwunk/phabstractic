<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Publisher Design Pattern Interface
 * 
 * This file contains the publisher interface.  A publisher announces
 * changes in its state to its observing objects, which attach themselves
 * to the publisher.
 * 
 * This interface assumes that externals can 'attach' themselves to it.  In the
 * observer/publisher design pattern implementation found in phabstractic it is
 * built so that both publishers and observers can register listeners/broacasters
 * This can be utilized one-sided in implementation, whichever way is more
 * conducive to the system.
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
 * Falcraft Libraries Pattern Resource Implementations Namespace
 * 
 */
namespace Phabstractic\Patterns\Resource
{
    
    require_once(realpath(__DIR__ . '/../../') . '/falcraftLoad.php');
    
    $includes = array( // We attach and detach listeners (observers)
                       '/Patterns/Resource/ObserverInterface.php',
                       // We alter the object's state, type checking against state
                       '/Patterns/Resource/StateInterface.php', );
    
    falcraftLoad($includes, __FILE__);
    
    /**
     * Publisher Interface - Identifies Many Different Objects As Notifiable
     * 
     * Any object that implements the publisher interface is capable of
     * registering listeners and announcing a change in their state.  Announce 
     * can theoretically be called at any given time during the function of the
     * publisher
     * 
     * CHANGELOG
     * 
     * 1.0: created PublisherInterface - August 16th, 2013
     * 2.0: refactored file to fit Primus - April 2nd, 2015
     * 3.0: reformatted for inclusion in phabstractic - July 26th, 2016
     * 
     * @version 3.0
     * 
     */
    interface PublisherInterface extends StateInterface
    {
        
        /**
         * Attach An Observer
         * 
         * Attach an observer to this publisher so that it can be notified of
         * state changes
         * 
         * @param Phabstractic\Patterns\Resource\ObserverInterface $observer
         * 
         */
        public function attachObserver(ObserverInterface $observer);
        
        /**
         * Detach An Observer
         * 
         * Detach an observer from this publisher so it is no longer modified of
         * state changes
         * 
         * @param Phabstractic\Patterns\Resource\ObserverInterface $observer
         * 
         */
        public function detachObserver(ObserverInterface $observer);
        
        /**
         * Detach this publisher from all observers
         * 
         */
        public function unlinkFromObservers();
        
        /**
         * Get an array of observer objects
         * 
         * @return array
         * 
         */
        public function getObservers();
        
        /**
         * Notify Observers
         * 
         * Announce information to all the attached observers
         * 
         */
        public function announce();
        
        /**
         * Retrieve A State Object
         * 
         * @return Phabstractic\patterns\resource\StateInterface
         * 
         */
        public function getStateObject();
        
        /**
         * Set State Object
         * 
         * In an 'event' publisher/observer ecosystem, such
         *      as asherwunk/phabstractic/-/event:
         * 
         * Replaces the current state object with a new state object.  In order
         * for the observer/publisher pattern to work it is recommended that
         * this function notifies all observers of the host object.
         * 
         * @param Phabstractic\patterns\resource\StateInterface $state The State Object
         * 
         */
        public function setStateObject(StateInterface $state);
        
    }
    
}
