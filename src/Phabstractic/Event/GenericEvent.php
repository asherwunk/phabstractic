<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * A Generic Event
 * 
 * This file contains a generic implementation of the AbstractEvent
 * abstract class.  This can serve as the foundation for all other
 * types of inherited events (or you can subclass AbstractEvent if you want)
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programing-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Event
 * 
 */

/**
 * Falcraft Libraries Event Resource Namespace
 * 
 */
namespace Phabstractic\Event
{
    require_once(realpath( __DIR__ . '/../') . '/falcraftLoad.php');
    
    $includes = array(// we inherit from abstract event
                      '/Event/Resource/AbstractEvent.php',
                      // we set a unique identifier
                      '/Features/IdentityTrait.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Event\Resource as EventResource;
    use Phabstractic\Features;
    
    /**
     * Generic Event Class - Provides a simple implementation of AbstractEvent
     * 
     * This class provides the member methods necessary to instantiate
     * or inherit an AbstractEvent.
     * 
     * It is recommended that all events inherit from the generic event
     * class to be used in the universal event system
     * 
     * CHANGELOG
     * 
     * 1.0 Created Generic Event - August 16th, 2013
     * 2.0 Integrated Generic Event into Primus2 - September 13th, 2015
     * 3.0: reformatted for inclusion in phabstractic - July 29th., 2016
     * 
     * @version 3.0
     * 
     */
    class GenericEvent extends EventResource\AbstractEvent
    {
        use Features\IdentityTrait;
        
        /**
         * The Generic Event Constructor
         * 
         * This is implements an event, setting all the necessary fields
         * 
         * @param object $target The target of the event
         * @param string $function The function/method generating the event
         * @param string $class The class name generating the event if applicable
         * @param string $namespace The namespace of the generating point
         * @param mixed $data Any data associated with the event
         * @param array $tags An array of strings associating the event with a set of tags
         * @param array $cats An array of strings associating the event with a set of categories
         * @param array $options
         * 
         */
        public function __construct(
            $target,
            $function,
            $class,
            $namespace,
            $data = null,
            array $tags = array(),
            array $cats = array()
        ) {
            parent::__construct(
                $target,
                $function,
                $class,
                $namespace,
                $data,
                $tags,
                $cats);
            $this->identityPrefix = 'GenericEvent';
            $this->setIdentifier();
        }
        
        /**
         * Retrieve the event identifier
         * 
         * @return mixed
         * 
         */
        public function getIdentifier()
        {
            return $this->identifier;
        }
        
        /**
         * Set an Identifier
         * 
         * For the generic class we just generate a unique id
         * 
         * @param $identifier The identifier to set
         * 
         */
        protected function setIdentifier($identifier = '')
        {
            if ($identifier) {
                $this->identifier = $identifier;
            } else {
                $this->identifier = $this->getNewIdentity();
            }
        }
        
        /**
         * Set the data associated with this event
         * 
         * @param mixed $data
         * 
         */
        public function setData($data)
        {
            $this->data = $data;
        }
        
        /**
         * Set the data associated with this event as a reference
         * 
         * @param mixed $data
         * 
         */
        public function setDataReference(&$data)
        {
            $this->data = &$data;
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
            return [
                'identifier' => $this->identifier,
                'target' => $this->target,
                'data' => $this->data,
                'namespace' => $this->namespace,
                'class' => $this->class,
                'function' => $this->function,
                'tags' => $this->tags->getPlainArray(),
                'categories' => $this->categories->getPlainArray(),
                'force' => $this->force,
            ];
        }
        
    }
    
}
