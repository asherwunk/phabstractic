<?php

require_once('src/Phabstractic/Data/Types/RestrictedStack.php');
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

class TestRestrictedStackClass {
    
}

class RestrictedStackTest extends TestCase
{
    public function testEmptyInstantiation() {
        $stack = new Types\RestrictedStack();
        
        $this->assertInstanceOf(Types\RestrictedList::class, $stack);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $stack);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $stack);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $stack);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $stack);
        
        $this->assertEquals(array(), $stack->getList());
        
    }
    
    public function testProperArrayInstantiation() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(array(1,2,3,4,5), $stack->getList());
        
        $this->assertInstanceOf(Types\RestrictedList::class, $stack);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $stack);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $stack);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $stack);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $stack);
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperArrayInstantiation() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,'five'), $restrictions);
        
    }
    
    public function testProperAbstractListInstantiation() {
        $stack1 = new Types\Stack(array(1,2,3,4,5));
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack2 = new Types\RestrictedStack($stack1, $restrictions);
        
        $this->assertEquals(array(1,2,3,4,5), $stack2->getList());
        
        $this->assertInstanceOf(Types\RestrictedList::class, $stack2);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $stack2);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $stack2);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $stack2);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $stack2);
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperAbstractListInstantiation() {
        $stack1 = new Types\Stack(array(1,2,3,4,'five'));
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack2 = new Types\RestrictedStack($stack1, $restrictions);
        
    }
    
    public function testProperScalarListInstantiation() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_STRING));
        $stack = new Types\RestrictedStack('thisisascalar', $restrictions);
        
        $this->assertEquals(array('thisisascalar'), $stack->getList());
        
        $this->assertInstanceOf(Types\RestrictedList::class, $stack);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $stack);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $stack);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $stack);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $stack);
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperScalarListInstantiation() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack('thisisascalar', $restrictions);
        
    }
    
    public function testCount() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(5, $stack->count());
    }
    
    public function testEmptyAndClear() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $this->assertFalse($stack->isEmpty());
        
        $stack->clear();
        
        $this->assertTrue($stack->isEmpty());
        
    }
    
    public function testRemove() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $stack->remove(4);
        
        $this->assertEquals(array(1,2,3,5), $stack->getList());
        
    }
    
    public function testBasicExchange() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $stack->exchange();
        
        $this->assertEquals(array(1,2,3,5,4), $stack->getList());
    }
    
    public function testReferencedExchange() {
        $testref1 = 'testref1';
        $testref2 = 'testref2';
        
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT, Type::BASIC_STRING));
        $stack = new Types\RestrictedStack(array(1,2,&$testref1,&$testref2), $restrictions);
        
        $stack->exchange();
        
        $testref2 = 'modified';
        
        $this->assertEquals(array(1,2,'modified','testref1'), $stack->getList());
        
    }
    
    public function testDuplicate() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $stack->duplicate();
        
        $this->assertEquals(array(1,2,3,4,5,5), $stack->getList());
        
    }
    
    public function testTopNotEmptyNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(5, $stack->top());
        
    }
    
    public function testTopEmptyNoStrict() {
        $stack = new Types\RestrictedStack(array());
        
        $this->assertInstanceOf(Types\None::class, $stack->top());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testTopEmptyStrict() {
        $stack = new Types\RestrictedStack(array(), null, array('strict' => true));
        
        $top = $stack->top();
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testBottomEmptyStrict() {
        $stack = new Types\RestrictedStack(array(), null, array('strict' => true));
        
        $top = $stack->bottom();
        
    }
    
    public function testBottomNotEmptyNoStrict() {
        $stack = new Types\RestrictedStack(array(1,2,3,4,5));
        
        $this->assertEquals(1, $stack->bottom());
        
    }
    
    public function testBottomEmptyNoStrict() {
        $stack = new Types\RestrictedStack(array());
        
        $this->assertInstanceOf(Types\None::class, $stack->bottom());
        
    }
    
    public function testProperPushMultipleNoReference() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(), $restrictions);
        
        $stack->push(1, 2, 3, 4, 5);
        
        $this->assertEquals(array(1,2,3,4,5), $stack->getList());
    }
    
    public function testImproperPushMultipleNoReferenceNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(), $restrictions);
        
        $stack->push(1, 2, 3, 4, 'test');
        
        $this->assertEquals(array(), $stack->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperPushMultipleNoReferenceWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(), $restrictions, array('strict' => true));
        
        $stack->push(1, 2, 3, 4, 'test');
    }
    
    public function testPushSingularNoReference() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4), $restrictions, array('strict' => true));
        
        $stack->push(5);
        
        $this->assertEquals(array(1,2,3,4,5), $stack->getList());
    }
    
    public function testPushSingularWithReference() {
        $testref = 'testref';
        
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT, Type::BASIC_STRING));
        $stack = new Types\RestrictedStack(array(1,2,3,4), $restrictions, array('strict' => true));
        
        $stack->pushReference($testref);
        
        $testref = 'modified';
        
        $this->assertEquals(array(1,2,3,4,'modified'), $stack->getList());
        
    }
    
    public function testPushSingularNoReferenceTypedObject() {
        $restrictions = new Types\Restrictions(array(Type::TYPED_OBJECT),array('TestRestrictedStackClass'));
        $stack = new Types\RestrictedStack(array(), $restrictions, array('strict' => true));
        
        $t = new TestRestrictedStackClass();
        
        $stack->push($t);
        
        $this->assertEquals(array($t), $stack->getList());
    }
    
    public function testImproperPushSingularWithReferenceNoStrict() {
        $testref = 'testref';
        
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4), $restrictions);
        
        $stack->pushReference($testref);
        
        $testref = 'modified';
        
        $this->assertEquals(array(1,2,3,4), $stack->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperPushSingularWithReferenceWithStrict() {
        $testref = 'testref';
        
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4), $restrictions, array('strict' => true));
        
        $stack->pushReference($testref);
        
        $testref = 'modified';
        
        $this->assertEquals(array(1,2,3,4), $stack->getList());
        
    }
    
    public function testPopNotEmptyNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(5, $stack->pop());
        $this->assertEquals(array(1,2,3,4), $stack->getList());
        
    }
    
    public function testPopEmptyNoStrict() {
        $stack = new Types\RestrictedStack(array());
        
        $this->assertInstanceOf(Types\None::class, $stack->pop());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testPopEmptyWithStrict() {
        $stack = new Types\RestrictedStack(array(), null, array('strict' => true));
        
        $p = $stack->pop();
        
    }
    
    public function testProperIndex() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(3, $stack->index(2));
        $this->assertEquals(1, $stack->index(4));
        $this->assertEquals(5, $stack->index(0));
        
    }
    
    public function testImproperIndexNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $this->assertInstanceOf(Types\None::class, $stack->index(7));
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperIndexWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions, array('strict'=>true));
        
        $i = $stack->index(7);
    }
    
    public function testProperIndexWithReference() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $testref = &$stack->indexReference(1);
        $testref = 7;
        
        $this->assertEquals(array(1,2,3,7,5), $stack->getList());
        
    }
    
    public function testImproperIndexWithReferenceNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $testref = &$stack->indexReference(7);
        $testref = 7;
        
        $this->assertEquals(array(1,2,3,4,5), $stack->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperIndexWithReferenceWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $testref = &$stack->indexReference(7);
        
    }
    
    public function testRollForward() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $stack->roll(2);
        
        $this->assertEquals(array(4,5,1,2,3), $stack->getList());
        
        $stack->roll(-3);
        
        $this->assertEquals(array(2,3,4,5,1), $stack->getlist());
        
    }
    
    public function testArrayAccessProperOffsetSet() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $stack[1] = 6;
        
        $this->assertEquals(array(1,2,3,6,5), $stack->getList());
    }
    
    public function testArrayAccessImproperOffsetSetNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $stack[8] = 6;
        
        $this->assertEquals(array(1,2,3,4,5), $stack->getList());
    }
    
    public function testArrayAccessImproperTypeOffsetSetNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $stack[1] = 'test';
        
        $this->assertEquals(array(1,2,3,4,5), $stack->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testArrayAccessImproperTypeOffsetSetWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $stack[1] = 'test';
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testArrayAccessImproperOffsetSetWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $stack[8] = 6;
    }
    
    public function testArrayAccessOffsetSetPush() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $stack[] = 6;
        
        $this->assertEquals(array(1,2,3,4,5,6), $stack->getList());
    }
    
    public function testArrayAccessImproperTypeOffsetSetPushNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $stack[] = 'test';
        
        $this->assertEquals(array(1,2,3,4,5), $stack->getList());
    }
    
    public function testArrayAccessProperOffsetSetPushTypedObject() {
        $restrictions = new Types\Restrictions(array(Type::TYPED_OBJECT),array('TestRestrictedStackClass'));
        $stack = new Types\RestrictedStack(array(), $restrictions);
        
        $t = new TestRestrictedStackClass();
        
        $stack[] = $t;
        
        $this->assertEquals(array($t), $stack->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     *
     */
    public function testArrayAccessImproperTypeOffsetSetPushWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $stack[] = 'test';
    }
    
    public function testArrayAccessProperOffsetGet() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(4, $stack[1]);
        
    }
    
    public function testArrayAccessImproperOffsetGetNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $this->assertInstanceOf(Types\None::class, $stack[8]);
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testArrayAccessImproperOffsetGetWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $i = $stack[8];
        
    }
    
    public function testArrayAccessOffsetUnset() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        unset($stack[1]);
        
        $this->assertEquals(array(1,3,4,5), $stack->getList());
        
    }
    
    public function testArrayAccessOffsetExists() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $stack = new Types\RestrictedStack(array(1,2,3,4,5), $restrictions);
        
        $this->assertTrue(isset($stack[1]));
        $this->assertFalse(isset($stack[8]));
        
    }
}
