<?php
require_once('src/Phabstractic/Patterns/Resource/ObserverInterface.php');
require_once('src/Phabstractic/Patterns/Resource/PublisherInterface.php');
require_once('src/Phabstractic/Patterns/PublisherTrait.php');
require_once('src/Phabstractic/Event/HandlerPriorityQueue.php');
require_once('src/Phabstractic/Event/Handler.php');
require_once('src/Phabstractic/Event/GenericEvent.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractSortedList.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractRestrictedList.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractList.php');
require_once('src/Phabstractic/Data/Types/Resource/ListInterface.php');
require_once('src/Phabstractic/Data/Types/Priority.php');
require_once('src/Phabstractic/Data/Types/PriorityQueue.php');


use PHPUnit\Framework\TestCase;
use Phabstractic\Patterns\Resource as PatternsResource;
use Phabstractic\Patterns;
use Phabstractic\Event;
use Phabstractic\Data\Types\Resource as TypesResource;
use Phabstractic\Data\Types;

class TestHandlerPriorityQueuePublisherClass implements PatternsResource\PublisherInterface {
    use Patterns\PublisherTrait;
    
}

class TestHandlerPriorityQueueTestClass {
    public $counter = array();
    
    public function method1() {
        $this->counter[] = 1;
    }
    
    public function method2() {
        $this->counter[] = 2;
    }
    
    public function method3() {
        $this->counter[] = 3;
    }
    
    public function method4() {
        $this->counter[] = 4;
    }
    
    public function method5() {
        $this->counter[] = 5;
    }
}

class HandlerPriorityQueueTest extends TestCase
{
    public function testEmptyInstantiation() {
        $queue = new Event\HandlerPriorityQueue();
        
        $this->assertInstanceOf(Types\PriorityQueue::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractSortedList::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $queue);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $queue);
        $this->assertInstanceOf(PatternsResource\ObserverInterface::class, $queue);
        
        $this->assertEquals(array(), $queue->getList());
        
    }
    
    public function testProperArrayInstantiation() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals(array($priority1, $priority2, $priority3, $priority4), $queue->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperArrayInstantiation() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $list = new Event\HandlerPriorityQueue(array($priority1, $priority2, 'nope', 5));
        
    }
    
    public function testProperAbstractListInstantiation() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue1 = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        $queue2 = new Event\HandlerPriorityQueue($queue1);
        
        $this->assertEquals(array($priority1, $priority2, $priority3, $priority4), $queue2->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperAbstractListInstantiation() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $list1 = new Types\Stack(array($priority1, $priority2, 'nope', 5));
        $queue = new Event\HandlerPriorityQueue($list1);
        
    }
    
    public function testProperScalarListInstantiation() {
        $priority = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $queue = new Event\HandlerPriorityQueue($priority);
        
        $this->assertEquals(array($priority), $queue->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperScalarListInstantiation() {
        $queue = new Event\HandlerPriorityQueue(5);
        
    }
    
    public function testCount() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals(4, $queue->count());
    }
    
    public function testEmptyAndClear() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertFalse($queue->isEmpty());
        
        $queue->clear();
        
        $this->assertTrue($queue->isEmpty());
        
    }
    
    public function testRemove() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $queue->remove($priority2);
        
        $this->assertEquals(array($priority1, $priority3, $priority4), $queue->getList());
        
    }
    
    public function testDelete() {
        $handler3 = Event\Handler::buildFromClosure(function(){return 3;});
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            $handler3, 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $queue->delete($handler3);
        
        $this->assertEquals(array($priority1, $priority2, $priority4), $queue->getList());
        
    }
    
    public function testDeletePriority() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 2);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $queue->deletePriority(2);
        
        $this->assertEquals(array($priority1, $priority4), $queue->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testBasicExchange() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $queue->exchange();
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testDuplicate() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $queue->duplicate();
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testRollForward() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $queue->roll(2);
        
    }
    
    public function testTopNotEmptyNoStrict() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals($priority1, $queue->top());
        
    }
    
    public function testTopEmptyNoStrict() {
        $queue = new Event\HandlerPriorityQueue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->top());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testTopEmptyStrict() {
        $queue = new Event\HandlerPriorityQueue(array(), null, array('strict' => true));
        
        $top = $queue->top();
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testBottomEmptyStrict() {
        $queue = new Event\HandlerPriorityQueue(array(), null, array('strict' => true));
        
        $top = $queue->bottom();
        
    }
    
    public function testBottomNotEmptyNoStrict() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals($priority4, $queue->bottom());
        
    }
    
    public function testBottomEmptyNoStrict() {
        $queue = new Event\HandlerPriorityQueue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->bottom());
        
    }
    
    public function testProperPushMultipleNoReference() {
        $queue = new Event\HandlerPriorityQueue(array());
        
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        
        $queue->push($priority4, $priority2, $priority3, $priority1);
        
        $this->assertEquals(array($priority1, $priority2, $priority3, $priority4), $queue->getList());
        
    }
    
    public function testImproperPushMultipleNoReferenceNoStrict() {
        $queue = new Event\HandlerPriorityQueue(array());
        
       $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        
        $queue->push($priority1, 'nope', 6, $priority2);
        
        $this->assertEquals(array(), $queue->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperPushMultipleNoReferenceWithStrict() {
        $queue = new Event\HandlerPriorityQueue(array(), null, array('strict' => true));
        
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        
        $queue->push($priority1, 'nope', 6, $priority2);
        
    }
    
    public function testPushSingularNoReference() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $priority5 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 5;}), 5);
        
        $queue->push($priority5);
        
        $this->assertEquals(array($priority1, $priority2, $priority3, $priority4, $priority5), $queue->getList());
        
    }
    
    public function testPushSingularWithReference() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 0);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $priority5 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 5;}), 5);
        
        $queue->pushReference($priority5);
        
        $this->assertEquals(array($priority1, $priority2, $priority3, $priority4, $priority5), $queue->getList());
        
        // set the priority elsewhere in the code
        $priority5->setPriority(1);
        
        // automatically sorts!
        $this->assertEquals(array($priority1, $priority5, $priority2, $priority3, $priority4), $queue->getList());
        
    }
    
    public function testImproperPushSingularWithReferenceNoStrict() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 1);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $testref = 6;
        
        $queue->pushReference($testref);
        
        $testref = 'modified';
        
        $this->assertEquals(array($priority1, $priority2, $priority3, $priority4), $queue->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperPushSingularWithReferenceWithStrict() {
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 0);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1), null, array('strict' => true));
        
        $testref = 6;
        
        $queue->pushReference($testref);
        
    }
    
    public function testPopNotEmptyNoStrict() {
        $handler1 = Event\Handler::buildFromClosure(function(){return 1;});
        $priority1 = Types\Priority::buildPriority($handler1, 0);
        $priority2 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 2;}), 2);
        $priority3 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 3;}), 3);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals($handler1, $queue->pop());
        $this->assertEquals(array($priority2, $priority3, $priority4), $queue->getList());
        
    }
    
    public function testPopEmptyNoStrict() {
        $queue = new Event\HandlerPriorityQueue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->pop());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testPopEmptyWithStrict() {
        $queue = new Event\HandlerPriorityQueue(array(), null, array('strict' => true));
        
        $p = $queue->pop();
        
    }
    
    public function testProperIndexEqual() {
        $handler2 = Event\Handler::buildFromClosure(function(){return 2;});
        $handler3 = Event\Handler::buildFromClosure(function(){return 3;});
        $priority1 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 1;}), 0);
        $priority2 = Types\Priority::buildPriority($handler2, 2);
        $priority3 = Types\Priority::buildPriority($handler3, 2);
        $priority4 = Types\Priority::buildPriority(
            Event\Handler::buildFromClosure(function(){return 4;}), 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals(array($handler3, $handler2), $queue->index(2));
        
    }
    
    public function testProperIndexHigher() {
        $handler1 = Event\Handler::buildFromClosure(function(){return 1;});
        $handler2 = Event\Handler::buildFromClosure(function(){return 2;});
        $handler3 = Event\Handler::buildFromClosure(function(){return 3;});
        $handler4 = Event\Handler::buildFromClosure(function(){return 4;});
        $priority1 = Types\Priority::buildPriority($handler1, 0);
        $priority2 = Types\Priority::buildPriority($handler2, 2);
        $priority3 = Types\Priority::buildPriority($handler3, 3);
        $priority4 = Types\Priority::buildPriority($handler4, 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals(array($handler3, $handler4), $queue->index(3, Types\PriorityQueue::HIGHER));
        
    }
    
    public function testProperIndexLower() {
        $handler1 = Event\Handler::buildFromClosure(function(){return 1;});
        $handler2 = Event\Handler::buildFromClosure(function(){return 2;});
        $handler3 = Event\Handler::buildFromClosure(function(){return 3;});
        $handler4 = Event\Handler::buildFromClosure(function(){return 4;});
        $priority1 = Types\Priority::buildPriority($handler1, 0);
        $priority2 = Types\Priority::buildPriority($handler2, 2);
        $priority3 = Types\Priority::buildPriority($handler3, 3);
        $priority4 = Types\Priority::buildPriority($handler4, 4);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals(array($handler1), $queue->index(1, Types\PriorityQueue::LOWER));
        
    }
    
    public function testIndexOnEmptyListNoStrict() {
        $queue = new Event\HandlerPriorityQueue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->index(5)[0]);
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testIndexOnEmptyListWithStrict() {
        $queue = new Event\HandlerPriorityQueue(array(), null, array('strict' => true));
        
        $this->assertInstanceOf(Types\None::class, $queue->index(5));
        
    }
    
    public function testProperIndexRange() {
        $handler1 = Event\Handler::buildFromClosure(function(){return 1;});
        $handler2 = Event\Handler::buildFromClosure(function(){return 2;});
        $handler3 = Event\Handler::buildFromClosure(function(){return 3;});
        $handler4 = Event\Handler::buildFromClosure(function(){return 4;});
        $handler5 = Event\Handler::buildFromClosure(function(){return 5;});
        $priority1 = Types\Priority::buildPriority($handler1, 0);
        $priority2 = Types\Priority::buildPriority($handler2, 2);
        $priority3 = Types\Priority::buildPriority($handler3, 3);
        $priority4 = Types\Priority::buildPriority($handler4, 4);
        $priority5 = Types\Priority::buildPriority($handler5, 6);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1, $priority5));
        
        // minimum, maximum
        $range = new Types\Range(2, 5);
        
        $this->assertEquals(array($handler2, $handler3, $handler4), $queue->indexRange($range));
        
    }
    
    public function testProperIndexWithReference() {
        $handler1 = Event\Handler::buildFromClosure(function(){return 1;});
        $handler2 = Event\Handler::buildFromClosure(function(){return 2;});
        $handler3 = Event\Handler::buildFromClosure(function(){return 3;});
        $handler4 = Event\Handler::buildFromClosure(function(){return 4;});
        $handler5 = Event\Handler::buildFromClosure(function(){return 5;});
        $handler6 = Event\Handler::buildFromClosure(function(){return 6;});
        $priority1 = Types\Priority::buildPriority($handler1, 0);
        $priority2 = Types\Priority::buildPriority($handler2, 2);
        $priority3 = Types\Priority::buildPriority($handler3, 3);
        $priority4 = Types\Priority::buildPriority($handler4, 2);
        $priority5 = Types\Priority::buildPriority($handler5, 6);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1, $priority5));
        
        // returns array: second, fourth
        $testref = &$queue->indexReference(2);
        
        // make sure we target the intended value (fourth)
        if ($testref[0] == $handler4) {
            $testref[0] = $handler6;
        } elseif ($testref[1] == $handler4) {
            $testref[1] = $handler6;
        }
        
        $this->assertEquals(array($handler1, $handler2, $handler6, $handler3, $handler5),
                            $queue->index(0, Types\PriorityQueue::HIGHER));
        
    }
    
    public function testIndexWithReferenceOnEmptyListNoStrict() {
        $queue = new Event\HandlerPriorityQueue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->indexReference(5)[0]);
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testIndexWithReferenceOnEmptyListWithStrict() {
        $queue = new Event\HandlerPriorityQueue(array(), null, array('strict' => true));

        $i = $queue->indexReference(5);
        
    }
    
    public function testRetrievePriorityExistingData() {
        $handler1 = Event\Handler::buildFromClosure(function(){return 1;});
        $handler2 = Event\Handler::buildFromClosure(function(){return 2;});
        $handler3 = Event\Handler::buildFromClosure(function(){return 3;});
        $handler4 = Event\Handler::buildFromClosure(function(){return 4;});
        $handler5 = Event\Handler::buildFromClosure(function(){return 5;});
        $priority1 = Types\Priority::buildPriority($handler1, 0);
        $priority2 = Types\Priority::buildPriority($handler2, 2);
        $priority3 = Types\Priority::buildPriority($handler3, 3);
        $priority4 = Types\Priority::buildPriority($handler4, 4);
        $priority5 = Types\Priority::buildPriority($handler5, 6);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1, $priority5));
        
        $priority = &$queue->retrievePriority($priority2->getData());
        $priority->setPriority(5);
        
        $this->assertEquals(array($priority1, $priority3, $priority4, $priority2, $priority5), $queue->getList());
    }
    
    public function testRetrievePriorityNoneExistingData() {
        $handler1 = Event\Handler::buildFromClosure(function(){return 1;});
        $handler2 = Event\Handler::buildFromClosure(function(){return 2;});
        $handler3 = Event\Handler::buildFromClosure(function(){return 3;});
        $handler4 = Event\Handler::buildFromClosure(function(){return 4;});
        $handler5 = Event\Handler::buildFromClosure(function(){return 5;});
        $handler6 = Event\Handler::buildFromClosure(function(){return 6;});
        $priority1 = Types\Priority::buildPriority($handler1, 0);
        $priority2 = Types\Priority::buildPriority($handler2, 2);
        $priority3 = Types\Priority::buildPriority($handler3, 3);
        $priority4 = Types\Priority::buildPriority($handler4, 4);
        $priority5 = Types\Priority::buildPriority($handler5, 6);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1, $priority5));
        
        $priority = &$queue->retrievePriority($handler6);
        
        $this->assertInstanceOf(Types\None::class, $priority);
        
    }
    
    public function testAttachPublisher() {
        $publisher1 = new TestHandlerPriorityQueuePublisherClass();
        $publisher2 = new TestHandlerPriorityQueuePublisherClass();
        $queue = new Event\HandlerPriorityQueue();
        
        $queue->attachPublisher($publisher1);
        $queue->attachPublisher($publisher2);
        
        $this->assertEquals(array($publisher1, $publisher2), $queue->getPublishers());
    }
    
    /**
     * @depends testAttachPublisher
     * 
     */
    public function testDetachPublisher() {
        $publisher1 = new TestHandlerPriorityQueuePublisherClass();
        $publisher2 = new TestHandlerPriorityQueuePublisherClass();
        $publisher3 = new TestHandlerPriorityQueuePublisherClass();
        $queue = new Event\HandlerPriorityQueue();
        
        $queue->attachPublisher($publisher1);
        $queue->attachPublisher($publisher2);
        $queue->attachPublisher($publisher3);
        
        $this->assertEquals(array($publisher1, $publisher2, $publisher3), $queue->getPublishers());
        
        $queue->detachPublisher($publisher2);
        
        $this->assertEquals(array($publisher1, $publisher3), $queue->getPublishers());
    }
    
    /**
     * @depends testAttachPublisher
     * 
     */
    public function testUnlinkFromPublishers() {
        $publisher1 = new TestHandlerPriorityQueuePublisherClass();
        $publisher2 = new TestHandlerPriorityQueuePublisherClass();
        $publisher3 = new TestHandlerPriorityQueuePublisherClass();
        $queue = new Event\HandlerPriorityQueue();
        
        $queue->attachPublisher($publisher1);
        $queue->attachPublisher($publisher2);
        $queue->attachPublisher($publisher3);
        
        $this->assertEquals(array($publisher1, $publisher2, $publisher3), $queue->getPublishers());
        
        $queue->unlinkFromPublishers();
        
        $this->assertEquals(array(), $queue->getPublishers());
    }
    
    public function testPropagation() {
        $testobject = new TestHandlerPriorityQueueTestClass();
        $handler1 = Event\Handler::buildFromArray(array($testobject, 'method1'));
        $handler2 = Event\Handler::buildFromArray(array($testobject, 'method2'));
        $handler3 = Event\Handler::buildFromArray(array($testobject, 'method3'));
        $handler4 = Event\Handler::buildFromArray(array($testobject, 'method4'));
        $handler5 = Event\Handler::buildFromArray(array($testobject, 'method5'));
        $priority1 = Types\Priority::buildPriority($handler1, 0);
        $priority2 = Types\Priority::buildPriority($handler2, 2);
        $priority3 = Types\Priority::buildPriority($handler3, 3);
        $priority4 = Types\Priority::buildPriority($handler4, 4);
        $priority5 = Types\Priority::buildPriority($handler5, 6);
        $queue = new Event\HandlerPriorityQueue();
        $queue->pushReference($priority1);
        $queue->pushReference($priority2);
        $queue->pushReference($priority3);
        $queue->pushReference($priority4);
        $queue->pushReference($priority5);
        
        $event = new Event\GenericEvent(null, __FUNCTION__, __CLASS__, __NAMESPACE__, null);
        $publisher = new TestHandlerPriorityQueuePublisherClass();
        
        $queue->notifyObserver($publisher, $event);
        
        $this->assertEquals(array(1,2,3,4,5), $testobject->counter);
        
        $testobject->counter = array();
        
        $priority1->setPriority(7);
        $priority2->setPriority(5);
        
        $queue->notifyObserver($publisher, $event);
        
        $this->assertEquals(array(3,4,2,5,1), $testobject->counter);
        
    }
    
    public function testDebugInfo() {
        $handler1 = Event\Handler::buildFromClosure(function(){return 1;});
        $handler2 = Event\Handler::buildFromClosure(function(){return 2;});
        $handler3 = Event\Handler::buildFromClosure(function(){return 3;});
        $handler4 = Event\Handler::buildFromClosure(function(){return 4;});
        $handler5 = Event\Handler::buildFromClosure(function(){return 5;});
        $handler6 = Event\Handler::buildFromClosure(function(){return 6;});
        $priority1 = Types\Priority::buildPriority($handler1, 0);
        $priority2 = Types\Priority::buildPriority($handler2, 2);
        $priority3 = Types\Priority::buildPriority($handler3, 3);
        $priority4 = Types\Priority::buildPriority($handler4, 4);
        $priority5 = Types\Priority::buildPriority($handler5, 6);
        $queue = new Event\HandlerPriorityQueue(array($priority4, $priority2, $priority3, $priority1, $priority5));
        
        ob_start();
        
        var_dump($queue);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?list\"?\]?.*=\\>\n.*array\\(5\\)/", $output);
        $this->assertRegExp("/\\[?\"?observedSubjects\"?\]?.*=\\>\n.*array\\(0\\)/", $output);
    }
    
}