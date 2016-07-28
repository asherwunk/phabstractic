<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */
   
/**
 * This file contains the observer interface
 * 
 * Pretty simple, an observer needs to be notified of a change of state from
 * a publisher
 * 
 * This interface assumes that externals can 'attach' themselves to it.  In the
 * observer/publisher design pattern implementation found in phabstractic it is
 * built so that both publishers and observers can register listeners/broacasters
 * This can be utilized one-sided, or coupled together in implementation,
 * whichever way is more conducive to the system.
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
 * Falcraft Libraries Pattern Implementations Namespace
 * 
 */
namespace Phabstractic\Patterns\Resource
{
    require_once(realpath( __DIR__ . '/../../') . '/falcraftLoad.php');
    
    /* PublisherInterface.php
     * StateInterface.php - These are used in the notify function to type check
     */
    $includes = array(// we attach and detach publishers
                      '/Patterns/Resource/PublisherInterface.php',
                      // we pass the state information of a given publisher
                      '/Patterns/Resource/StateInterface.php',);
    
    falcraftLoad($includes, __FILE__);
    
    /**
     * Observer Interface - Identifies Many Different Objects As Notifiable
     * 
     * Any object that implements the notify function as specified below
     * can be 'tagged' (interface) as able to be an observer.
     * 
     * This interface assumes that externals can 'attach' themselves to it.  In the
     * observer/publisher design pattern implementation found in phabstractic it is
     * built so that both publishers and observers can register listeners/broacasters
     * This can be utilized one-sided, or coupled together in implementation,
     * whichever way is more conducive to the system.
     * 
     * CHANGELOG
     * 
     * 1.0  created ObserverInterface - August 16th, 2013
     * 2.0: reformatted for inclusion in phabstractic - July 26th, 2016
     * 
     * @version 2.0
     * 
     */
    interface ObserverInterface
    {
        /**
         * When a state changes or something happens, this function is called
         * 
         * It accepts any capable publisher, and any capable state change information.
         * 
         * This for example is used in the universal event system, where state is the
         * most recent event
         * 
         * @param Phabstractic\Patterns\Resource\PublisherInterface &$publisher
         * @param Phabstractic\Patterns\Resource\StateInterface &$state
         * 
         */
        public function notifyObserver(
            PublisherInterface &$publisher,
            StateInterface &$state
        );
        
        /**
         * Attach a publisher object to be listened to
         * 
         * @param Phabstractic\Patterns\Resource\PublisherInterface $publisher
         * 
         */
        public function attachPublisher(PublisherInterface $publisher);
        
        /**
         * Detach an existing publisher object from being listened to
         * 
         * @param Phabstractic\Patterns\Resource\PublisherInterface $publisher
         *
         */
        public function detachPublisher(PublisherInterface $publisher);
        
        /**
         * Remove the observer from all registered publishers
         * 
         */
        public function unlinkFromPublishers();
        
        /**
         * Return array of publishers
         * 
         * @return array
         * 
         */
        public function getPublishers();
        
        
    }
    
}
