<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Event Handling
 * 
 * This file contains a handler.  A handler encapsulates a callable element that
 * when triggered recieves an event.  It is useful to be contained in such a way
 * in priority queues, such as employed by the conduit.
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
    
    $includes = array(// we type check our notify method
                      '/Event/Resource/EventInterface.php',
                      // we implement the observerinterface
                      '/Patterns/Resource/ObserverInterface.php',
                      '/Patterns/ObserverTrait.php',
                      // we typecheck against publisherinterface
                      '/Patterns/Resource/PublisherInterface.php',
                      // we typecheck against stateinterface
                      '/Patterns/Resource/StateInterface.php',
                      // we return None as opposed to null from a function
                      '/Data/Types/None.php',
                      // we throw these errors
                      '/Event/Exception/InvalidArgumentException.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Patterns\Resource as PatternsResource;
    use Phabstractic\Patterns;
    use Phabstractic\Event\Resource as EventResource;
    use Phabstractic\Event\Exception as EventException;
    use Phabstractic\Data\Types;
    
    /**
     * The Handler Class
     * 
     * This class takes an object, a function, and a namespace.  It uses
     * these to encapsulate a callable that can be triggered.  It's the action
     * part of an event, when triggered it does something.  It can be paired
     * with a filter to enable proper hooks and such.  Without a paired filter,
     * (an object external to this class) it acts when notified by any event.
     * 
     * NOTE:  The filter is separate from the handler so that one filter can
     *        be used for multiple handlers ala conduit.
     * 
     * IMPORTANT! - To pass a closure to handler pass it as the function parameter
     * 
     * CHANGELOG
     * 
     * 1.0: Created Handler - August 16th, 2013
     * 1.1: Updated getDestination to handle function names - October 7th, 2013
     * 2.0: Integrated with Primus 2 in mind - October 13th, 2015
     * 3.0: integrated observer trait
     *      decoupled from priority
     *      modified constructor to default arguments
     *      set funcitons now determine if exists on call
     *      reformatted for inclusion in phabstractic - July 29th, 2016
     * 
     * @version 3.0
     * 
     */
    class Handler implements PatternsResource\ObserverInterface
    {
        use Patterns\ObserverTrait;

        /**
         * The object upon which the function (method) will be called
         * 
         * @var object
         * 
         */
        private $object;
        
        /**
         * The namespace of the object, or function to be called
         * 
         * @var string
         * 
         */
        private $namespace;
        
        /**
         * The name of the function or method to be called
         * 
         * @var string
         * 
         */
        private $function;
        
        /**
         * The Handler Constructor
         * 
         * This takes a destination (presumably an object, where our
         * code is going to GO [forward]), a function/method name and some
         * options
         * 
         * @param object $object The destination object
         * @param string $function The function/method name to be called
         * @param string $namespace The namespace definition of the destination
         * 
         */
        public function __construct(
            $object = null,
            $function = null,
            $namespace = null
        ) {
            $this->object = $object;
            $this->function = $function;
            $this->namespace = $namespace;
            
            if ($this->function !== null &&
                    !(is_callable($this->function, false, $nature) &&
                    $nature == 'Closure::__invoke')) {
                if ($this->object !== null && $this->namespace !== null) {
                    $ns = '';
                    
                    if ($this->namespace) {
                        $ns = $this->namespace;
                    }
                    
                    $ns .= '\\'; // Basic (global) namespace
                    
                    if (is_string($this->object)) {
                        // This is a class function
                        if (!method_exists($ns . $this->object, $this->function)) {
                            throw new EventException\InvalidArgumentException(
                                'Phabstractic\\Event\\Handler->__construct: ' .
                                'Non-existent method ' . $this->function . 
                                ' on class ' . $ns . $this->object);
                        }
                    } else {
                        // This is a specific function
                        if (!method_exists($this->object, $this->function)) {
                            throw new EventException\InvalidArgumentException(
                                'Phabstractic\\Event\\Handler->__construct: ' .
                                'Non-existent method ' . $this->function . 
                                ' on object of class ' . $ns . get_class($this->object));
                        }
                    }
                }
            }
        }
        
        /**
         * Return the destination object
         * 
         * @return object
         * 
         */
        public function getObject()
        {
            return $this->object;
        }
        
        /**
         * Set the destination object
         * 
         * CAREFUL: Make sure your method is still valid
         * 
         * @param object $object
         * 
         */
        public function setObject($object)
        {
            if ($this->function && $this->namespace !== null) {
                if (!(is_callable($this->function, false, $nature) &&
                        $nature == 'Closure::__invoke')) {
                     $ns = '';
                    
                    if ($this->namespace) {
                        $ns = $this->namespace;
                    }
                    
                    $ns .= '\\'; // Basic (global) namespace
                    
                    if (is_string($object)) {
                        // This is a class function
                        if (!method_exists($ns . $object, $this->function)) {
                            throw new EventException\InvalidArgumentException(
                                'Phabstractic\\Event\\Handler->setObject: ' .
                                'Non-existent method ' . $this->function . 
                                ' on class ' . $ns . $object);
                        }
                    } else {
                        // This is a specific function
                        if (!method_exists($object, $this->function)) {
                            throw new EventException\InvalidArgumentException(
                                'Phabstractic\\Event\\Handler->setObject: ' .
                                'Non-existent method ' . $this->function . 
                                ' on object of class ' . $ns . get_class($object));
                        }
                    }
                }
            }
            
            $this->object = $object;
        }
        
        /**
         * Get the apprioriate namespace for the object
         * 
         * If applicable
         * 
         * @return string
         * 
         */
        public function getNamespace()
        {
            return $this->namespace;
        }
        
        /**
         * Set the appropriate namespace for the object
         * 
         * If applicable
         * 
         * @param string $namespace
         * 
         */
        public function setNamespace($namespace)
        {
            if ($this->function && $this->object) {
                if (!(is_callable($this->function, false, $nature) &&
                        $nature == 'Closure::__invoke')) {
                    $ns = $namespace;
                    
                    $ns .= '\\'; // Basic (global) namespace
                    
                    if (is_string($this->object)) {
                        // This is a class function
                        if (!method_exists($ns . $this->object, $this->function)) {
                            throw new EventException\InvalidArgumentException(
                                'Phabstractic\\Event\\Handler->setNamespace: ' .
                                'Non-existent method ' . $this->function . 
                                ' on class ' . $ns . $this->object);
                        }
                    } else {
                        // This is a specific function
                        if (!method_exists($this->object, $this->function)) {
                            throw new EventException\InvalidArgumentException(
                                'Phabstractic\\Event\\Handler->setNamespace: ' .
                                'Non-existent method ' . $this->function . 
                                ' on object of class ' . $ns . get_class($this->object));
                        }
                    }
                }
            }
            
            $this->namespace = $namespace;
        }
        
        /**
         * Get the function/method name of the destination
         * 
         * @return string
         * 
         */
        public function getFunction()
        {
            return $this->function;
        }
        
        /**
         * Set the function/method name of the destination
         * 
         * @param string $function
         * 
         */
        public function setFunction($function)
        {
            if ($this->namespace !== null && $this->object) {
                if (!(is_callable($function, false, $nature) &&
                        $nature == 'Closure::__invoke')) {
                     $ns = '';
                    
                    if ($this->namespace) {
                        $ns = $this->namespace;
                    }
                    
                    $ns .= '\\'; // Basic (global) namespace
                    
                    if (is_string($this->object)) {
                        // This is a class function
                        if (!method_exists($ns . $this->object, $function)) {
                            throw new EventException\InvalidArgumentException(
                                'Phabstractic\\Event\\Handler->setFunction: ' .
                                'Non-existent method ' . $function . 
                                ' on class ' . $ns . $this->object);
                        }
                    } else {
                        // This is a specific function
                        if (!method_exists($this->object, $function)) {
                            throw new EventException\InvalidArgumentException(
                                'Phabstractic\\Event\\Handler->setFunction: ' .
                                'Non-existent method ' . $this->function . 
                                ' on object of class ' . $ns . get_class($this->object));
                        }
                    }
                }
            }
            
            $this->function = $function;
        }
        
        /**
         * Notify the object of an event
         * 
         * This causes the object to 'fire' on any event passed to it.
         * 
         * Don't subclass this class to build a filter in this area,
         * use a filter object instead. (See above)
         * 
         * @param Falcraft\Patterns\Resource\PublisherInterface &$publisher
         *              The event originator
         * @param Falcraft\Patterns\Resource\StateInterface &$state
         *              The 'event' object, or otherwise
         * 
         * @return bool Return true if handled (instanceof AbstractEvent)
         * 
         */
        public function notify(
            PatternsResource\PublisherInterface &$target,
            PatternsResource\StateInterface &$state
        ) {
            if ($state instanceof EventResource\EventInterface) {
                return $this->handle($state);
            } else {
                return false;
            }
            
        }
        
        /**
         * Handle the event
         * 
         * This method takes care of the details of getting an event ($e) to
         * whatever needs to be called.  The function/method/class is called
         * passing the event along.
         * 
         * This works with instance methods, class methods (static), and normal
         * defined functions, even closures, as long as they accept an
         * AbstractEvent $e.
         * 
         * NOTE: To use a class method (static), $this->object must be a string
         * 
         * @param Falcraft\Event\Resource\AbstractEvent &$e
         * 
         * @return mixed The result of the given function
         * 
         */
        public function handle(EventResource\AbstractEvent &$e)
        {
            $nature = '';
            if (is_callable($this->function, false, $nature) &&
                    $nature == 'Closure::__invoke' ) {
                // This is a closure, call it (requires no namespace, below)
                return call_user_func_array($this->function, array(&$e));
            }
            
            $ns = '';
            
            if ($this->namespace) {
                $ns = $this->namespace;
            }
            
            $ns .= '\\'; // Basic (global) namespace
            
            if ($this->object) {
                if (is_string($this->object)) {
                    // This is a class function
                    if (method_exists($ns . $this->object, $this->function)) {
                        return call_user_func_array(
                            $ns . $this->object . '::' . $this->function,
                            array($e)
                        );
                    }
                } else {
                    // This is a specific function
                    if (method_exists($this->object, $this->function)) {
                        return call_user_func_array(
                            array($this->object, $this->function),
                            array($e)
                        );
                    }
                }
            }
            
            if (($this->function && !$this->object) &&
                    is_callable($ns . $this->function)) {
                // This is a global function in a namespace
                return call_user_func_array($ns . $this->function, array($e));
            }
            
            // A function might return null, this is an actual null
            // This of course, means that functions shouldn't return null objects
            return new Types\None();
        }
        
        /**
         * Return the callback in whatever form it is
         * 
         * string for function or static function
         * array for object / class
         * Closure for, well, closures
         * 
         * @return string|array|\Closure
         * 
         */
        public function getDestination()
        {
            if ($this->object) {
                if (is_string($this->object)) {
                    // This is a static function
                    return $this->namespace . '\\' .
                           $this->object . '::' . $this->function;
                } else if (is_object($this->object)) {
                    return array($this->object, $this->function);
                }
            }
            
            if (is_callable($this->function, false, $nature)) {
                // Closures don't have namespaces
                if ($nature == 'Closure::__invoke') {
                    return $this->function;
                } else {
                    return $this->namespace . '\\' . $this->function;
                }
            }
        }
        
        /**
         * Build From Object-Method Array
         * 
         * @param array Callable array
         *
         */
        public static function buildFromArray(array $array) {
            return new Handler($array[0], $array[1], '');
        }
        
        /**
         * Build from Closure
         * 
         * @param closure
         * 
         */
        public static function buildFromClosure($closure) {
            return new Handler(null, $closure);
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
                'namespace' => $this->namespace,
                'object' => $this->object,
                'function' => $this->function,
            ];
        }
        
    }
    
}
