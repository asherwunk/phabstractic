<?php
require_once('src/Phabstractic/Patterns/PublisherTrait.php');
require_once('src/Phabstractic/Patterns/Resource/PublisherInterface.php');
require_once('src/Phabstractic/Patterns/ObserverTrait.php');
require_once('src/Phabstractic/Patterns/Resource/ObserverInterface.php');
require_once('src/Phabstractic/Patterns/Resource/StateInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Patterns;
use Phabstractic\Patterns\Resource as PatternsResource;

class TestPublisherPublisherClass implements PatternsResource\PublisherInterface {
    use Patterns\PublisherTrait;
    
}

class TestPublisherObserverClass implements PatternsResource\ObserverInterface {
    use Patterns\ObserverTrait;
    
    public $testPublisher = 0;
    
    public function publishedTest() {
        $this->testPublisher++;
    }
}

class TestPublisherStateClass implements PatternsResource\StateInterface {
        
        public $data = '';
        
        public function getState() {
            return $this->data;
        }
        
        public function setState($stateData) {
            $this->data = $stateData;
        }
}

class PublisherTraitTest extends TestCase
{
    public function testInstantiation() {
        $publisher = new TestPublisherPublisherClass();
        
        $this->assertInstanceOf(PatternsResource\PublisherInterface::class, $publisher);
        
    }
    
    public function testAttachObserver() {
        $observer1 = new TestPublisherObserverClass();
        $observer2 = new TestPublisherObserverClass();
        $publisher = new TestPublisherPublisherClass();
        
        $publisher->attachObserver($observer1);
        $publisher->attachObserver($observer2);
        
        $this->assertEquals(array($observer1, $observer2), $publisher->getObservers());
    }
    
    /**
     * @depends testAttachObserver
     * 
     */
    public function testDetachObserver() {
        $observer1 = new TestPublisherObserverClass();
        $observer2 = new TestPublisherObserverClass();
        $observer3 = new TestPublisherObserverClass();
        $publisher = new TestPublisherPublisherClass();
        
        $publisher->attachObserver($observer1);
        $publisher->attachObserver($observer2);
        $publisher->attachObserver($observer3);
        
        $this->assertEquals(array($observer1, $observer2, $observer3), $publisher->getObservers());
        
        $publisher->detachObserver($observer2);
        
        $this->assertEquals(array($observer1, $observer3), $publisher->getObservers());
    }
    
    /**
     * @depends testAttachObserver
     * 
     */
    public function testUnlinkFromObservers() {
        $observer1 = new TestPublisherObserverClass();
        $observer2 = new TestPublisherObserverClass();
        $observer3 = new TestPublisherObserverClass();
        $publisher = new TestPublisherPublisherClass();
        
        $publisher->attachObserver($observer1);
        $publisher->attachObserver($observer2);
        $publisher->attachObserver($observer3);
        
        $this->assertEquals(array($observer1, $observer2, $observer3), $publisher->getObservers());
        
        $publisher->unlinkFromObservers();
        
        $this->assertEquals(array(), $publisher->getObservers());
    }
    
    /**
     * @depends testAttachObserver
     * 
     */
    public function testState() {
        $observer1 = new TestPublisherObserverClass();
        $observer2 = new TestPublisherObserverClass();
        $observer3 = new TestPublisherObserverClass();
        $publisher = new TestPublisherPublisherClass();
        
        $publisher->attachObserver($observer1);
        $publisher->attachObserver($observer2);
        $publisher->attachObserver($observer3);
        
        $this->assertEquals(0, $observer1->testPublisher);
        $this->assertEquals(0, $observer2->testPublisher);
        $this->assertEquals(0, $observer3->testPublisher);
        
        $this->assertEquals(array($observer1, $observer2, $observer3), $publisher->getObservers());
        
        $state = new TestPublisherStateClass();
        
        $state->setState('test');
        
        $publisher->setState($state);
        
        $this->assertEquals(1, $observer1->testPublisher);
        $this->assertEquals(1, $observer2->testPublisher);
        $this->assertEquals(1, $observer3->testPublisher);
        
        $this->assertEquals('test', $publisher->getStateObject()->getState());
        
        $publisher->detachObserver($observer2);
        
        $publisher->setState($state);
        
        $this->assertEquals(2, $observer1->testPublisher);
        $this->assertEquals(1, $observer2->testPublisher);
        $this->assertEquals(2, $observer3->testPublisher);
        
    }
    
    /**
     * @depends testAttachObserver
     * 
     */
    public function testDebugInfo() {
        $observer1 = new TestPublisherObserverClass();
        $publisher = new TestPublisherPublisherClass();
        
        $publisher->attachObserver($observer1);
        
        ob_start();
        
        var_dump($publisher);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?publisherObservers\"?\]?.*=\\>\n.*array\\(1\\)/", $output);
    }
    
}
