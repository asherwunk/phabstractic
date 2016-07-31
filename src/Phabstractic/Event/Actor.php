<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Event Actor (Filter -> Handler)
 * 
 * This file contains an actor.  An actor is the combination of a filter
 * and a handler.  If an event makes it through a filter, then it triggers
 * the handler
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
 * Falcraft Libraries Event Namespace
 * 
 */
namespace Phabstractic\Event
{
    require_once(realpath( __DIR__ . '/../') . '/falcraftLoad.php');
    
    /* Observer.php - We inherit from the observer pattern
       ObserverInterface.php - test against observer interface for handler
    
       FilterInterface.php
       PublisherInterface.php
       StateInterface.php - We typecheck against filter, publisher, and stateinterface
       
       AbstractEvent.php - We only operate on universal event objects */
    
    $includes = array(// we implement the observerinterface
                      '/Patterns/Resource/ObserverInterface.php',
                      // we use the observertrait
                      '/Patterns/ObserverTrait.php',
                      // we typo check against publisher and state
                      '/Patterns/Resource/PublisherInterface.php',
                      '/Patterns/Resource/StateInterface.php',
                      // we type check against filter interface
                      '/Event/Resource/FilterInterface.php',
                      '/Event/Resource/EventInterface.php',
                      // we type check against handler
                      '/Event/Handler.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Patterns\Resource as PatternsResource;
    use Phabstractic\Patterns;
    use Phabstractic\Event\Resource as EventResource;
    
    /**
     * The Actor Class
     * 
     * This class combines the functionality of a filter with a handler.
     * 
     * The object is presented with various events using notify, but only events
     * that meet the criteria of the filter actually make the handler fire
     * 
     * CHANGELOG
     * 
     * 1.0 Created Actor - August 16th, 2013
     * 2.0 Integrated Primus 2
     *     Changed Handler to ObserverInterface - October 14th, 2015
     * 3.0: included set methods
     *      removed subject from constructor
     *      eliminated destructor
     *      reformatted for inclusion in phabstarctic - July 30th, 2016
     * 
     * @version 2.0
     * 
     */
    class Actor implements PatternsResource\ObserverInterface
    {
        use Patterns\ObserverTrait {
            Patterns\ObserverTrait::__debugInfo as observerDebugInfo;
        }
        
        /**
         * The filter object (predicate)
         * 
         * This object identifies which events are acted upon
         * 
         * @var Phabstractic\Event\Filter
         * 
         */
        protected $filter;
        
        /**
         * The handler object
         * 
         * This contains what HAPPENS if the event is fired
         * 
         * @var Phabstractic\Event\Handler
         * 
         */
        protected $handler;
        
        /**
         * Retrieve the actor's filter object
         * 
         * @return Phabstractic\Event\Filter
         * 
         */
        public function getFilter()
        {
            return $this->filter;
        }
        
        /**
         * Set the actor's filter object
         * 
         * @param Phabstractic\Event\Filter
         * 
         */
        public function setFilter(EventResource\FilterInterface $filter)
        {
            $this->filter = $filter;
            return $this;
        }
        
        /**
         * Set the actor's handler
         * 
         * @return Phabstractic\Event\Handler
         * 
         */
        public function getHandler()
        {
            return $this->handler;
        }
        
        /**
         * Retrieve the actor's handler
         * 
         * @param Phabstractic\Event\Handler
         * 
         */
        public function setHandler(Handler $handler)
        {
            $this->handler = $handler;
            return $this;
        }
        
        /**
         * The Actor Class Constructor
         * 
         * The actor class IS an observer, so we make sure we got that covered
         * 
         * This is where the filter and handler are generally set
         * 
         * @param Phabstractic\Event\Filter $filter The filter to employ
         * @param Phabstractic\Event\Handler $handler The handler to employ
         * 
         */
        public function __construct(
            EventResource\FilterInterface $filter = null,
            PatternsResource\ObserverInterface $handler = null
        ) {
            $this->filter = $filter;
            $this->handler = $handler;
        }
        
        /**
         * Send the object and originator to the observer
         * 
         * Run it past the filter and if we meet success, then fire off
         * the handler
         * 
         * @param Falcraft\Patterns\Resource\PublisherInterface &$subject
         *              The originator of the event
         * @param Falcraft\Patterns\Resource\StateInterface &$state The
         *              originating event object
         * 
         * @return false if state doesn't cause a notification
         * 
         */
        public function notifyObserver(
            PatternsResource\PublisherInterface &$subject,
            PatternsResource\StateInterface &$state
        ) {
            if (!$this->filter || !$this->handler) {
                return false;
            }
            
            if ($state instanceof EventResource\EventInterface) {
                if ($this->filter->isEventApplicable($state)) {
                    $this->handler->notifyObserver($subject, $state);
                    return true;
                }
            }
            
            return false;
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
            $ret = $this->observerDebugInfo();
            
            return [
                'filter' => $this->filter,
                'handler' => $this->handler,
                'observedSubjects' => $ret['observedSubjects'],
            ];
        }
        
    }
    
}
