<?php

require_once('src/Phabstractic/Data/Types/LexicographicList.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractList.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractSortedList.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractRestrictedList.php');
require_once('src/Phabstractic/Data/Types/Resource/ListInterface.php');
require_once('src/Phabstractic/Data/Types/None.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Resource as TypesResource;
use Phabstractic\Features\Resource as FeaturesResource;

class LexicographicListTest extends TestCase
{
    public function testEmptyInstantiation() {
        $list = new Types\LexicographicList();
        
        $this->assertInstanceOf(TypesResource\AbstractSortedList::class, $list);
        $this->assertInstanceOf(TypesResource\AbstractRestrictedList::class, $list);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $list);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $list);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $list);
        
        $this->assertEquals(array(), $list->getList());
        
    }
    
    public function testProperArrayInstantiation() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $this->assertEquals(array('five','four','one','three','two'), $list->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperArrayInstantiation() {
        $list = new Types\LexicographicList(array('one','two','three','four',5));
        
    }
    
    public function testProperAbstractListInstantiation() {
        $list1 = new Types\LexicographicList(array('one','two','three','four','five'));
        $list2 = new Types\LexicographicList($list1);
        
        $this->assertEquals(array('five','four','one','three','two'), $list2->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperAbstractListInstantiation() {
        $list1 = new Types\Stack(array('one','two','three','four',5));
        $list2 = new Types\LexicographicList($list1);
        
        $this->assertEquals(array('five','four','one','three','two'), $list2->getList());

    }
    
    public function testProperScalarListInstantiation() {
        $list = new Types\LexicographicList('thisisascalar');
        
        $this->assertEquals(array('thisisascalar'), $list->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperScalarListInstantiation() {
        $list = new Types\LexicographicList(5);
        
    }
    
    public function testCount() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $this->assertEquals(5, $list->count());
    }
    
    public function testEmptyAndClear() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $this->assertFalse($list->isEmpty());
        
        $list->clear();
        
        $this->assertTrue($list->isEmpty());
        
    }
    
    public function testRemove() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $list->remove('four');
        
        $this->assertEquals(array('five','one','three','two'), $list->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testBasicExchange() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $list->exchange();
        
    }
    
    public function testDuplicate() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $list->duplicate();
        
        $this->assertEquals(array('five','five','four','one','three','two'), $list->getList());
        
    }
    
    public function testTopNotEmptyNoStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $this->assertEquals('five', $list->top());
        
    }
    
    public function testTopEmptyNoStrict() {
        $list = new Types\LexicographicList(array());
        
        $this->assertInstanceOf(Types\None::class, $list->top());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testTopEmptyStrict() {
        $list = new Types\LexicographicList(array(), null, array('strict' => true));
        
        $top = $list->top();
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testBottomEmptyStrict() {
        $list = new Types\LexicographicList(array(), null, array('strict' => true));
        
        $top = $list->bottom();
        
    }
    
    public function testBottomNotEmptyNoStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $this->assertEquals('two', $list->bottom());
        
    }
    
    public function testBottomEmptyNoStrict() {
        $list = new Types\LexicographicList(array());
        
        $this->assertInstanceOf(Types\None::class, $list->bottom());
        
    }
    
    public function testProperPushMultipleNoReference() {
        $list = new Types\LexicographicList(array());
        
        $list->push('one','two','three','four','five');
        
        $this->assertEquals(array('five','four','one','three','two'), $list->getList());
    }
    
    public function testImproperPushMultipleNoReferenceNoStrict() {
        $list = new Types\LexicographicList(array());
        
        $list->push('one','two','three','four',5);
        
        $this->assertEquals(array(), $list->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperPushMultipleNoReferenceWithStrict() {
        $list = new Types\LexicographicList(array(), null, array('strict' => true));
        
        $list->push('one','two','three','four',5);
        
    }
    
    public function testPushSingularNoReference() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $list->push('six');
        
        $this->assertEquals(array('five','four','one','six','three','two'), $list->getList());
        
    }
    
    public function testPushSingularWithReference() {
        $testref = 'testref';
        
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $list->pushReference($testref);
        
        $testref = 'modified';
        
        $this->assertEquals(array('five','four','modified','one','three','two'), $list->getList());
        
    }
    
    public function testImproperPushSingularWithReferenceNoStrict() {
        $testref = 6;
        
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $list->pushReference($testref);
        
        $testref = 'modified';
        
        $this->assertEquals(array('five','four','one','three','two'), $list->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperPushSingularWithReferenceWithStrict() {
        $testref = 6;
        
        $list = new Types\LexicographicList(array('one','two','three','four','five'), null, array('strict' => true));
        
        $list->pushReference($testref);
        
    }
    
    public function testPopNotEmptyNoStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $this->assertEquals('five', $list->pop());
        $this->assertEquals(array('four','one','three','two'), $list->getList());
        
    }
    
    public function testPopEmptyNoStrict() {
        $list = new Types\LexicographicList(array());
        
        $this->assertInstanceOf(Types\None::class, $list->pop());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testPopEmptyWithStrict() {
        $list = new Types\LexicographicList(array(), null, array('strict' => true));
        
        $p = $list->pop();
        
    }
    
    public function testProperIndex() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $this->assertEquals('one', $list->index(2));
        $this->assertEquals('two', $list->index(4));
        $this->assertEquals('five', $list->index(0));
        
    }
    
    public function testImproperIndexNoStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $this->assertInstanceOf(Types\None::class, $list->index(7));
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperIndexWithStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'), null, array('strict' => true));
        
        $i = $list->index(7);
    }
    
    public function testProperIndexWithReference() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $testref = &$list->indexReference(1);
        $testref = 'testref';
        
        $this->assertEquals(array('five','one','testref','three','two'), $list->getList());
        
        $testref = 'aftersort';
        
        $this->assertEquals(array('aftersort','five','one','three','two'), $list->getList());
        
    }
    
    public function testImproperIndexWithReferenceNoStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $testref = &$list->indexReference(7);
        $testref = 7;
        
        $this->assertEquals(array('five','four','one','three','two'), $list->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperIndexWithReferenceWithStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'), null, array('strict' => true));
        
        $testref = &$list->indexReference(7);
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testRollForward() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $list->roll(2);
        
    }
    
    public function testArrayAccessProperOffsetSet() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $list[1] = 'six';
        
        $this->assertEquals(array('five','one','six','three','two'), $list->getList());
    }
    
    public function testArrayAccessImproperOffsetSetNoStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $list[8] = 'six';
        
        $this->assertEquals(array('five','four','one','three','two'), $list->getList());
    }
    
    public function testArrayAccessImproperTypeOffsetSetNoStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $list[1] = 6;
        
        $this->assertEquals(array('five','four','one','three','two'), $list->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testArrayAccessImproperTypeOffsetSetWithStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'), null, array('strict' => true));
        
        $list[1] = 6;
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testArrayAccessImproperOffsetSetWithStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'), null, array('strict' => true));
        
        $list[8] = 'six';
    }
    
    public function testArrayAccessOffsetSetPush() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $list[] = 'six';
        
        $this->assertEquals(array('five','four','one','six','three','two'), $list->getList());
    }
    
    public function testArrayAccessImproperTypeOffsetSetPushNoStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $list[] = 6;
        
        $this->assertEquals(array('five','four','one','three','two'), $list->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     *
     */
    public function testArrayAccessImproperTypeOffsetSetPushWithStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'), null, array('strict' => true));
        
        $list[] = 6;
    }
    
    public function testArrayAccessProperOffsetGet() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $this->assertEquals('four', $list[1]);
        
    }
    
    public function testArrayAccessImproperOffsetGetNoStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $this->assertInstanceOf(Types\None::class, $list[8]);
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testArrayAccessImproperOffsetGetWithStrict() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'), null, array('strict' => true));
        
        $i = $list[8];
        
    }
    
    public function testArrayAccessOffsetUnset() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        unset($list[1]);
        
        $this->assertEquals(array('five','one','three','two'), $list->getList());
        
    }
    
    public function testArrayAccessOffsetExists() {
        $list = new Types\LexicographicList(array('one','two','three','four','five'));
        
        $this->assertTrue(isset($list[1]));
        $this->assertFalse(isset($list[8]));
        
    }
    
}
