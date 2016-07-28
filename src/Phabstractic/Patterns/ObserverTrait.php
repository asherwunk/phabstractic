<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Observer Pattern Implementation 
 * 
 * This is an observer implementation.  It takes a publisher (subject/target)
 * and can attach and detach itself from a publisher (reciprocal funtions exist
 * in publisher objects as well) The main part of the observer pattern is the
 * notify function.  This function is called when the attached publisher changes
 * state
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Patterns
 * @subpackage Event
 * 
 */

/**
 * Falcraft Libraries Pattern Implementations Namespace
 * 
 */
namespace Phabstractic\Patterns
{
	require_once(realpath(__DIR__ . '/../') . '/falcraftLoad.php');
    
    $includes = array(// We implement the observer interface
                      '/Patterns/Resource/PublisherInterface.php',
                      '/Patterns/Resource/StateInterface.php',
                      // These are type checked in notify function
                      '/Data/Types/Type.php',
                      '/Data/Types/Restrictions.php',
                      '/Data/Types/RestrictedSet.php',);
    
    falcraftLoad($includes, __NAMESPACE__ . __FILE__);
    
    use Phabstractic\Data\Types\Type;
    use Phabstractic\Data\Types;
    use Phabstractic\Patterns\Resource as PatternsResource;
    use Phabstractic\Features;
    
    /**
     * Observer Class - The basic minimal observer functionality
     * 
     * Any object inheriting from Observer is prepared to perform
     * a basic tricky function remapping handler.  The observer object
     * expects a string, and then converts that string into a method call
     * as the handler of the state change
     * 
     * CHANGELOG
     * 
     * 1.0  created Observer - August 16th, 2013
     * 2.0  Adapted Observer to Primus - August 25th, 2015
     * 3.0: changed to trait from class
     *      reformatted for inclusion in phabstractic - July 27th, 2016
     * 
     * @version 2.0
     */
    trait ObserverTrait
    {
        /**
         * The subjects that this observer is listening to
         * 
         * For an observer that listens to multiple publishers with
         * some added functionality see Phabstractic\Event\Aggregator
         * 
         * @var Phabstractic\Patterns\Resource\PublisherInterface
         * 
         */
        protected $observedSubjects = null;
        
        /**
         * Make sure $this->observedSubjects has been constructed
         * 
         */
        protected function constructObservedSubjects() {
            if ($this->observedSubjects == null) {
                $this->observedSubjects = new Types\RestrictedSet(
                    array(),
                    new Types\Restrictions(
                        array(Type::BASIC_NULL, Type::TYPED_OBJECT),
                        array('Phabstractic\\Patterns\\Resource\\PublisherInterface'),
                        array('strict' => true)
                    ),
                    array( 'strict' => true )
                );
            }
        }
        
        /**
         * This detaches the observer from its subject
         * 
         * Reciprocal methods exist in the publisher
         * 
         */
        public function detachPublisher(PatternsResource\PublisherInterface $publisher)
        {
            $this->constructObservedSubjects();
            
            if ($this->observedSubjects->in($publisher)) {
                $this->observedSubjects->remove($publisher);
                $publisher->detachObserver($this);
            }
        }
        
        /**
         * Attach this observer to a new publisher
         * 
         * This also acts like a 'set' function, and null
         * is an acceptable value to be passed
         * 
         * @param Phabstractic\Patterns\Resource\PublisherInterface
         * 
         */
        public function attachPublisher(PatternsResource\PublisherInterface $publisher)
        {
            $this->constructObservedSubjects();
            
            if (!$this->observedSubjects->in($publisher)) {
                $this->observedSubjects->add($publisher);
                $publisher->attachObserver($this);
            }
        }
        
        /**
         * Remove this observer from all publishers
         * 
         */
        public function unlinkFromPublishers()
        {
            $this->constructObservedSubjects();
            
            Types\RestrictedSet::mapInternal(
                'detachListener',
                array($this),
                $this->observedSubjects
            );
            
            $this->observedSubjects->clear();
        }
        
        /**
         * Return array of publishers
         * 
         * @return array
         * 
         */
        public function getPublishers()
        {
            $this->constructObservedSubjects();
            
            return $this->observedSubjects->getPlainArray();
        }
     
         /**
         * The meat of the observer.
         * 
         * This is where a state change gets transformed into an action
         * 
         * In this case the expected state change is a string, which
         * gets turned into a method name and called (presumably defined
         * in an extended class) [publishedFunction]
         * 
         * @param Phabstractic\Patterns\Resource\PublisherInterface &$publisher
         * @param Phabstractic\Patterns\Resource\StateInterface &$state
         * 
         * @return bool True if event is handled
         * 
         */
        public function notifyObserver(
            PatternsResource\PublisherInterface &$publisher,
            PatternsResource\StateInterface &$state
        ) {
            // default behavior
            if (is_string($state->getState())) {
                $method = 'published' . ucfirst($state->getState());
            
                // looks for an observer method with the state name
                if (method_exists($this, $method)) {
                    call_user_func_array(
                        array($this, $method),
                        array($publisher, $state)
                    );
                    
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
            return [
                'observedSubjects' => $this->observedSubjects->getPlainArray(),
            ];
        }
    }
    
}
