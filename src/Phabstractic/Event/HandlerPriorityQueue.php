<?php
/**
 * A priority queue of handlers
 * 
 * This file contains a priority queue of handlers.  This is handy in a conduit
 * where there may be one filter, and then a series of handlers that act on the
 * event that gets past that filter.  They must do it in some order, so to make
 * that order controllable we have the Handler Priority Queue
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
        
    $includes = array(// we inherit from the priorityqueue
                      '/Data/Types/PriorityQueue.php',
                      // we typecheck against eventinterface in propogate
                      '/Event/Resource/EventInterface.php',
                      // we implement observerinterface
                      '/Patterns/Resource/ObserverInterface.php',
                      // we use the observertrait
                      '/Patterns/ObserverTrait.php',
                      // we type check against publisher and state in notify
                      '/Patterns/Resource/PublisherInterface.php',
                      '/Patterns/Resource/StateInterface.php',);
        
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Event\Resource as EventResource;
    use Phabstractic\Patterns\Resource as PatternsResource;
    use Phabstractic\Patterns;
    use Phabstractic\Data\Types;
    
    /**
     * The Handler Priority Queue Class
     * 
     * This class lines up Handlers in a line of priority.  It then is able
     * to propagate an event down the handlers 'chain'.
     * 
     * CHANGELOG
     * 
     * 1.0: Created Handler Priority Queue - August 16th, 2013
     * 1.1: Eliminated __destruct as obselete - October 7th, 2013
     * 2.0: Integrated with Primus 2 in mind - October 14th, 2015
     * 3.0: reformatted for inclusion in phabstractic - July 30th, 2016
     * 
     * @version 3.0
     * 
     */
    class HandlerPriorityQueue extends Types\PriorityQueue implements
        PatternsResource\ObserverInterface
    {
        use Patterns\ObserverTrait {
            Patterns\ObserverTrait::__debugInfo as observerDebugInfo;
        }
        
        /**
         * Notify the object of an event
         * 
         * This causes the object to 'fire' on any event passed to it.
         * 
         * Don't subclass this class to build a filter in this area,
         * use a filter object instead. See handler class description
         * 
         * @param Phabstractic\Patterns\Resource\PublisherInterface &$publisher
         *              The event originator
         * @param Phabstractic\Patterns\Resource\StateInterface &$state
         *              The 'event' object, or otherwise
         * 
         * @return bool Return true if propagated (state is AbstractEvent)
         * 
         */
        public function notifyObserver(
            PatternsResource\PublisherInterface &$target,
            PatternsResource\StateInterface &$state
        ) {
            if ($state instanceof EventResource\EventInterface) {
                $this->propagate($target, $state);
                return true;
            } else {
                return false;
            }
            
        }
        
        /**
         * Propagate an event down through the list of handlers
         * 
         * This checks if an event has been stopped or is unstoppable
         * These can be set in the handlers themselves.
         * 
         * NOTE: PriorityQueue inherits from AbsractSortedList
         *       getList() will already be sorted with the appropriate queues
         * 
         * It's important that the event passed is a reference, so that it can
         * be potentially stopped.
         * 
         * @param Phabstractic\Patterns\Resource\PublisherInterface &$p
         *              The event originator
         * @param Phabstractic\Event\Resource\AbstractEvent &$e
         *              The actual event object
         * 
         */
        public function propagate(
            PatternsResource\PublisherInterface &$publisher,
            EventResource\EventInterface &$event
        ) {
            // foreach doesn't guarantee proper order
            foreach ($this->getList() as $priority) {
                $handler = $priority->getData();
                if ($handler instanceof PatternsResource\ObserverInterface) {
                    if ($event->isUnstoppable() || !$event->isStopped()) {
                        $handler->notifyObserver($publisher, $event);
                    }
                }
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
            $ret = $this->observerDebugInfo();
            
            return [
                'options' => array('strict' => $this->conf->strict,),
                'list' => $this->list,
                'observedSubjects' => $ret['observedSubjects'],
            ];
        }
        
    }
}
