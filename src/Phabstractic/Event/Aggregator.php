<?php
/**
 * Publisher Aggregation
 * 
 * This file contains an aggregate.  An aggregate basically brings a bunch of
 * publishers together, and funnels all their events into one avenue, or publisher.
 * 
 * @copyright Copyright 2015 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunK.me/programming-projects/phabstractic/ Framework URL
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
    
    /* Filter.php - Filter whether an event in itself changes the published
                    state of the aggregator
    
       Type.php
       Restrictions.php
       RestrictedSet.php - We use restricted sets to contain the publishers
                           and the observers. We have publishers we're listening
                           to, and observers listening to us
       
       ObserverInterface.php
       PublisherInterface.php
       StateInterface.php - Aggregator is both an observer (of others)
                            and a publisher (for others) */
        
    $includes = array(// we employ a filter
                      '/Event/Resource/FilterInterface.php',
                      // we type check against eventinterface
                      '/Event/Resource/EventInterface.php',
                      // we implement observerinterface
                      '/Patterns/Resource/ObserverInterface.php',
                      // using traits
                      '/Patterns/PublisherTrait.php',
                      '/Patterns/ObserverTrait.php',
                      // we type check against publisher and stateinterface
                      '/Patterns/Resource/PublisherInterface.php',
                      '/Patterns/Resource/StateInterface.php', );
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Event\Resource as EventResource;
    use Phabstractic\Patterns\Resource as PatternsResource;
    use Phabstractic\Patterns;
    
    /**
     * The Aggregator Class
     * 
     * This class puts a whole bunch of publishers under one roof.
     * 
     * They each vie to change the state (causing an event to fire) of the
     * aggregate with their own states, but they are filtered by a built
     * in filter  (usually a custom filter)
     * 
     * CHANGELOG
     * 
     * 1.0 Created Aggregator - August 16th, 2013
     * 2.0 Integrated into Primus 2
     *     Changed Filter to FilterInterface - October 14th, 2015
     * 3.0: updated to use observertrait and publishertrait
     *      reformatted for inclusion in phabstractic - July 31st, 2016
     * 
     * @version 2.0
     * 
     */
    class Aggregator implements
        PatternsResource\ObserverInterface,
        PatternsResource\PublisherInterface
    {
        use Patterns\PublisherTrait, Patterns\ObserverTrait {
            Patterns\PublisherTrait::__debugInfo as publisherDebugInfo;
            Patterns\ObserverTrait::__debugInfo as observerDebugInfo;
        }
        
        /**
         * The filter that determines when the aggregators state is changed
         * 
         * @var Falcraft\Event\Resource\Filter
         * 
         */
        protected $filter;
        
        /**
         * The Aggregator Class Constructor
         * 
         * This class is an observer and a publisher  We take in all our
         * publishers in an array and create another careful set for our
         * observers too.  We can also specify the head filter here too.
         * (Usually a custom filter for this class)
         * 
         * @param Falcraft\Event\Resource\Filter $filter The head filter
         * @param array $publishers The publishers we're listening to and aggregating
         * 
         */
        public function __construct(
            EventResource\FilterInterface $filter = null,
            array $publishers = array()
        ) {
            $this->filter = $filter;
            
            foreach ($publishers as $publisher) {
                $publisher->attachObserver($this);
                $this->attachPublisher($publisher);
            }
        }
        
        /**
         * Get the head filter that determines when the state is truly changed
         * 
         * @return Falcraft\Event\Resource\Filter
         * 
         */
        public function getFilter()
        {
            return $this->filter;
        }
        
        /**
         * Set the head filter that determines when the state is truly changed
         * 
         * @param Falcraft\Event\Resource\Filter $filter
         * 
         */
        public function setFilter(
            EventResource\FilterInterface $filter
        ) {
            $this->filter = $filter;
        }
        
        /**
         * Set the state of the aggregator, causes announce
         * 
         * We run the state through the filter, if present, and if it makes it
         * we change the state and announce it to all our listeners
         * 
         * @param Falcraft\Patterns\Resource\StateInterface $state
         * 
         * @return bool True if event is handled
         * 
         */
        public function setStateObject(
            PatternsResource\StateInterface $state
        ) {
            if ($state instanceof EventResource\EventInterface) {
                if ($this->filter && $this->filter->isEventApplicable($state)) {
                    $this->publisherState = $state;
                    $this->announce();
                } else if (!$this->filter) {
                    $this->publisherState = $state;
                    $this->announce();
                }
                
                return true;
            } else {
                return false;
            }
        }
        
        /**
         * Get notified of a state change from one of our publishers
         * 
         * @param Falcraft\Patterns\Resource\PublisherInterface $publisher
         * @param Falcraft\Patterns\Resource\StateInterface $state Maybe new state, (abstract event)
         * 
         * @return bool true if handled (is AbstractEvent)
         * 
         */
        public function notifyObserver(
            PatternsResource\PublisherInterface &$publisher,
            PatternsResource\StateInterface &$state
        ) {
            if ($state instanceof EventResource\EventInterface) {
                $this->setStateObject($state);
                return true;
            } else {
                return false;
            }
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
            $ret = array();
            $ret = array_merge($this->publisherDebugInfo(), $ret);
            $ret = array_merge($this->observerDebugInfo(), $ret);
            
            return [
                'filter' => $this->filter,
                'publisherObservers' => $ret['publisherObservers'],
                'observedSubjects' => $ret['observedSubjects'],
            ];
        }
        
    }
    
}
