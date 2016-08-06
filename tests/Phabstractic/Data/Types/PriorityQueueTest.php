<?php

require_once('src/Phabstractic/Data/Types/PriorityQueue.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractList.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractSortedList.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractRestrictedList.php');
require_once('src/Phabstractic/Data/Types/Resource/ListInterface.php');
require_once('src/Phabstractic/Data/Types/Priority.php');
require_once('src/Phabstractic/Data/Types/None.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Resource as TypesResource;
use Phabstractic\Features\Resource as FeaturesResource;

class PriorityQueueTest extends TestCase
{
    
    public function testEmptyInstantiation() {
        $queue = new Types\PriorityQueue();
        
        $this->assertInstanceOf(Types\PriorityQueue::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractSortedList::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $queue);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $queue);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $queue);
        
        $this->assertEquals(array(), $queue->getList());
        
    }
    
    public function testProperArrayInstantiation() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals(array($priority1, $priority2, $priority3, $priority4), $queue->getList());
        
        $this->assertInstanceOf(Types\PriorityQueue::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractSortedList::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $queue);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $queue);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $queue);
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperArrayInstantiation() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $list = new Types\PriorityQueue(array($priority1, $priority2, 'nope', 5));
        
        $this->assertInstanceOf(Types\PriorityQueue::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractSortedList::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $queue);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $queue);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $queue);
    }
    
    public function testProperAbstractListInstantiation() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue1 = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        $queue2 = new Types\PriorityQueue($queue1);
        
        $this->assertEquals(array($priority1, $priority2, $priority3, $priority4), $queue2->getList());
        
        $this->assertInstanceOf(Types\PriorityQueue::class, $queue2);
        $this->assertInstanceOf(TypesResource\AbstractSortedList::class, $queue2);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $queue2);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $queue2);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $queue2);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $queue2);
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperAbstractListInstantiation() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $list1 = new Types\Stack(array($priority1, $priority2, 'nope', 5));
        $queue = new Types\PriorityQueue($list1);
        
    }
    
    public function testProperScalarListInstantiation() {
        $priority = Types\Priority::buildPriority('test', 6);
        $queue = new Types\PriorityQueue($priority);
        
        $this->assertEquals(array($priority), $queue->getList());
        
        $this->assertInstanceOf(Types\PriorityQueue::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractSortedList::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $queue);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $queue);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $queue);
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperScalarListInstantiation() {
        $list = new Types\PriorityQueue(5);
        
    }
    
    public function testCount() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals(4, $queue->count());
    }
    
    public function testEmptyAndClear() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertFalse($queue->isEmpty());
        
        $queue->clear();
        
        $this->assertTrue($queue->isEmpty());
        
    }
    
    public function testRemove() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $queue->remove($priority2);
        
        $this->assertEquals(array($priority1, $priority3, $priority4), $queue->getList());
        
    }
    
    public function testDelete() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $queue->delete('third');
        
        $this->assertEquals(array($priority1, $priority2, $priority4), $queue->getList());
        
    }
    
    public function testDeletePriority() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 2);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $queue->deletePriority(2);
        
        $this->assertEquals(array($priority1, $priority4), $queue->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testBasicExchange() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 2);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $queue->exchange();
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testDuplicate() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 2);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $queue->duplicate();
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testRollForward() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 2);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $queue->roll(2);
        
    }
    
    public function testTopNotEmptyNoStrict() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals($priority1, $queue->top());
        
    }
    
    public function testTopEmptyNoStrict() {
        $queue = new Types\PriorityQueue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->top());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testTopEmptyStrict() {
        $queue = new Types\PriorityQueue(array(), null, array('strict' => true));
        
        $top = $queue->top();
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testBottomEmptyStrict() {
        $queue = new Types\PriorityQueue(array(), null, array('strict' => true));
        
        $top = $queue->bottom();
        
    }
    
    public function testBottomNotEmptyNoStrict() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals($priority4, $queue->bottom());
        
    }
    
    public function testBottomEmptyNoStrict() {
        $queue = new Types\PriorityQueue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->bottom());
        
    }
    
    public function testProperPushMultipleNoReference() {
        $queue = new Types\PriorityQueue(array());
        
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        
        $queue->push($priority4, $priority2, $priority3, $priority1);
        
        $this->assertEquals(array($priority1, $priority2, $priority3, $priority4), $queue->getList());
        
    }
    
    public function testImproperPushMultipleNoReferenceNoStrict() {
        $queue = new Types\PriorityQueue(array());
        
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        
        $queue->push($priority1, 'nope', 6, $priority2);
        
        $this->assertEquals(array(), $queue->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperPushMultipleNoReferenceWithStrict() {
        $queue = new Types\PriorityQueue(array(), null, array('strict' => true));
        
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        
        $queue->push($priority1, 'nope', 6, $priority2);
        
    }
    
    public function testPushSingularNoReference() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $priority5 = Types\Priority::buildPriority('fifth',7);
        
        $queue->push($priority5);
        
        $this->assertEquals(array($priority1, $priority2, $priority3, $priority4, $priority5), $queue->getList());
        
    }
    
    public function testPushSingularWithReference() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $priority5 = Types\Priority::buildPriority('fifth',7);
        
        $queue->pushReference($priority5);
        
        $this->assertEquals(array($priority1, $priority2, $priority3, $priority4, $priority5), $queue->getList());
        
        // set the priority elsewhere in the code
        $priority5->setPriority(1);
        
        // automatically sorts!
        $this->assertEquals(array($priority1, $priority5, $priority2, $priority3, $priority4), $queue->getList());
        
    }
    
    public function testImproperPushSingularWithReferenceNoStrict() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
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
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1), null, array('strict' => true));
        
        $testref = 6;
        
        $queue->pushReference($testref);
        
    }
    
    public function testPopNotEmptyNoStrict() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals('first', $queue->pop());
        $this->assertEquals(array($priority2, $priority3, $priority4), $queue->getList());
        
    }
    
    public function testPopEmptyNoStrict() {
        $queue = new Types\PriorityQueue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->pop());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testPopEmptyWithStrict() {
        $queue = new Types\PriorityQueue(array(), null, array('strict' => true));
        
        $p = $queue->pop();
        
    }
    
    public function testProperIndexEqual() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 2);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals(array('third', 'second'), $queue->index(2));
        
    }
    
    public function testProperIndexHigher() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 2);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals(array('fourth'), $queue->index(3, Types\PriorityQueue::HIGHER));
        
    }
    
    public function testProperIndexLower() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 2);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1));
        
        $this->assertEquals(array('first'), $queue->index(1, Types\PriorityQueue::LOWER));
        
    }
    
    public function testIndexOnEmptyListNoStrict() {
        $queue = new Types\PriorityQueue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->index(5)[0]);
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testIndexOnEmptyListWithStrict() {
        $queue = new Types\PriorityQueue(array(), null, array('strict' => true));
        
        $this->assertInstanceOf(Types\None::class, $queue->index(5));
        
    }
    
    public function testProperIndexRange() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 5);
        $priority5 = Types\Priority::buildPriority('fifth', 7);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1, $priority5));
        
        // minimum, maximum
        $range = new Types\Range(2, 5);
        
        $this->assertEquals(array('second', 'third', 'fourth'), $queue->indexRange($range));
        
    }
    
    public function testProperIndexWithReference() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 2);
        $priority5 = Types\Priority::buildPriority('fifth', 7);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1, $priority5));
        
        // returns array: second, fourth
        $testref = &$queue->indexReference(2);
        
        // make sure we target the intended value (fourth)
        if ($testref[0] == 'fourth') {
            $testref[0] = 'modified';
        } elseif ($testref[1] == 'fourth') {
            $testref[1] = 'modified';
        }
        
        $this->assertEquals(array('first','modified','second','third','fifth'),
                            $queue->index(0, Types\PriorityQueue::HIGHER));
        
    }
    
    public function testIndexWithReferenceOnEmptyListNoStrict() {
        $queue = new Types\PriorityQueue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->indexReference(5)[0]);
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testIndexWithReferenceOnEmptyListWithStrict() {
        $queue = new Types\PriorityQueue(array(), null, array('strict' => true));

        $i = $queue->indexReference(5);
        
    }
    
    public function testRetrievePriorityExistingData() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 2);
        $priority5 = Types\Priority::buildPriority('fifth', 7);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1, $priority5));
        
        $priority = &$queue->retrievePriority($priority2->getData());
        $priority->setPriority(5);
        
        $this->assertEquals(array($priority1, $priority4, $priority3, $priority2, $priority5), $queue->getList());
    }
    
    public function testRetrievePriorityNoneExistingData() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 2);
        $priority5 = Types\Priority::buildPriority('fifth', 7);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1, $priority5));
        
        $priority = &$queue->retrievePriority('six');
        
        $this->assertInstanceOf(Types\None::class, $priority);
        
    }
    
    public function testDebugInfo() {
        $priority1 = Types\Priority::buildPriority('first', 0);
        $priority2 = Types\Priority::buildPriority('second', 2);
        $priority3 = Types\Priority::buildPriority('third', 4);
        $priority4 = Types\Priority::buildPriority('fourth', 2);
        $priority5 = Types\Priority::buildPriority('fifth', 7);
        $queue = new Types\PriorityQueue(array($priority4, $priority2, $priority3, $priority1, $priority5));
        
        ob_start();
        
        var_dump($queue);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?list\"?\]?.*=\\>\n.*array\\(5\\)/", $output);
    }
    
}