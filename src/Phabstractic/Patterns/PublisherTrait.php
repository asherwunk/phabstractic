<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Falcraft Libraries Pattern, Falcraft/Pattern: FALCRAFT LIBRARIES
 * 
 * This is a publisher implementation in the Observer/Publisher design
 * pattern.  This consists of registering one or more observers to the publisher
 * object (stores in a restricted set).  The publisher keeps track of a state
 * and whenever that state changes it announces it to all its observers.
 * 
 * In the Universal Event System (Phabstractic\Event) The states are event objects
 * 
 * @link http://phpmaster.com/understanding-the-observer-pattern/
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
namespace Phabstractic\Patterns
{
    require_once(realpath(__DIR__ . '/../') . '/falcraftLoad.php');
    
    $includes = array(// Being a publisher we implement the interface
                      '/Patterns/Resource/PublisherInterface.php',
                      // and we maintain a state
                      // (theoretically a publisher could track a publisher)
                      '/Patterns/Resource/StateInterface.php',
                      '/Patterns/Resource/ObserverInterface.php',
                      // We create a restricted set to observers only
                      '/Data/Types/Type.php',
                      '/Data/Types/Restrictions.php',
                      '/Data/Types/RestrictedSet.php',);
    
    falcraftLoad($includes, __NAMESPACE__ . __FILE__);
    
    use Phabstractic\Data\Types\Type;
    use Phabstractic\Data\Types;
    use Phabstractic\Patterns\Resource as PatternsResource;
    
    /**
     * Publisher Class - The basic minimal publisher functionality
     * 
     * This object keeps track of a state, thus implementing the state interface
     * When that state changes it notifies all the observer objects its collected
     * in its restricted set.
     * 
     * CHANGELOG
     * 
     * 1.0  created Publisher - August 16th, 2013
     * 2.0 Adapted to Primus2 - August 25th, 2015
     * 3.0: changed from class to trait
     *      reformatted for inclusion in phabstractic - July 27th, 2016
     * 
     * @version 3.0
     * 
     */
    trait PublisherTrait
    {
        /**
         * The observers (restricted set) that are listening to this publisher
         * 
         * @var Phabstractic\Data\Types\RestrictedSet
         * 
         */
        protected $publisherObservers = null;
        
        /**
         * The state of the publisher, can be any object in this basic
         * implementation
         * 
         * @var mixed
         * 
         */
         protected $publisherState = null;
        
        /**
         * Make sure $this->publisherObservers has been constructed
         * 
         */
        protected function constructPublisherObservers() {
            if ($this->publisherObservers == null) {
                $this->publisherObservers = new Types\RestrictedSet(
                    array(),
                    new Types\Restrictions(
                        array(Type::BASIC_NULL, Type::TYPED_OBJECT),
                        array('Phabstractic\\Patterns\\Resource\\ObserverInterface'),
                        array('strict' => true)
                    ),
                    array( 'strict' => true )
                );
            }
        }
     
         /**
         * Attach an observer object to this publisher
         * 
         * This places an observer into the restricted set, as well
         * as establishes this publisher as the observers subject
         * 
         * @param Phabstractic\Patterns\Resource\ObserverInterface $observer
         * 
         */
        public function attachObserver(PatternsResource\ObserverInterface $observer)
        {
            $this->constructPublisherObservers();
            
            if (!$this->publisherObservers->in($observer)) {
                $this->publisherObservers->add($observer);
                $observer->attachPublisher($this);
            }
        }
     
         /**
         * Detach a listener/observer from the publisher
         * 
         * Removes the observer from the restricted set
         * 
         * @param Phabstractic\Patterns\Resource\ObserverInterface $observer
         */
        public function detachObserver(PatternsResource\ObserverInterface $observer)
        {
            $this->constructPublisherObservers();
            
            if ($this->publisherObservers->in($observer)) {
                $this->publisherObservers->remove($observer);
                $observer->detachPublisher($this);
            }
        }
        
        /**
         * Unlink this publisher from all observers
         * 
         */
        public function unlinkFromObservers()
        {
            $this->constructPublisherObservers();
            
            Types\RestrictedSet::mapInternal(
                'detach',
                array($this),
                $this->publisherObservers
            );
            
            $this->publisherObservers->clear();
        }
     
         /**
         * Retrieve an ARRAY of the observing objects for this publisher
         * 
         * @return array
         * 
         */
        public function getObservers()
        {
            $this->constructPublisherObservers();
            
            return $this->publisherObservers->getPlainArray();
        }
        
        /**
         * Set the publishers state, this notifies the observers
         * 
         * @param Phabstractic\Pattern\Resource\StateInterface $state
         * 
         */
        public function setState($state)
        {
            $this->setStateObject($state);
        }
     
         /**
         * Set the publishers state, this notifies the observers
         * 
         * @param Phabstractic\Pattern\Resource\StateInterface $state
         * 
         */
        public function setStateObject(PatternsResource\StateInterface $state)
        {
            $this->state = $state;
            $this->announce();
        }
        
        /**
         * Retrieve the state, whatever it may contain
         * 
         * @return mixed
         * 
         */
        public function getState()
        {
            return $this->state;
        }
        
        /**
         * Retrieve the state object, whatever it may contain
         * 
         * @return Phabstractic\Pattern\Resource\StateInterface
         * 
         */
        public function getStateObject()
        {
            return $this->getState();
        }
     
         /**
         * Notify all the observers in the set of the state change, or otherwise
         * 
         */
        public function announce()
        {
            $this->constructPublisherObservers();
            
            Types\RestrictedSet::mapInternal(
                'notifyObserver',
                array(&$this, &$this->state),
                $this->publisherObservers
            );
            
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
                'state' => $this->publisherState,
                'publisherObservers' => $this->publisherObservers->getPlainArray(),
            ];
        }
        
    }
}
