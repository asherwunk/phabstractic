<?php
/**
 * Event Conduit
 * 
 * This file contains the conduit class.  This is quite a formidable class.
 * 
 * A conduit basically listens on one end listening to many different
 * publishers And then a bunch of filters at the 'head' of a chain of handlers
 * (priorityqueue).
 * 
 * The publisher signals a state change (event) which is then passed on to
 * every filter and if those requirements are met, the event is passed down
 * that particular handler chain.
 * 
 * This uses a Map with filters as keys, and HandlerPriorityQueues as
 * the values.
 * 
 * @copyright Copyright 2015 Asher Wolfstein
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
    
    /* Observer.php - The conduit utilizes an aggregate to be an observer.
                      It must be an observer to 'listen' to an aggregator
     
       PublisherInterface.php
       StateInterface.php - Typecheck for publisher interfaces and state
                            interfaces (notify)
     
       Map.php - We use a map to keep track of our filters and handler chains
     
       Filter.php - We employ filters as the keys to our map
     
       Handler.php
       HandlerPriorityQueue.php - We use handler priority queues as the values of our map
       
       AbstractEvent.php - The states are themselves abstract events
       
       Aggregator.php - The aggregator is what we lsiten to */
    
    $includes = array(// we are configurable
                      '/Features/ConfigurationTrait.php',
                      '/Features/Resource/ConfigurationInterface.php',
                      // we implement observerinterface
                      '/Patterns/Resource/ObserverInterface.php',
                      // we use observertrait
                      '/Patterns/ObserverTrait.php',
                      // we type check against publisher and stateinterface
                      '/Patterns/Resource/PublisherInterface.php',
                      '/Patterns/Resource/StateInterface.php',
                      // we type check against filterinterface
                      '/Event/Resource/FilterInterface.php',
                      // we type check against handlerpriorityqueue
                      '/Event/HandlerPriorityQueue.php',
                      // we type check against eventinterface
                      '/Event/Resource/EventInterface.php',
                      // we utilize a map to keep track of handlers
                      '/Data/Types/Map.php',);
        
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Features;
    use Phabstractic\Features\Resource as FeaturesResource;
    use Phabstractic\Patterns\Resource as PatternsResource;
    use Phabstractic\Patterns;
    use Phabstractic\Event\Resource as EventResource;
    use Phabstractic\Data\Types;
    
    /**
     * The Conduit Class
     * 
     * This class uses observer to collect a bunch of events from
     * multiple sources and then employs a map (filters as keys,
     * handlerpriorityqueues as values) to send those events to each filter to
     * fire each handler chain.
     * 
     * CHANGELOG
     * 
     * 1.0: Created Conduit Class - August 16th, 2013
     * 1.1: Enabled Aggregator Injection - October 7th, 2013
     * 2.0: Integrated into Primus 2
     *      Changed filter concrete to FilterInterface - October 14th, 2015
     * 3.0: implements configurationinterface
     *      eliminated need for aggregator, just implements observer now
     *      reformatted for inclusion in phabstarctic - July 31st, 2016
     * 
     * @version 3.0
     * 
     */
    class Conduit implements
        PatternsResource\ObserverInterface,
        FeaturesResource\ConfigurationInterface
    {
        use Features\ConfigurationTrait, Patterns\ObserverTrait {
            Patterns\ObserverTrait::__debugInfo as observerDebugInfo;
        }
        
        /**
         * The actual conduit map
         * 
         * Keys are filters, values are chains
         * 
         * @var Falcraft\Data\Types\Map
         * 
         */
        private $map;
        
        /**
         * The Conduit Constructor
         * 
         * This takes an array of filters to set up the map
         * 
         * This can be a multidimensional array where 0 is the filter and 1 is
         * the priority queue Or it can just be a filter object, in which case
         * an empty priority queue is instantiated
         * 
         * @param array $filters
         * @param array $options
         * 
         */
        public function __construct(
            array $filters = array(),
            array $options = array()
        ) {
            $this->configure($options);
            
            $this->map = new Types\Map(
                array(),
                array('strict' => $this->conf->strict)
            );
            
            foreach ($filters as $filter) {
                if ($filter instanceof EventResource\FilterInterface) {
                    $this->map[$filter] = new HandlerPriorityQueue(
                        array(),
                        null,
                        array('strict' => $this->conf->strict)
                    );
                } else if (is_array( $filter )) {
                    $this->map[$filter[0]] = $filter[1];
                }
            }
        }
        
        /**
         * Retrieve all the filters registered with the conduit as an array
         * 
         * @return array
         * 
         */
        public function getFilters()
        {
            return $this->map->getKeys();
        }
        
        /**
         * Add a filter to the list of filters
         * 
         * Instantiates with an empty priority queue
         * 
         * @param Falcraft\Event\Resource\Filter
         * 
         */
        public function addFilter(EventResource\FilterInterface $filter)
        {
            $this->map[$filter] = new HandlerPriorityQueue(
                array(),
                null,
                array('strict' => $this->conf->strict)
            );
        }
        
        /**
         * Associate a given filter, in the map, with a given priority queue
         * 
         * @param Falcraft\Event\Resource\Filter $filter The filter to set
         * @param Falcraft\Event\Resource\HandlerPriorityQueue $queue The given queue to be used
         * 
         */
        public function setFilter(
            EventResource\FilterInterface $filter,
            HandlerPriorityQueue $queue
        ) {
            $this->map[$filter] = $queue;
        }
        
        /**
         * Remove a filter from the map, this also of course removes the queue it represents
         * 
         * @param Falcraft\Event\Resource\Filter $filter The filter to remove
         * 
         */
        public function removeFilter(
            EventResource\FilterInterface $filter
        ) {
            if (in_array($filter, $this->map->getKeys())) {
                unset($this->map[$filter]);
            }
        }
        
        /**
         * Retrieve HandlerPriorityQueue associated to given filter
         * 
         * @param Phabstractic\Event\Filter $filter
         * 
         * @return Phabstractic\Event\HandlerPriorityQueue
         * 
         */
        public function getHandlerPriorityQueue(
            EventResource\FilterInterface $filter
        ) {
            if (in_array($filter, $this->map->getKeys())) {
                return $this->map[$filter];
            }
        }
        
        /**
         * Retrieve HandlerPriorityQueue associated to given filter as reference
         * 
         * @param Phabstractic\Event\Filter $filter
         * 
         * @return Phabstractic\Event\HandlerPriorityQueue
         * 
         */
        public function &getHandlerPriorityQueueReference(
            EventResource\FilterInterface $filter
        ) {
            if (in_array($filter, $this->map->getKeys())) {
                return $this->map->findReference($filter);
            }
        }
        
        /**
         * The aggregate will call this class when it fires.
         * 
         * When a state change occurs and the notify function is called, we first make sure
         * it's an abstract event, otherwise we exit gracefully, and then we run it through 
         * the various filters and their event chains.  Events are handled as references
         * so they can be affected by the handlers they encounter (or filters, or conduits, etc)
         * 
         * @param Falcraft\Patterns\Resource\PublisherInterface &$publisher
         * @param Falcraft\Patterns\Resource\StateInterface &$state
         * 
         * @return bool true if handled (is AbstractEvent)
         * 
         */
        public function notifyObserver(
            PatternsResource\PublisherInterface &$publisher,
            PatternsResource\StateInterface &$state
        ) {
            if (!($state instanceof EventResource\EventInterface)) {
                return false;
            }
            
            foreach ($this->map as $filter => $queue) {
                if ($filter->isEventApplicable($state)) {
                    $queue->notifyObserver($publisher, $state);
                }
            }
            
            return true;
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
            $ret = array_merge($this->observerDebugInfo(), $ret);
            
            return [
                'observedSubjects' => $ret['observedSubjects'],
                'filters' => $this->map,
            ];
        }
        
    }
    
}
