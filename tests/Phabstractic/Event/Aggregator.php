<?php
require_once('src/Phabstractic/Patterns/Resource/ObserverInterface.php');
require_once('src/Phabstractic/Patterns/Resource/PublisherInterface.php');
require_once('src/Phabstractic/Patterns/PublisherTrait.php');
require_once('src/Phabstractic/Patterns/ObserverTrait.php');
require_once('src/Phabstractic/Event/Filter.php');
require_once('src/Phabstractic/Event/GenericEvent.php');
require_once('src/Phabstractic/Event/Aggregator.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Patterns\Resource as PatternsResource;
use Phabstractic\Patterns;
use Phabstractic\Event;

class TestAggregatorPublisherClass implements PatternsResource\PublisherInterface {
    use Patterns\PublisherTrait;
    
}

class TestAggregatorObserverClass implements PatternsResource\ObserverInterface {
    use Patterns\ObserverTrait;
    
    public $testvar = 0;
    
    public function notifyObserver(
            PatternsResource\PublisherInterface &$publisher,
            PatternsResource\StateInterface &$state
        ) {
            $this->testvar = 1;
            return true;
        }
}

class AggregatorTest extends TestCase
{
    public function testBasicInstantiation() {
        $aggregator = new Event\Aggregator();
        
        $this->assertInstanceOf(PatternsResource\ObserverInterface::class, $aggregator);
        $this->assertInstanceOf(PatternsResource\PublisherInterface::class, $aggregator);
        $this->assertInstanceOf(Event\Aggregator::class, $aggregator);
        
    }
    
    public function testProperInstantiationWithoutPublishers() {
        $filter = new Event\Filter();
        $aggregator = new Event\Aggregator($filter);
        
        $this->assertInstanceOf(PatternsResource\ObserverInterface::class, $aggregator);
        $this->assertInstanceOf(PatternsResource\PublisherInterface::class, $aggregator);
        $this->assertInstanceOf(Event\Aggregator::class, $aggregator);
    }
    
    public function testProperInstantiationWithPublishers() {
        $publisher1 = new TestAggregatorPublisherClass();
        $publisher2 = new TestAggregatorPublisherClass();
        $filter = new Event\Filter();
        $aggregator = new Event\Aggregator($filter, array($publisher1, $publisher2));
        
        $this->assertInstanceOf(PatternsResource\ObserverInterface::class, $aggregator);
        $this->assertInstanceOf(PatternsResource\PublisherInterface::class, $aggregator);
        $this->assertInstanceOf(Event\Aggregator::class, $aggregator);
        
        $this->assertEquals(array($publisher1, $publisher2), $aggregator->getPublishers());
        
    }
    
    public function testAttachPublisher() {
        $publisher1 = new TestAggregatorPublisherClass();
        $publisher2 = new TestAggregatorPublisherClass();
        $aggregator = new Event\Aggregator();
        
        $aggregator->attachPublisher($publisher1);
        $aggregator->attachPublisher($publisher2);
        
        $this->assertEquals(array($publisher1, $publisher2), $aggregator->getPublishers());
    }
    
    /**
     * @depends testAttachPublisher
     * 
     */
    public function testDetachPublisher() {
        $publisher1 = new TestAggregatorPublisherClass();
        $publisher2 = new TestAggregatorPublisherClass();
        $publisher3 = new TestAggregatorPublisherClass();
        $aggregator = new Event\Aggregator();
        
        $aggregator->attachPublisher($publisher1);
        $aggregator->attachPublisher($publisher2);
        $aggregator->attachPublisher($publisher3);
        
        $this->assertEquals(array($publisher1, $publisher2, $publisher3), $aggregator->getPublishers());
        
        $aggregator->detachPublisher($publisher2);
        
        $this->assertEquals(array($publisher1, $publisher3), $aggregator->getPublishers());
    }
    
    /**
     * @depends testAttachPublisher
     * 
     */
    public function testUnlinkFromPublishers() {
        $publisher1 = new TestAggregatorPublisherClass();
        $publisher2 = new TestAggregatorPublisherClass();
        $publisher3 = new TestAggregatorPublisherClass();
        $aggregator = new Event\Aggregator();
        
        $aggregator->attachPublisher($publisher1);
        $aggregator->attachPublisher($publisher2);
        $aggregator->attachPublisher($publisher3);
        
        $this->assertEquals(array($publisher1, $publisher2, $publisher3), $aggregator->getPublishers());
        
        $aggregator->unlinkFromPublishers();
        
        $this->assertEquals(array(), $aggregator->getPublishers());
    }
    
    public function testAttachObserver() {
        $observer1 = new TestAggregatorObserverClass();
        $observer2 = new TestAggregatorObserverClass();
        $aggregator = new Event\Aggregator();
        
        $aggregator->attachObserver($observer1);
        $aggregator->attachObserver($observer2);
        
        $this->assertEquals(array($observer1, $observer2), $aggregator->getObservers());
    }
    
    /**
     * @depends testAttachObserver
     * 
     */
    public function testDetachObserver() {
        $observer1 = new TestAggregatorObserverClass();
        $observer2 = new TestAggregatorObserverClass();
        $observer3 = new TestAggregatorObserverClass();
        $aggregator = new Event\Aggregator();
        
        $aggregator->attachObserver($observer1);
        $aggregator->attachObserver($observer2);
        $aggregator->attachObserver($observer3);
        
        $this->assertEquals(array($observer1, $observer2, $observer3), $aggregator->getObservers());
        
        $aggregator->detachObserver($observer2);
        
        $this->assertEquals(array($observer1, $observer3), $aggregator->getObservers());
    }
    
    /**
     * @depends testAttachObserver
     * 
     */
    public function testUnlinkFromObservers() {
        $observer1 = new TestAggregatorObserverClass();
        $observer2 = new TestAggregatorObserverClass();
        $observer3 = new TestAggregatorObserverClass();
        $aggregator = new Event\Aggregator();
        
        $aggregator->attachObserver($observer1);
        $aggregator->attachObserver($observer2);
        $aggregator->attachObserver($observer3);
        
        $this->assertEquals(array($observer1, $observer2, $observer3), $aggregator->getObservers());
        
        $aggregator->unlinkFromObservers();
        
        $this->assertEquals(array(), $aggregator->getObservers());
    }
    
    /**
     * @depends testAttachObserver
     * 
     */
    public function testState() {
        $observer1 = new TestAggregatorObserverClass();
        $observer2 = new TestAggregatorObserverClass();
        $observer3 = new TestAggregatorObserverClass();
        $aggregator = new Event\Aggregator();
        
        $aggregator->attachObserver($observer1);
        $aggregator->attachObserver($observer2);
        $aggregator->attachObserver($observer3);
        
        $this->assertEquals(0, $observer1->testvar);
        $this->assertEquals(0, $observer2->testvar);
        $this->assertEquals(0, $observer3->testvar);
        
        $this->assertEquals(array($observer1, $observer2, $observer3), $aggregator->getObservers());
        
        $event = new Event\GenericEvent(null, __FUNCTION__, __CLASS__, __NAMESPACE__, null);
        
        $aggregator->setStateObject($event);
        
        $this->assertEquals(1, $observer1->testvar);
        $this->assertEquals(1, $observer2->testvar);
        $this->assertEquals(1, $observer3->testvar);
        
        $observer1->testvar = 0;
        $observer2->testvar = 0;
        $observer3->testvar = 0;
        
        $this->assertEquals($event->getState(), $aggregator->getStateObject()->getState());
        
        $aggregator->detachObserver($observer2);
        
        $aggregator->setStateObject($event);
        
        $this->assertEquals(1, $observer1->testvar);
        $this->assertEquals(0, $observer2->testvar);
        $this->assertEquals(1, $observer3->testvar);
        
    }
    
    public function testNotifyObserver() {
        $publisher1 = new TestAggregatorPublisherClass();
        $publisher2 = new TestAggregatorPublisherClass();
        $publisher3 = new TestAggregatorPublisherClass();
        
        $filter = new Event\Filter();
        $filter->setTags(array('tag1', 'tag2'));
        $filter->loosenUp();
        
        $observer = new TestAggregatorObserverClass();
        
        $aggregator = new Event\Aggregator($filter, array($publisher1, $publisher2, $publisher3));
        $aggregator->attachObserver($observer);
        
        $event = new Event\GenericEvent(null, __FUNCTION__, __CLASS__, __NAMESPACE__, null, array('tag1'));
        
        $publisher1->setStateObject($event);
        $this->assertEquals(1, $observer->testvar);
        $observer->testvar = 0;
        
        $publisher2->setStateObject($event);
        $this->assertEquals(1, $observer->testvar);
        $observer->testvar = 0;
        
        $publisher3->setStateObject($event);
        $this->assertEquals(1, $observer->testvar);
        $observer->testvar = 0;
        
        $filter->setStateWithEvent($event);
        $filter->addTag('tag2');
        $filter->makeStrict();
        $aggregator->setFilter($filter);
        
        $publisher1->setStateObject($event);
        $this->assertEquals(0, $observer->testvar);
        
        $publisher2->setStateObject($event);
        $this->assertEquals(0, $observer->testvar);
        
        $publisher3->setStateObject($event);
        $this->assertEquals(0, $observer->testvar);
        
        $event->addTag('tag2');
        
        $publisher1->setStateObject($event);
        $this->assertEquals(1, $observer->testvar);
        $observer->testvar = 0;
        
        $publisher2->setStateObject($event);
        $this->assertEquals(1, $observer->testvar);
        $observer->testvar = 0;
        
        $publisher3->setStateObject($event);
        $this->assertEquals(1, $observer->testvar);
        $observer->testvar = 0;
        
    }
    
    public function testDebugInfo() {
        $filter = new Event\Filter();
        $publisher1 = new TestAggregatorPublisherClass();
        $aggregator = new Event\Aggregator($filter, array($publisher1));
        
        ob_start();
        
        var_dump($aggregator);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?filter\"?\]?.*=\\>/", $output);
        $this->assertRegExp("/\\[?\"?publisherObservers\"?\]?.*=\\>\n.*array\\(0\\)/", $output);
        $this->assertRegExp("/\\[?\"?observedSubjects\"?\]?.*=\\>\n.*array\\(1\\)/", $output);
    }
    
}