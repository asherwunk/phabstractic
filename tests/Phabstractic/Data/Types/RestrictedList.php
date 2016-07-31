<?php

require_once('src/Phabstractic/Data/Types/RestrictedList.php');
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

class TestRestrictedListClass {
    
}

class RestrictedListTest extends TestCase
{
    public function testEmptyInstantiation() {
        $list = new Types\RestrictedList();
        
        $this->assertInstanceOf(Types\RestrictedList::class, $list);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $list);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $list);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $list);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $list);
        
        $this->assertEquals(array(), $list->getList());
        
    }
    
    public function testProperArrayInstantiation() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5),$restrictions);
        
        $this->assertEquals(array(1,2,3,4,5), $list->getList());
        
        $this->assertInstanceOf(Types\RestrictedList::class, $list);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $list);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $list);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $list);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $list);
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperArrayInstantiation() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,'five'),$restrictions);
        
    }
    
    public function testProperAbstractListInstantiation() {
        $stack1 = new Types\Stack(array(1,2,3,4,5));
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList($stack1, $restrictions);
        
        $this->assertEquals(array(1,2,3,4,5), $list->getList());
        
        $this->assertInstanceOf(Types\RestrictedList::class, $list);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $list);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $list);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $list);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $list);
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperAbstractListInstantiation() {
        $stack1 = new Types\Stack(array(1,2,3,4,'five'));
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList($stack1, $restrictions);
        
        
    }
    
    public function testProperScalarListInstantiation() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_STRING));
        $list = new Types\RestrictedList('thisisascalar', $restrictions);
        
        $this->assertEquals(array('thisisascalar'), $list->getList());
        
        $this->assertInstanceOf(Types\RestrictedList::class, $list);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $list);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $list);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $list);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $list);
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperScalarListInstantiation() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList('thisisascalar', $restrictions);
        
    }
    
    public function testCount() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(5, $list->count());
    }
    
    public function testEmptyAndClear() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $this->assertFalse($list->isEmpty());
        
        $list->clear();
        
        $this->assertTrue($list->isEmpty());
        
    }
    
    public function testRemove() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $list->remove(4);
        
        $this->assertEquals(array(1,2,3,5), $list->getList());
        
    }
    
    public function testBasicExchange() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $list->exchange();
        
        $this->assertEquals(array(1,2,3,5,4), $list->getList());
    }
    
    public function testReferencedExchange() {
        $testref1 = 'testref1';
        $testref2 = 'testref2';
        
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT, Type::BASIC_STRING));
        $list = new Types\RestrictedList(array(1,2,&$testref1,&$testref2), $restrictions);
        
        $list->exchange();
        
        $testref2 = 'modified';
        
        $this->assertEquals(array(1,2,'modified','testref1'), $list->getList());
        
    }
    
    public function testDuplicate() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $list->duplicate();
        
        $this->assertEquals(array(1,2,3,4,5,5), $list->getList());
        
    }
    
    public function testTopNotEmptyNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(5, $list->top());
        
    }
    
    public function testTopEmptyNoStrict() {
        $list = new Types\RestrictedList(array());
        
        $this->assertInstanceOf(Types\None::class, $list->top());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testTopEmptyStrict() {
        $list = new Types\RestrictedList(array(), null, array('strict' => true));
        
        $top = $list->top();
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testBottomEmptyStrict() {
        $list = new Types\RestrictedList(array(), null, array('strict' => true));
        
        $top = $list->bottom();
        
    }
    
    public function testBottomNotEmptyNoStrict() {
        $list = new Types\RestrictedList(array(1,2,3,4,5));
        
        $this->assertEquals(1, $list->bottom());
        
    }
    
    public function testBottomEmptyNoStrict() {
        $list = new Types\RestrictedList(array());
        
        $this->assertInstanceOf(Types\None::class, $list->bottom());
        
    }
    
    
    public function testProperPushMultipleNoReference() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(), $restrictions);
        
        $list->push(1, 2, 3, 4, 5);
        
        $this->assertEquals(array(1,2,3,4,5), $list->getList());
    }
    
    public function testImproperPushMultipleNoReferenceNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(), $restrictions);
        
        $list->push(1, 2, 3, 4, 'test');
        
        $this->assertEquals(array(), $list->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperPushMultipleNoReferenceWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(), $restrictions, array('strict' => true));
        
        $list->push(1, 2, 3, 4, 'test');
    }
    
    public function testPushSingularNoReference() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4), $restrictions, array('strict' => true));
        
        $list->push(5);
        
        $this->assertEquals(array(1,2,3,4,5), $list->getList());
    }
    
    public function testPushSingularWithReference() {
        $testref = 'testref';
        
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT, Type::BASIC_STRING));
        $list = new Types\RestrictedList(array(1,2,3,4), $restrictions, array('strict' => true));
        
        $list->pushReference($testref);
        
        $testref = 'modified';
        
        $this->assertEquals(array(1,2,3,4,'modified'), $list->getList());
        
    }
    
    public function testPushSingularNoReferenceTypedObject() {
        $restrictions = new Types\Restrictions(array(Type::TYPED_OBJECT),array('TestRestrictedListClass'));
        $list = new Types\RestrictedList(array(), $restrictions, array('strict' => true));
        
        $t = new TestRestrictedListClass();
        
        $list->push($t);
        
        $this->assertEquals(array($t), $list->getList());
    }
    
    public function testImproperPushSingularWithReferenceNoStrict() {
        $testref = 'testref';
        
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4), $restrictions);
        
        $list->pushReference($testref);
        
        $testref = 'modified';
        
        $this->assertEquals(array(1,2,3,4), $list->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperPushSingularWithReferenceWithStrict() {
        $testref = 'testref';
        
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4), $restrictions, array('strict' => true));
        
        $list->pushReference($testref);
        
        $testref = 'modified';
        
        $this->assertEquals(array(1,2,3,4), $list->getList());
        
    }
    
    public function testPopNotEmptyNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(5, $list->pop());
        $this->assertEquals(array(1,2,3,4), $list->getList());
        
    }
    
    public function testPopEmptyNoStrict() {
        $list = new Types\RestrictedList(array());
        
        $this->assertInstanceOf(Types\None::class, $list->pop());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testPopEmptyWithStrict() {
        $list = new Types\RestrictedList(array(), null, array('strict' => true));
        
        $p = $list->pop();
        
    }
    
    public function testProperIndex() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(3, $list->index(2));
        $this->assertEquals(5, $list->index(4));
        $this->assertEquals(1, $list->index(0));
        
    }
    
    public function testImproperIndexNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $this->assertInstanceOf(Types\None::class, $list->index(7));
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperIndexWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions, array('strict'=>true));
        
        $i = $list->index(7);
    }
    
    public function testProperIndexWithReference() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $testref = &$list->indexReference(1);
        $testref = 7;
        
        $this->assertEquals(array(1,7,3,4,5), $list->getList());
        
    }
    
    public function testImproperIndexWithReferenceNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $testref = &$list->indexReference(7);
        $testref = 7;
        
        $this->assertEquals(array(1,2,3,4,5), $list->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperIndexWithReferenceWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $testref = &$list->indexReference(7);
        
    }
    
    public function testRollForward() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $list->roll(2);
        
        $this->assertEquals(array(4,5,1,2,3), $list->getList());
        
        $list->roll(-3);
        
        $this->assertEquals(array(2,3,4,5,1), $list->getlist());
        
    }
    
    public function testArrayAccessProperOffsetSet() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $list[1] = 6;
        
        $this->assertEquals(array(1,6,3,4,5), $list->getList());
    }
    
    public function testArrayAccessImproperOffsetSetNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $list[8] = 6;
        
        $this->assertEquals(array(1,2,3,4,5), $list->getList());
    }
    
    public function testArrayAccessImproperTypeOffsetSetNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $list[1] = 'test';
        
        $this->assertEquals(array(1,2,3,4,5), $list->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testArrayAccessImproperTypeOffsetSetWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $list[1] = 'test';
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testArrayAccessImproperOffsetSetWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $list[8] = 6;
    }
    
    public function testArrayAccessOffsetSetPush() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $list[] = 6;
        
        $this->assertEquals(array(1,2,3,4,5,6), $list->getList());
    }
    
    public function testArrayAccessImproperTypeOffsetSetPushNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $list[] = 'test';
        
        $this->assertEquals(array(1,2,3,4,5), $list->getList());
    }
    
    public function testArrayAccessProperOffsetSetPushTypedObject() {
        $restrictions = new Types\Restrictions(array(Type::TYPED_OBJECT),array('TestRestrictedListClass'));
        $list = new Types\RestrictedList(array(), $restrictions);
        
        $t = new TestRestrictedListClass();
        
        $list[] = $t;
        
        $this->assertEquals(array($t), $list->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     *
     */
    public function testArrayAccessImproperTypeOffsetSetPushWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $list[] = 'test';
    }
    
    public function testArrayAccessProperOffsetGet() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $this->assertEquals(2, $list[1]);
        
    }
    
    public function testArrayAccessImproperOffsetGetNoStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $this->assertInstanceOf(Types\None::class, $list[8]);
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testArrayAccessImproperOffsetGetWithStrict() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions, array('strict' => true));
        
        $i = $list[8];
        
    }
    
    public function testArrayAccessOffsetUnset() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        unset($list[1]);
        
        $this->assertEquals(array(1,3,4,5), $list->getList());
        
    }
    
    public function testArrayAccessOffsetExists() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT));
        $list = new Types\RestrictedList(array(1,2,3,4,5), $restrictions);
        
        $this->assertTrue(isset($list[1]));
        $this->assertFalse(isset($list[8]));
        
    }
}
