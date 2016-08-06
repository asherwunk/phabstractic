<?php
require_once('src/Phabstractic/Patterns/PublisherTrait.php');
require_once('src/Phabstractic/Patterns/Resource/PublisherInterface.php');
require_once('src/Phabstractic/Patterns/ObserverTrait.php');
require_once('src/Phabstractic/Patterns/Resource/ObserverInterface.php');
require_once('src/Phabstractic/Patterns/Resource/StateInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Patterns;
use Phabstractic\Patterns\Resource as PatternsResource;

class TestObserverPublisherClass implements PatternsResource\PublisherInterface {
    use Patterns\PublisherTrait;
    
}

class TestObserverObserverClass implements PatternsResource\ObserverInterface {
    use Patterns\ObserverTrait;
    
    public $testPublisher = 0;
    
    public function publishedTest() {
        $this->testPublisher++;
    }
}

class TestObserverStateClass implements PatternsResource\StateInterface {
        
        public $data = '';
        
        public function getState() {
            return $this->data;
        }
        
        public function setState($stateData) {
            $this->data = $stateData;
        }
}

class ObserverTraitTest extends TestCase
{
    public function testInstantiation() {
        $observer = new TestObserverObserverClass();
        
        $this->assertInstanceOf(PatternsResource\ObserverInterface::class, $observer);
        
    }
    
    public function testAttachPublisher() {
        $publisher1 = new TestObserverPublisherClass();
        $publisher2 = new TestObserverPublisherClass();
        $observer = new TestObserverObserverClass();
        
        $observer->attachPublisher($publisher1);
        $observer->attachPublisher($publisher2);
        
        $this->assertEquals(array($publisher1, $publisher2), $observer->getPublishers());
    }
    
    /**
     * @depends testAttachPublisher
     * 
     */
    public function testDetachPublisher() {
        $publisher1 = new TestObserverPublisherClass();
        $publisher2 = new TestObserverPublisherClass();
        $publisher3 = new TestObserverPublisherClass();
        $observer = new TestObserverObserverClass();
        
        $observer->attachPublisher($publisher1);
        $observer->attachPublisher($publisher2);
        $observer->attachPublisher($publisher3);
        
        $this->assertEquals(array($publisher1, $publisher2, $publisher3), $observer->getPublishers());
        
        $observer->detachPublisher($publisher2);
        
        $this->assertEquals(array($publisher1, $publisher3), $observer->getPublishers());
    }
    
    /**
     * @depends testAttachPublisher
     * 
     */
    public function testUnlinkFromPublishers() {
        $publisher1 = new TestObserverPublisherClass();
        $publisher2 = new TestObserverPublisherClass();
        $publisher3 = new TestObserverPublisherClass();
        $observer = new TestObserverObserverClass();
        
        $observer->attachPublisher($publisher1);
        $observer->attachPublisher($publisher2);
        $observer->attachPublisher($publisher3);
        
        $this->assertEquals(array($publisher1, $publisher2, $publisher3), $observer->getPublishers());
        
        $observer->unlinkFromPublishers();
        
        $this->assertEquals(array(), $observer->getPublishers());
    }
    
    /**
     * @depends testAttachPublisher
     * 
     */
    public function testState() {
        $publisher1 = new TestObserverPublisherClass();
        $publisher2 = new TestObserverPublisherClass();
        $publisher3 = new TestObserverPublisherClass();
        $observer = new TestObserverObserverClass();
        
        $observer->attachPublisher($publisher1);
        $observer->attachPublisher($publisher2);
        $observer->attachPublisher($publisher3);
        
        $this->assertEquals(0, $observer->testPublisher);
        
        $this->assertEquals(array($publisher1, $publisher2, $publisher3), $observer->getPublishers());
        
        $state = new TestPublisherStateClass();
        
        $state->setState('test');
        
        $publisher1->setStateObject($state);
        $publisher2->setStateObject($state);
        $publisher3->setStateObject($state);
        
        $this->assertEquals(3, $observer->testPublisher);
        
        $this->assertEquals('test', $state->getState());
        
        $observer->detachPublisher($publisher2);
        
        $publisher1->setStateObject($state);
        $publisher2->setStateObject($state);
        $publisher3->setStateObject($state);
        
        $this->assertEquals(5, $observer->testPublisher);
        
    }
    
    /**
     * @depends testAttachPublisher
     * 
     */
    public function testDebugInfo() {
        $publisher1 = new TestObserverPublisherClass();
        $observer = new TestObserverObserverClass();
        
        $observer->attachPublisher($publisher1);
        
        ob_start();
        
        var_dump($observer);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?observedSubjects\"?\]?.*=\\>\n.*array\\(1\\)/", $output);
    }
}
