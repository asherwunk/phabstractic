<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Event Interface
 * 
 * This file contains the basic event functionality that all 'universal' events
 * implement and use.
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Event
 * @subpackage Abstractions
 * 
 */
/**
 * Falcraft Libraries Event Resource Namespace
 * 
 */
namespace Phabstractic\Event\Resource
{
    require_once(realpath( __DIR__ . '/../../') . '/falcraftLoad.php');
    
    $includes = array(// type checks against stateinterface, implements
                      '/Patterns/Resource/StateInterface.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Patterns\Resource as PatternsResource;
    
    /**
     * Event Interface - Defines basic event functionality
     * 
     * This interface defines the basic functionality and members
     * that all events share.  This serves as the universal
     * event type checker.  All recognizable event in the
     * event system must implement this interface.
     * 
     * Technically an event is an object 'state', when the
     * state changes in a publisher, it's like 'triggering'
     * an event.
     * 
     * CHANGELOG
     * 
     * 1.0  Created Abstract Event - August 16th, 2013
     * 2.0  Integrated into Primus2 - September 12th, 2015
     * 3.0: reformatted for invlusion in phabstractic - July 29th, 2016
     * 
     * @version 3.0
     * 
     */
    interface EventInterface extends PatternsResource\StateInterface
    {
        
        /**
         * State Interface Status Get
         * 
         */
        public function getState();
        
        /**
         * State Interface Status Set
         *
         */
        public function setState($state);
        
        /**
         * This sets or morphs an event with new information
         * 
         * If morph is set, the event doesn't clear it's
         * information, and instead overwrites whats already there.
         * 
         * @param Phabstractic\Event\Resource\EventInterface $event
         *                  The state information encapsulated in an object
         * @param bool $morph
         *                  Whether we should replace or overwrite the object state
         * 
         */
        public function setStateWithEvent(EventInterface $event, $morph = true);
        
        /**
         * This sets or morphs an event with new information
         * 
         * If morph is set, the event doesn't clear it's
         * information, and instead overwrites whats already there.
         * 
         * @param array $event
         *                  The state information in an associative array
         * @param bool $morph
         *                  Whether we should replace or overwrite the object state
         * 
         */
        public function setStateWithArray(array $state, $morph = true);
        
        /**
         * Retrieve the event identifier
         * 
         * @return mixed
         * 
         */
        public function getIdentifier();
        
        /**
         * Return the categories of the event
         * 
         * NOTE: This returns an array
         * 
         * @return array
         * 
         */
        public function getCategories();
        
        /**
         * Add a category to the event
         * 
         * @param string $cat
         * 
         */
        public function addCategory($category);
        
        /**
         * Set a bunch of categories at once
         * 
         * @param array $cats
         * 
         */
        public function setCategories(array $categories);
        
        /**
         * Remove a category from the event
         * 
         * @param string $cat
         * 
         */
        public function removeCategory($category);

        /**
         * Does this event have a particular category?
         * 
         * @param string $cat
         * 
         * @return bool
         * 
         */
        public function isCategory($category);

        /**
         * Get the tags associated with this event
         * 
         * NOTE: This returns an array
         * 
         * @return array
         * 
         */
        public function getTags();

        /**
         * Associate a tag with this event
         * 
         * @param string $tag
         * 
         */
        public function addTag($tag);

        /**
         * Set a bunch of tags to be associated with this event
         * 
         * @param array $tags
         * 
         */
        public function setTags($tags);

        /**
         * Dissasociate a particular tag from this event
         * 
         * @param string $tag
         * 
         */
        public function removeTag($tag);

        /**
         * Is a tag associated with this particular event
         * 
         * @param string $tag
         * 
         * @return bool
         * 
         */
        public function isTag($tag);

        /**
         * Returns the generator of the event
         * 
         * @return object
         * 
         */
        public function getTarget();

        /**
         * Returns the generator of the event's reference
         * 
         * @return object
         * 
         */
        public function &getTargetReference();

        /**
         * Sets the generator of the event
         * 
         * NOTE: Use this function out of the construction
         *       of an event with caution
         * 
         * @param object $publisher
         * 
         */
        public function setTarget($target);

        /**
         * Sets the generator of the event's reference
         * 
         * @param object $publisher
         * 
         */
        public function setTargetReference(&$target);

        /**
         * Retrieve the data associated with the event
         * 
         * @return mixed
         * 
         */
        public function getData();

        /**
         * Retrieve the data associated with the event as a reference
         * 
         * @return mixed
         * 
         */
        public function &getDataReference();

        /**
         * Set the data associated with the event
         * 
         * This is implementation specific
         * 
         * @param mixed $data
         * 
         */
        public function setData($data);
        
        /**
         * Set the data associated with the event as a reference
         * 
         * This is implementation specific
         * 
         * @param mixed $data
         * 
         */
        public function setDataReference(&$data);
        
        /**
         * Retrieve the event originating function
         * 
         * @return string
         * 
         */
        public function getFunction();
        
        /**
         * Retrieve the event originating class
         * 
         * @return string
         * 
         */
        public function getClass();

        /**
         * Retrieve the event originating namespace
         * 
         * @return string
         * 
         */
        public function getNamespace();

        /**
         * Stop propagation of the event
         * 
         * This member is checked by the filters
         * 
         * @param bool $stop
         * 
         */
        public function stop();

        /**
         * Allow the event to continue propogating
         * 
         */
        public function proceed();

        /**
         * Has the propogation of this event stopped?
         * 
         * @return bool
         * 
         */
        public function isStopped();

        /**
         * Make this event unstoppable
         * 
         */
        public function force();

        /**
         * Make this event stoppable
         * 
         */
        public function subdue();

        /**
         * Is this event unstoppable?
         * 
         * Checked by filters
         * 
         * @return bool
         * 
         */
        public function isUnstoppable();
        
    }
    
}
