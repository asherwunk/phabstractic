<?php
require_once('src/Phabstractic/Patterns/Resource/ObserverInterface.php');
require_once('src/Phabstractic/Patterns/Resource/PublisherInterface.php');
require_once('src/Phabstractic/Patterns/PublisherTrait.php');
require_once('src/Phabstractic/Event/Filter.php');
require_once('src/Phabstractic/Event/Handler.php');
require_once('src/Phabstractic/Event/Actor.php');
require_once('src/Phabstractic/Event/GenericEvent.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Patterns\Resource as PatternsResource;
use Phabstractic\Patterns;
use Phabstractic\Event;

class TestActorPublisherClass implements PatternsResource\PublisherInterface {
    use Patterns\PublisherTrait;
    
}

class ActorTest extends TestCase
{
    public function testBasicInstantiation() {
        $actor = new Event\Actor();
        
        $this->assertInstanceOf(Phabstractic\Event\Actor::class, $actor);
    }
    
    public function testProperInstantiation() {
        $filter = new Event\Filter();
        $handler = new Event\Handler();
        $actor = new Event\Actor($filter, $handler);
        
        $this->assertInstanceOf(Phabstractic\Event\Actor::class, $actor);
    }
    
    public function testNotifyObserver() {
        $testvar = 0;
        
        $publisher = new TestActorPublisherClass();
        
        $filter = new Event\Filter();
        $filter->setTags(array('tag1', 'tag2'));
        $filter->loosenUp();
        
        $handler = Event\Handler::buildFromClosure(function() use (&$testvar) {$testvar = 1; return true;});
        
        $actor = new Event\Actor($filter, $handler);
        
        $actor->attachPublisher($publisher);
        
        $event = new Event\GenericEvent(null, __FUNCTION__, __CLASS__, __NAMESPACE__, null, array('tag1'));
        
        $publisher->setStateObject($event);
        
        $this->assertEquals(1, $testvar);
        
        $testvar = 0;
        
        $filter->setStateWithEvent($event);
        $filter->addTag('tag2');
        $filter->makeStrict();
        $actor->setFilter($filter);
        
        $publisher->setStateObject($event);
        
        $this->assertEquals(0, $testvar);
        
        $event->addTag('tag2');
        
        $publisher->setStateObject($event);
        
        $this->assertEquals(1, $testvar);
        
    }
    
    public function testAttachPublisher() {
        $publisher1 = new TestActorPublisherClass();
        $publisher2 = new TestActorPublisherClass();
        $actor = new Event\Actor();
        
        $actor->attachPublisher($publisher1);
        $actor->attachPublisher($publisher2);
        
        $this->assertEquals(array($publisher1, $publisher2), $actor->getPublishers());
    }
    
    /**
     * @depends testAttachPublisher
     * 
     */
    public function testDetachPublisher() {
        $publisher1 = new TestActorPublisherClass();
        $publisher2 = new TestActorPublisherClass();
        $publisher3 = new TestActorPublisherClass();
        $actor = new Event\Actor();
        
        $actor->attachPublisher($publisher1);
        $actor->attachPublisher($publisher2);
        $actor->attachPublisher($publisher3);
        
        $this->assertEquals(array($publisher1, $publisher2, $publisher3), $actor->getPublishers());
        
        $actor->detachPublisher($publisher2);
        
        $this->assertEquals(array($publisher1, $publisher3), $actor->getPublishers());
    }
    
    /**
     * @depends testAttachPublisher
     * 
     */
    public function testUnlinkFromPublishers() {
        $publisher1 = new TestActorPublisherClass();
        $publisher2 = new TestActorPublisherClass();
        $publisher3 = new TestActorPublisherClass();
        $actor = new Event\Actor();
        
        $actor->attachPublisher($publisher1);
        $actor->attachPublisher($publisher2);
        $actor->attachPublisher($publisher3);
        
        $this->assertEquals(array($publisher1, $publisher2, $publisher3), $actor->getPublishers());
        
        $actor->unlinkFromPublishers();
        
        $this->assertEquals(array(), $actor->getPublishers());
    }
    
    public function testDebugInfo() {
        $filter = new Event\Filter();
        $handler = new Event\Handler();
        $actor = new Event\Actor($filter, $handler);
        
        ob_start();
        
        var_dump($actor);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?filter\"?\]?.*=\\>/", $output);
        $this->assertRegExp("/\\[?\"?handler\"?\]?.*=\\>/", $output);
    }
    
}