<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Event Filtering
 * 
 * This file contains the filter class.  A filter checks the
 * contents of a given event, as defined by it's own event fields,
 * to see if an event matches the requirements of the filter.
 * 
 * In other words, a predicate.
 * 
 * This class provides two built in filter functions, strictly applicable, and
 * loosely applicable (These only work on Generic Event and descendents)
 * You can also provide the class with your own calleable that is passed a
 * the Generic Event, 
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
    
    /* AbstractEvent - Checks incoming events if they are universal events
    
       Generic - Inherits from a generic event so that it can reflect it
       
       Set - Uses set operations to test predicate */
    
    $includes = array(// we type check against eventinterface
                      '/Event/Resource/EventInterface.php',
                      // we implement filterinterface
                      '/Event/Resource/FilterInterface.php',
                      // we inherit from genericevent
                      '/Event/GenericEvent.php',
                      // we are configurable
                      '/Features/ConfigurationTrait.php',
                      // we perform set operations on tags and categories
                      '/Data/Types/Set.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types;
    use Phabstractic\Event\Resource as EventResource;
    use Phabstractic\Features;
    
    /**
     * Event Filter Class - Defines basic event predicate
     * 
     * This object allows event handlers to filter what events they respond to.
     * It sits in front of the event handler and when an event contains all the
     * things (strictly) that the filter contains it is passed along to the
     * handler
     * 
     * CHANGELOG
     * 
     * 1.0 Created Event Filter - August 16th, 2013
     * 2.0 Integrated with Primus 2 - October 13th, 2015
     * 3.0: changed getTagsObject and getCategoriesObject to --Set
     *      reformatted for inclusion in phabstractic - July 29th, 2016
     * 
     * @version 3.0
     * 
     */
    class Filter extends GenericEvent implements EventResource\FilterInterface
    {
        use Features\ConfigurationTrait;
        
        /**
         * The custom function to call (optional)
         * 
         */
        private $test = null;
        
        /**
         * The Filter Class Constructor
         * 
         * This builds a filter class with no state
         * 
         * Pass in a strict option to make an event match EVERYTHING
         * in the filter object, rather than just one thing.
         * 
         * Options: strict - Strict Applicability
         * 
         * @param mixed $identifier
         * @param object $target
         * @param array $options
         * 
         */
        public function __construct($target = null, $options = array())
        {
            $this->configure($options);
            
            $this->build(
                $target,
                null,
                null,
                null,
                null
            );
        }
        
        /**
         * Wrapper for setStateWithEvent
         * 
         * @param Phabstractic\Event\Resource\FilterInterface
         * 
         */
        public function setStateWithFilter(
            EventResource\FilterInterface $filter,
            $morph = true
        ) {
            return $this->setStateWithEvent($filter, $morph);
        }
        
        /**
         * Initialize the filter with values
         * 
         * This mimics the constructor to initialize the filter with the
         * passed arguments
         * 
         * @see Falcraft\Event\Resource\Generc
         * 
         * @param object $target
         * @param string $function
         * @param string $class
         * @param string $namespace
         * @param mixed $data
         * @param array $tags
         * @param array $cats
         * @param array $options
         */
        public function build($target,
                              $function,
                              $class,
                              $namespace,
                              $data,
                              array $tags = array(),
                              array $cats = array()
        ) {
            parent::__construct($target,
                                $function,
                                $class,
                                $namespace,
                                $data,
                                $tags,
                                $cats);
            
            $this->identityPrefix = 'EventFilter';
            $this->setIdentifier();
        }
        
        /**
         * This returns an array containing the object state
         * 
         * This converts the event state to an array, tags and categories
         * are set up as sub-arrays, as well as the available object members
         * 
         * @return array
         * 
         */
        public function getState()
        {
            $ret = parent::getState();
            
            unset($ret['fields']['stop']);
            unset($ret['fields']['force']);
            
            return $ret;
        }
        
        /**
         * Make this filter require all fields, not just one
         * 
         * NOTE: Zend\Config\Config is constructed in feature to be malleable.
         * 
         */
        public function makeStrict()
        {
            $this->conf->strict = true;
        }
        
        /**
         * Loosen up the filter, so it only requires one field match
         * 
         * NOTE: Zend\Config\Config is constructed in feature to be malleable.
         * 
         */
        public function loosenUp()
        {
            $this->conf->strict = false;
        }
        
        /**
         * Enable a custom filter
         * 
         * Pass in the calleable value here
         * 
         * @param $function Custom calleable value
         * 
         */
        public function enableFunction($function)
        {
            $this->test = $function;
        }
        
        /**
         * Disbale the custom filter
         * 
         */
        public function disableFunction()
        {
            $this->test = null;
        }
        
        /**
         * Figures what kind of testing we need
         * 
         * @param Falcraft\Event\Resource\AbstractEvent
         * 
         * @return bool
         * 
         */
        public function isEventApplicable(EventResource\EventInterface $event)
        {
            if ($this->test) {
                if (is_callable($this->test, false)) {
                    return call_user_func_array(
                        $this->test,
                        array($event)
                    );
                }
            }
            
            if ($this->conf->strict) {
                return $this->isStrictlyApplicable($event);
            } else {
                return $this->isLooselyApplicable($event);
            }
            
        }
        
        /**
         * Takes an event and compares it against the filter
         * 
         * If it matches on one account, the event is propagated
         * 
         * If this object is 'strict' we call strictlyApplicable instead
         * 
         * @param Falcraft\Event\Resource\AbstractEvent
         * 
         * @return bool
         * 
         */
        public function isLooselyApplicable(EventResource\EventInterface $event)
        {
            if ($this->getIdentifier() == $event->getIdentifier()) {
                return true;
            }
            
            if ((!$this->target &&
                 !$this->function &&
                 !$this->class &&
                 !$this->namespace &&
                 $this->tags->isEmpty() &&
                 $this->categories->isEmpty())) {
                return true;
            }
            
            if (($this->target && $this->target == $event->getTarget()) ||
                ($this->function && $this->function == $event->getFunction()) ||
                ($this->class && $this->class == $event->getClass()) ||
                ($this->namespace && $this->namespace == $event->getNamespace()) ||
                (Types\Set::intersection($this->tags, $event->getTagsSet())) ||
                (Types\Set::intersection(
                    $this->categories, $event->getCategoriesSet()))) {
                
                return true;
            }
            
            return false;
        }
        
        /**
         * See if an event matches the filter exactly
         * 
         * If one thing doesn't match between the event and the filter
         * then we return false, instead of only one thing matching
         * 
         * @param Falcraft\Event\Resource\AbstractEvent $event
         *      The event to compare
         * 
         * @return bool
         * 
         */
        public function isStrictlyApplicable(
            EventResource\EventInterface $event,
            $includeIdentifier = false
        ) {
            if ($includeIdentifier) {
                if ($this->identifier != $event->getIdentifier()) {
                    return false;
                }
            }
            
            if (($this->target != $event->getTarget()) ||
                ($this->function != $event->getFunction()) ||
                ($this->class != $event->getClass()) ||
                ($this->namespace != $event->getNamespace()) ||
                (!Types\Set::subset($event->getTagsSet(), $this->tags)) ||
                (!Types\Set::subset(
                    $event->getCategoriesSet(),
                    $this->categories))) {
                
                return false;
            }
            
            return true;
        }
    }
}
