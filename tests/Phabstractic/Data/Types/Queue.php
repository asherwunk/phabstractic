<?php

require_once('src/Phabstractic/Data/Types/Queue.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractList.php');
require_once('src/Phabstractic/Data/Types/Resource/ListInterface.php');
require_once('src/Phabstractic/Data/Types/None.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Resource as TypesResource;

class QueueTest extends TestCase
{
    public function testEmptyInstantiation() {
        $queue = new Types\Queue();
        
        $this->assertInstanceOf(Types\Queue::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $queue);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $queue);
        
        $this->assertEquals(array(), $queue->getList());
        
    }
    
    public function testArrayInstantiation() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $this->assertEquals(array(1,2,3,4,5), $queue->getList());
        
    }
    
    public function testAbstractListInstantiation() {
        $queue1 = new Types\Queue(array(1,2,3,4,5));
        $queue2 = new Types\Queue($queue1);
        
        $this->assertEquals(array(1,2,3,4,5), $queue2->getList());
        
    }
    
    public function testScalarListInstantiation() {
        $queue = new Types\Queue('thisisascalar');
        
        $this->assertEquals(array('thisisascalar'), $queue->getList());
        
    }
    
    public function testGetQueue() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $this->assertEquals(array(1,2,3,4,5), $queue->getQueue());
    }
    
    public function testCount() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $this->assertEquals(5, $queue->count());
    }
    
    public function testEmptyAndClear() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $this->assertFalse($queue->isEmpty());
        
        $queue->clear();
        
        $this->assertTrue($queue->isEmpty());
        
    }
    
    public function testRemove() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $queue->remove(4);
        
        $this->assertEquals(array(1,2,3,5), $queue->getList());
        
    }
    
    public function testBasicExchange() {
        $queue = new Types\Queue(array(1,'test1','test2'));
        
        $queue->exchange();
        
        $this->assertEquals(array('test1', 1, 'test2'), $queue->getList());
    }
    
    public function testReferencedExchange() {
        $testref1 = 'testref1';
        $testref2 = 'testref2';
        
        $queue = new Types\Queue(array(1,&$testref1,&$testref2));
        
        $queue->exchange();
        
        $testref1 = 'modified';
        
        $this->assertEquals(array('modified',1,'testref2'), $queue->getList());
        
    }
    
    public function testDuplicate() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $queue->duplicate();
        
        $this->assertEquals(array(1,1,2,3,4,5), $queue->getList());
        
    }
    
    public function testTopNotEmptyNoStrict() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $this->assertEquals(1, $queue->top());
        
    }
    
    public function testTopEmptyNoStrict() {
        $queue = new Types\Queue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->top());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testBottomEmptyStrict() {
        $queue = new Types\Queue(array(), array('strict' => true));
        
        $top = $queue->bottom();
        
    }
    
    public function testBottomNotEmptyNoStrict() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $this->assertEquals(5, $queue->bottom());
        
    }
    
    public function testBottomEmptyNoStrict() {
        $queue = new Types\Queue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->bottom());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testTopEmptyStrict() {
        $queue = new Types\Queue(array(), array('strict' => true));
        
        $top = $queue->top();
        
    }
    
    public function testPushMultipleNoReference() {
        $queue = new Types\Queue();
        
        $queue->push(1, 2, 3, 4, 5);
        
        $this->assertEquals(array(1,2,3,4,5), $queue->getList());
    }
    
    public function testPushSingularNoReference() {
        $queue = new Types\Queue(array(1,2,3,4));
        
        $queue->push(5);
        
        $this->assertEquals(array(1,2,3,4,5), $queue->getList());
    }
    
    public function testPushSingularWithReference() {
        $testref = 'testref';
        
        $queue = new Types\Queue(array(1,2,3,4));
        
        $queue->pushReference($testref);
        
        $testref = 'modified';
        
        $this->assertEquals(array(1,2,3,4,'modified'), $queue->getList());
        
    }
    
    public function testPopNotEmptyNoStrict() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $this->assertEquals(1, $queue->pop());
        $this->assertEquals(array(2,3,4,5), $queue->getList());
        
    }
    
    public function testPopEmptyNoStrict() {
        $queue = new Types\Queue();
        
        $this->assertInstanceOf(Types\None::class, $queue->pop());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testPopEmptyWithStrict() {
        $queue = new Types\Queue(array(), array('strict' => true));
        
        $p = $queue->pop();
        
    }
    
    public function testProperIndex() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $this->assertEquals(3, $queue->index(2));
        $this->assertEquals(5, $queue->index(4));
        $this->assertEquals(1, $queue->index(0));
        
    }
    
    public function testImproperIndexNoStrict() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $this->assertInstanceOf(Types\None::class, $queue->index(7));
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperIndexWithStrict() {
        $queue = new Types\Queue(array(1,2,3,4,5), array('strict' => true));
        
        $i = $queue->index(7);
    }
    
    public function testProperIndexWithReference() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $testref = &$queue->indexReference(1);
        $testref = 7;
        
        $this->assertEquals(array(1,7,3,4,5), $queue->getList());
        
    }
    
    public function testImproperIndexWithReference() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $testref = &$queue->indexReference(7);
        $testref = 7;
        
        $this->assertEquals(array(1,2,3,4,5), $queue->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperIndexWithReferenceWithStrict() {
        $queue = new Types\Queue(array(1,2,3,4,5), array('strict' => true));
        
        $testref = &$queue->indexReference(7);
        
    }
    
    public function testRollForward() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $queue->roll(2);
        
        $this->assertEquals(array(3,4,5,1,2), $queue->getList());
        
        $queue->roll(-3);
        
        $this->assertEquals(array(5,1,2,3,4), $queue->getlist());
        
    }
    
    public function testArrayAccessProperOffsetSet() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $queue[1] = 6;
        
        $this->assertEquals(array(1,6,3,4,5), $queue->getList());
    }
    
    public function testArrayAccessImproperOffsetSetNoStrict() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $queue[8] = 6;
        
        $this->assertEquals(array(1,2,3,4,5), $queue->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testArrayAccessImproperOffsetSetWithStrict() {
        $queue = new Types\Queue(array(1,2,3,4,5), array('strict' => true));
        
        $queue[8] = 6;
    }
    
    public function testArrayAccessOffsetSetPush() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $queue[] = 6;
        
        $this->assertEquals(array(1,2,3,4,5,6), $queue->getList());
    }
    
    public function testArrayAccessProperOffsetGet() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $this->assertEquals(2, $queue[1]);
        
    }
    
    public function testArrayAccessImproperOffsetGetNoStrict() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $this->assertInstanceOf(Types\None::class, $queue[8]);
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testArrayAccessImproperOffsetGetWithStrict() {
        $queue = new Types\Queue(array(1,2,3,4,5), array('strict' => true));
        
        $i = $queue[8];
        
    }
    
    public function testArrayAccessOffsetUnset() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        unset($queue[1]);
        
        $this->assertEquals(array(1,3,4,5), $queue->getList());
        
    }
    
    public function testArrayAccessOffsetExists() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        $this->assertTrue(isset($queue[1]));
        $this->assertFalse(isset($queue[8]));
        
    }
    
    public function testSetDebugInfo() {
        $queue = new Types\Queue(array(1,2,3,4,5));
        
        ob_start();
        
        var_dump($queue);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?list\"?\]?.*=\\>\n.*array\\(5\\)/", $output);

    }
}
