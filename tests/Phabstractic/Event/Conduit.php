<?php
require_once('src/Phabstractic/Patterns/Resource/ObserverInterface.php');
require_once('src/Phabstractic/Patterns/Resource/PublisherInterface.php');
require_once('src/Phabstractic/Patterns/PublisherTrait.php');
require_once('src/Phabstractic/Event/Filter.php');
require_once('src/Phabstractic/Event/Handler.php');
require_once('src/Phabstractic/Event/HandlerPriorityQueue.php');
require_once('src/Phabstractic/Data/Types/Priority.php');
require_once('src/Phabstractic/Event/Conduit.php');
require_once('src/Phabstractic/Event/GenericEvent.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Patterns\Resource as PatternsResource;
use Phabstractic\Patterns;
use Phabstractic\Event;
use Phabstractic\Data\Types;
use Phabstractic\Features\Resource as FeaturesResource;

class TestConduitPublisherClass implements PatternsResource\PublisherInterface {
    use Patterns\PublisherTrait;
    
}

class ConduitTest extends TestCase
{
    public function testBasicInstantiation() {
        $conduit = new Event\Conduit();
        
        $this->assertInstanceOf(Event\Conduit::class, $conduit);
        $this->assertInstanceOf(PatternsResource\ObserverInterface::class, $conduit);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $conduit);
        
    }
    
    public function testProperInstantiation() {
        $filter1 = new Event\Filter();
        $filter2 = new Event\Filter();
        $filter3 = new Event\Filter();
        $conduit = new Event\Conduit(array($filter1, $filter2, $filter3));
        
        $this->assertInstanceOf(Event\Conduit::class, $conduit);
        $this->assertInstanceOf(PatternsResource\ObserverInterface::class, $conduit);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $conduit);
        
        $this->assertEquals(array($filter1, $filter2, $filter3), $conduit->getFilters());
        
    }
    
    public function testAttachPublisher() {
        $publisher1 = new TestConduitPublisherClass();
        $publisher2 = new TestConduitPublisherClass();
        $conduit = new Event\Conduit();
        
        $conduit->attachPublisher($publisher1);
        $conduit->attachPublisher($publisher2);
        
        $this->assertEquals(array($publisher1, $publisher2), $conduit->getPublishers());
    }
    
    /**
     * @depends testAttachPublisher
     * 
     */
    public function testDetachPublisher() {
        $publisher1 = new TestConduitPublisherClass();
        $publisher2 = new TestConduitPublisherClass();
        $publisher3 = new TestConduitPublisherClass();
        $conduit = new Event\Conduit();
        
        $conduit->attachPublisher($publisher1);
        $conduit->attachPublisher($publisher2);
        $conduit->attachPublisher($publisher3);
        
        $this->assertEquals(array($publisher1, $publisher2, $publisher3), $conduit->getPublishers());
        
        $conduit->detachPublisher($publisher2);
        
        $this->assertEquals(array($publisher1, $publisher3), $conduit->getPublishers());
    }
    
    /**
     * @depends testAttachPublisher
     * 
     */
    public function testUnlinkFromPublishers() {
        $publisher1 = new TestConduitPublisherClass();
        $publisher2 = new TestConduitPublisherClass();
        $publisher3 = new TestConduitPublisherClass();
        $conduit = new Event\Conduit();
        
        $conduit->attachPublisher($publisher1);
        $conduit->attachPublisher($publisher2);
        $conduit->attachPublisher($publisher3);
        
        $this->assertEquals(array($publisher1, $publisher2, $publisher3), $conduit->getPublishers());
        
        $conduit->unlinkFromPublishers();
        
        $this->assertEquals(array(), $conduit->getPublishers());
    }
    
    public function testAddFilter() {
        $filter1 = new Event\Filter();
        $filter2 = new Event\Filter();
        $filter3 = new Event\Filter();
        $conduit = new Event\Conduit(array($filter1, $filter2));
        
        $conduit->addFilter($filter3);
        
        $this->assertEquals(array($filter1, $filter2, $filter3), $conduit->getFilters());
        
    }
    
    public function testRemoveFilter() {
        $filter1 = new Event\Filter();
        $filter2 = new Event\Filter();
        $filter3 = new Event\Filter();
        $conduit = new Event\Conduit(array($filter1, $filter2, $filter3));
        
        $conduit->removeFilter($filter2);
    
        $this->assertEquals(array($filter1, $filter3), $conduit->getFilters());
        
    }
    
    public function testSetFilter() {
        $filter1 = new Event\Filter();
        $filter2 = new Event\Filter();
        $filter3 = new Event\Filter();
        $conduit = new Event\Conduit(array($filter1, $filter2, $filter3));
        
        $queue1 = new Event\HandlerPriorityQueue();
        $queue2 = new Event\HandlerPriorityQueue();
        
        $conduit->setFilter($filter1, $queue1);
        $conduit->setFilter($filter3, $queue2);
        
        $this->assertEquals($queue1, $conduit->getHandlerPriorityQueue($filter1));
        $this->assertEquals($queue2, $conduit->getHandlerPriorityQueue($filter3));
        
    }
    
    public function testGetHandlerPriorityQueue() {
        $filter1 = new Event\Filter();
        $filter2 = new Event\Filter();
        $filter3 = new Event\Filter();
        $conduit = new Event\Conduit(array($filter1, $filter2, $filter3));
        
        $queue = new Event\HandlerPriorityQueue();
        $conduit->setFilter($filter2, $queue);
        
        $this->assertEquals($queue, $conduit->getHandlerPriorityQueue($filter2));
        
        $handler = new Event\Handler();
        $queue = &$conduit->getHandlerPriorityQueueReference($filter2);
        $priority = Types\Priority::buildPriority($handler, 0);
        $queue->push($priority);
        
        unset($queue);
        
        $queue = $conduit->getHandlerPriorityQueue($filter2);
        $queue = $queue->getList();
        $this->assertEquals($handler, $queue[0]->getData());
        
    }
    
    /**
     * @depends testGetHandlerPriorityQueue
     * 
     */
    public function testNotifyObserver() {
        $testvar = array();
        
        $publisher = new TestActorPublisherClass();
        
        $filter1 = new Event\Filter();
        $filter1->setTags(array('filter1'));
        $filter1->loosenUp();
        
        $filter2 = new Event\Filter();
        $filter2->setTags(array('filter2'));
        $filter2->loosenUp();
        
        $handler1 = Event\Handler::buildFromClosure(function() use (&$testvar) {$testvar[] = 1; return true;});
        $handler2 = Event\Handler::buildFromClosure(function() use (&$testvar) {$testvar[] = 2; return true;});
        $handler3 = Event\Handler::buildFromClosure(function() use (&$testvar) {$testvar[] = 3; return true;});
        $handler4 = Event\Handler::buildFromClosure(function() use (&$testvar) {$testvar[] = 4; return true;});
        $handler5 = Event\Handler::buildFromClosure(function() use (&$testvar) {$testvar[] = 5; return true;});
        $handler6 = Event\Handler::buildFromClosure(function() use (&$testvar) {$testvar[] = 6; return true;});
        
        $conduit = new Event\Conduit(array($filter1, $filter2));
        
        $queue = &$conduit->getHandlerPriorityQueueReference($filter1);
        $priority = Types\Priority::buildPriority($handler1, 0);
        $queue->push($priority);
        $priority = Types\Priority::buildPriority($handler2, 3);
        $queue->push($priority);
        $priority = Types\Priority::buildPriority($handler3, 1);
        $queue->push($priority);
        
        $queue = &$conduit->getHandlerPriorityQueueReference($filter2);
        $priority = Types\Priority::buildPriority($handler4, 0);
        $queue->push($priority);
        $priority = Types\Priority::buildPriority($handler5, 3);
        $queue->push($priority);
        $priority = Types\Priority::buildPriority($handler6, 5);
        $queue->push($priority);
        
        $conduit->attachPublisher($publisher);
        
        $event = new Event\GenericEvent(null, __FUNCTION__, __CLASS__, __NAMESPACE__, null, array('filter1'));
        
        $publisher->setStateObject($event);
        
        $this->assertEquals(array(1, 3, 2), $testvar);
        
        $testvar = array();
        
        $event->setTags(array('filter2'));
        
        $publisher->setStateObject($event);
        
        $this->assertEquals(array(4, 5, 6), $testvar);
        
    }
    
    /* public function testDebugInfo() {
        $filter = new Event\Filter();
        $handler = new Event\Handler();
        $actor = new Event\Actor($filter, $handler);
        
        ob_start();
        
        var_dump($actor);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?filter\"?\]?.*=\\>/", $output);
        $this->assertRegExp("/\\[?\"?handler\"?\]?.*=\\>/", $output);
    } */
    
}