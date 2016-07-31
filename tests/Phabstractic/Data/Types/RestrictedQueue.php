<?php

require_once('src/Phabstractic/Data/Types/RestrictedQueue.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractList.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractRestrictedList.php');
require_once('src/Phabstractic/Data/Types/Resource/ListInterface.php');
require_once('src/Phabstractic/Data/Types/Restrictions.php');
require_once('src/Phabstractic/Data/Types/Type.php');
require_once('src/Phabstractic/Data/Types/None.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Resource as TypesResource;
use Phabstractic\Data\Types\Type;
use Phabstractic\Features\Resource as FeaturesResource;

class TestRestrictedQueueClass {
    
}

class RestrictedQueueTest extends TestCase
{
    public function testEmptyInstantiation() {
        $queue = new Types\RestrictedQueue();
        
        $this->assertInstanceOf(Types\RestrictedList::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $queue);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $queue);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $queue);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $queue);
        
        $this->assertEquals(array(), $queue->getList());
        
    }
    
    public function testProperArrayInstantiation() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(array(1,2,3,4,5), $queue->getList());
        
        $this->assertInstanceOf(Types\RestrictedList::class, $queue);
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
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,'five'), $restrictions);
        
    }
    
    public function testProperAbstractListInstantiation() {
        $queue1 = new Types\Queue(array(1,2,3,4,5));
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue2 = new Types\RestrictedStack($queue1, $restrictions);
        
        $this->assertEquals(array(1,2,3,4,5), $queue2->getList());
        
        $this->assertInstanceOf(Types\RestrictedList::class, $queue2);
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
        $queue1 = new Types\Queue(array(1,2,3,4,'five'));
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue2 = new Types\RestrictedQueue($queue1, $restrictions);
        
    }
    
    public function testProperScalarListInstantiation() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_STRING));
        $queue = new Types\RestrictedQueue('thisisascalar', $restrictions);
        
        $this->assertEquals(array('thisisascalar'), $queue->getList());
        
        $this->assertInstanceOf(Types\RestrictedList::class, $queue);
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
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue('thisisascalar', $restrictions);
        
    }
    
    public function testCount() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(5, $queue->count());
    }
    
    public function testEmptyAndClear() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $this->assertFalse($queue->isEmpty());
        
        $queue->clear();
        
        $this->assertTrue($queue->isEmpty());
        
    }
    
    public function testRemove() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $queue->remove(4);
        
        $this->assertEquals(array(1,2,3,5), $queue->getList());
        
    }
    
    public function testBasicExchange() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $queue->exchange();
        
        $this->assertEquals(array(2,1,3,4,5), $queue->getList());
    }
    
    public function testReferencedExchange() {
        $testref1 = 'testref1';
        $testref2 = 'testref2';
        
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT, Type::BASIC_STRING));
        $queue = new Types\RestrictedQueue(array(&$testref1,&$testref2,1,2), $restrictions);
        
        $queue->exchange();
        
        $testref2 = 'modified';
        
        $this->assertEquals(array('modified','testref1',1,2), $queue->getList());
        
    }
    
    public function testDuplicate() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $queue->duplicate();
        
        $this->assertEquals(array(1,1,2,3,4,5), $queue->getList());
        
    }
    
    public function testTopNotEmptyNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(1, $queue->top());
        
    }
    
    public function testTopEmptyNoStrict() {
        $queue = new Types\RestrictedQueue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->top());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testTopEmptyStrict() {
        $queue = new Types\RestrictedQueue(array(), null, array('strict' => true));
        
        $top = $queue->top();
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testBottomEmptyStrict() {
        $queue = new Types\RestrictedQueue(array(), null, array('strict' => true));
        
        $top = $queue->bottom();
        
    }
    
    public function testBottomNotEmptyNoStrict() {
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5));
        
        $this->assertEquals(5, $queue->bottom());
        
    }
    
    public function testBottomEmptyNoStrict() {
        $queue = new Types\RestrictedQueue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->bottom());
        
    }
    
    public function testProperPushMultipleNoReference() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(), $restrictions);
        
        $queue->push(1, 2, 3, 4, 5);
        
        $this->assertEquals(array(1,2,3,4,5), $queue->getList());
    }
    
    public function testImproperPushMultipleNoReferenceNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(), $restrictions);
        
        $queue->push(1, 2, 3, 4, 'test');
        
        $this->assertEquals(array(), $queue->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperPushMultipleNoReferenceWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(), $restrictions, array('strict' => true));
        
        $queue->push(1, 2, 3, 4, 'test');
    }
    
    public function testPushSingularNoReference() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4), $restrictions, array('strict' => true));
        
        $queue->push(5);
        
        $this->assertEquals(array(1,2,3,4,5), $queue->getList());
    }
    
    public function testPushSingularWithReference() {
        $testref = 'testref';
        
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT, Type::BASIC_STRING));
        $queue = new Types\RestrictedQueue(array(1,2,3,4), $restrictions, array('strict' => true));
        
        $queue->pushReference($testref);
        
        $testref = 'modified';
        
        $this->assertEquals(array(1,2,3,4,'modified'), $queue->getList());
        
    }
    
    public function testPushSingularNoReferenceTypedObject() {
        $restrictions = new Types\Restrictions(array(Type::TYPED_OBJECT),array('TestRestrictedStackClass'));
        $queue = new Types\RestrictedQueue(array(), $restrictions, array('strict' => true));
        
        $t = new TestRestrictedStackClass();
        
        $queue->push($t);
        
        $this->assertEquals(array($t), $queue->getList());
    }
    
    public function testImproperPushSingularWithReferenceNoStrict() {
        $testref = 'testref';
        
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4), $restrictions);
        
        $queue->pushReference($testref);
        
        $testref = 'modified';
        
        $this->assertEquals(array(1,2,3,4), $queue->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperPushSingularWithReferenceWithStrict() {
        $testref = 'testref';
        
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4), $restrictions, array('strict' => true));
        
        $queue->pushReference($testref);
        
        $testref = 'modified';
        
        $this->assertEquals(array(1,2,3,4), $queue->getList());
        
    }
    
    public function testPopNotEmptyNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(1, $queue->pop());
        $this->assertEquals(array(2,3,4,5), $queue->getList());
        
    }
    
    public function testPopEmptyNoStrict() {
        $queue = new Types\RestrictedQueue(array());
        
        $this->assertInstanceOf(Types\None::class, $queue->pop());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testPopEmptyWithStrict() {
        $queue = new Types\RestrictedQueue(array(), null, array('strict' => true));
        
        $p = $queue->pop();
        
    }
    
    public function testProperIndex() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(3, $queue->index(2));
        $this->assertEquals(5, $queue->index(4));
        $this->assertEquals(1, $queue->index(0));
        
    }
    
    public function testImproperIndexNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $this->assertInstanceOf(Types\None::class, $queue->index(7));
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperIndexWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions, array('strict'=>true));
        
        $i = $queue->index(7);
    }
    
    public function testProperIndexWithReference() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $testref = &$queue->indexReference(1);
        $testref = 7;
        
        $this->assertEquals(array(1,7,3,4,5), $queue->getList());
        
    }
    
    public function testImproperIndexWithReferenceNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $testref = &$queue->indexReference(7);
        $testref = 7;
        
        $this->assertEquals(array(1,2,3,4,5), $queue->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperIndexWithReferenceWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $testref = &$queue->indexReference(7);
        
    }
    
    public function testRollForward() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $queue->roll(2);
        
        $this->assertEquals(array(3,4,5,1,2), $queue->getList());
        
        $queue->roll(-3);
        
        $this->assertEquals(array(5,1,2,3,4), $queue->getlist());
        
    }
    
    public function testArrayAccessProperOffsetSet() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $queue[1] = 6;
        
        $this->assertEquals(array(1,6,3,4,5), $queue->getList());
    }
    
    public function testArrayAccessImproperOffsetSetNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $queue[8] = 6;
        
        $this->assertEquals(array(1,2,3,4,5), $queue->getList());
    }
    
    public function testArrayAccessImproperTypeOffsetSetNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $queue[1] = 'test';
        
        $this->assertEquals(array(1,2,3,4,5), $queue->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testArrayAccessImproperTypeOffsetSetWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $queue[1] = 'test';
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testArrayAccessImproperOffsetSetWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $queue[8] = 6;
    }
    
    public function testArrayAccessOffsetSetPush() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $queue[] = 6;
        
        $this->assertEquals(array(1,2,3,4,5,6), $queue->getList());
    }
    
    public function testArrayAccessImproperTypeOffsetSetPushNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $queue[] = 'test';
        
        $this->assertEquals(array(1,2,3,4,5), $queue->getList());
    }
    
    public function testArrayAccessProperOffsetSetPushTypedObject() {
        $restrictions = new Types\Restrictions(array(Type::TYPED_OBJECT),array('TestRestrictedQueueClass'));
        $queue = new Types\RestrictedQueue(array(), $restrictions);
        
        $t = new TestRestrictedQueueClass();
        
        $queue[] = $t;
        
        $this->assertEquals(array($t), $queue->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     *
     */
    public function testArrayAccessImproperTypeOffsetSetPushWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $queue[] = 'test';
    }
    
    public function testArrayAccessProperOffsetGet() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(2, $queue[1]);
        
    }
    
    public function testArrayAccessImproperOffsetGetNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $this->assertInstanceOf(Types\None::class, $queue[8]);
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testArrayAccessImproperOffsetGetWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $i = $queue[8];
        
    }
    
    public function testArrayAccessOffsetUnset() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        unset($queue[1]);
        
        $this->assertEquals(array(1,3,4,5), $queue->getList());
        
    }
    
    public function testArrayAccessOffsetExists() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $queue = new Types\RestrictedQueue(array(1,2,3,4,5), $restrictions);
        
        $this->assertTrue(isset($queue[1]));
        $this->assertFalse(isset($queue[8]));
        
    }
}
