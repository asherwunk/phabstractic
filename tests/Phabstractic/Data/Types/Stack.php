<?php

require_once('src/Phabstractic/Data/Types/Stack.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractList.php');
require_once('src/Phabstractic/Data/Types/Resource/ListInterface.php');
require_once('src/Phabstractic/Data/Types/None.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Resource as TypesResource;
use Phabstractic\Features\Resource as FeaturesResource;

class StackTest extends TestCase
{
    public function testEmptyInstantiation() {
        $stack = new Types\Stack();
        
        $this->assertInstanceOf(Types\Stack::class, $stack);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $stack);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $stack);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $stack);
        
        $this->assertEquals(array(), $stack->getList());
        
    }
    
    public function testArrayInstantiation() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $this->assertEquals(array(1,2,3,4,5), $stack->getList());
        
        $this->assertInstanceOf(Types\Stack::class, $stack);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $stack);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $stack);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $stack);
    }
    
    public function testAbstractListInstantiation() {
        $stack1 = new Types\Stack(array(1,2,3,4,5));
        $stack2 = new Types\Stack($stack1);
        
        $this->assertEquals(array(1,2,3,4,5), $stack2->getList());
        
        $this->assertInstanceOf(Types\Stack::class, $stack2);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $stack2);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $stack2);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $stack2);
    }
    
    public function testScalarListInstantiation() {
        $stack = new Types\Stack('thisisascalar');
        
        $this->assertEquals(array('thisisascalar'), $stack->getList());
        
        $this->assertInstanceOf(Types\Stack::class, $stack);
        $this->assertInstanceOf(TypesResource\AbstractList::class, $stack);
        $this->assertInstanceOf(TypesResource\ListInterface::class, $stack);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $stack);
    }
    
    public function testGetStack() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $this->assertEquals(array(5,4,3,2,1), $stack->getStack());
    }
    
    public function testCount() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $this->assertEquals(5, $stack->count());
    }
    
    public function testEmptyAndClear() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $this->assertFalse($stack->isEmpty());
        
        $stack->clear();
        
        $this->assertTrue($stack->isEmpty());
        
    }
    
    public function testRemove() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $stack->remove(4);
        
        $this->assertEquals(array(1,2,3,5), $stack->getList());
        
    }
    
    public function testBasicExchange() {
        $stack = new Types\Stack(array(1,'test1','test2'));
        
        $stack->exchange();
        
        $this->assertEquals(array(1,'test2','test1'), $stack->getList());
    }
    
    public function testReferencedExchange() {
        $testref1 = 'testref1';
        $testref2 = 'testref2';
        
        $stack = new Types\Stack(array(1,2,&$testref1,&$testref2));
        
        $stack->exchange();
        
        $testref2 = 'modified';
        
        $this->assertEquals(array(1,2,'modified','testref1'), $stack->getList());
        
    }
    
    public function testDuplicate() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $stack->duplicate();
        
        $this->assertEquals(array(1,2,3,4,5,5), $stack->getList());
        
    }
    
    public function testTopNotEmptyNoStrict() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $this->assertEquals(5, $stack->top());
        
    }
    
    public function testTopEmptyNoStrict() {
        $stack = new Types\Stack(array());
        
        $this->assertInstanceOf(Types\None::class, $stack->top());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testTopEmptyStrict() {
        $stack = new Types\Stack(array(), array('strict' => true));
        
        $top = $stack->top();
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testBottomEmptyStrict() {
        $stack = new Types\Stack(array(), array('strict' => true));
        
        $top = $stack->bottom();
        
    }
    
    public function testBottomNotEmptyNoStrict() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $this->assertEquals(1, $stack->bottom());
        
    }
    
    public function testBottomEmptyNoStrict() {
        $stack = new Types\Stack(array());
        
        $this->assertInstanceOf(Types\None::class, $stack->bottom());
        
    }
    
    public function testPushMultipleNoReference() {
        $stack = new Types\Stack();
        
        $stack->push(1, 2, 3, 4, 5);
        
        $this->assertEquals(array(1,2,3,4,5), $stack->getList());
    }
    
    public function testPushSingularNoReference() {
        $stack = new Types\Stack(array(1,2,3,4));
        
        $stack->push(5);
        
        $this->assertEquals(array(1,2,3,4,5), $stack->getList());
    }
    
    public function testPushSingularWithReference() {
        $testref = 'testref';
        
        $stack = new Types\Stack(array(1,2,3,4));
        
        $stack->pushReference($testref);
        
        $testref = 'modified';
        
        $this->assertEquals(array(1,2,3,4,'modified'), $stack->getList());
        
    }
    
    public function testPopNotEmptyNoStrict() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $this->assertEquals(5, $stack->pop());
        $this->assertEquals(array(1,2,3,4), $stack->getList());
        
    }
    
    public function testPopEmptyNoStrict() {
        $stack = new Types\Stack();
        
        $this->assertInstanceOf(Types\None::class, $stack->pop());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testPopEmptyWithStrict() {
        $stack = new Types\Stack(array(), array('strict' => true));
        
        $p = $stack->pop();
        
    }
    
    public function testProperIndex() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $this->assertEquals(3, $stack->index(2));
        $this->assertEquals(1, $stack->index(4));
        $this->assertEquals(5, $stack->index(0));
        
    }
    
    public function testImproperIndexNoStrict() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $this->assertInstanceOf(Types\None::class, $stack->index(7));
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperIndexWithStrict() {
        $stack = new Types\Stack(array(1,2,3,4,5), array('strict' => true));
        
        $i = $stack->index(7);
    }
    
    public function testProperIndexWithReference() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $testref = &$stack->indexReference(1);
        $testref = 7;
        
        $this->assertEquals(array(1,2,3,7,5), $stack->getList());
        
    }
    
    public function testImproperIndexWithReference() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $testref = &$stack->indexReference(7);
        $testref = 7;
        
        $this->assertEquals(array(1,2,3,4,5), $stack->getList());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperIndexWithReferenceWithStrict() {
        $stack = new Types\Stack(array(1,2,3,4,5), array('strict' => true));
        
        $testref = &$stack->indexReference(7);
        
    }
    
    public function testRollForward() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $stack->roll(2);
        
        $this->assertEquals(array(4,5,1,2,3), $stack->getList());
        
        $stack->roll(-3);
        
        $this->assertEquals(array(2,3,4,5,1), $stack->getlist());
        
    }
    
    public function testArrayAccessProperOffsetSet() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $stack[1] = 6;
        
        $this->assertEquals(array(1,2,3,6,5), $stack->getList());
    }
    
    public function testArrayAccessImproperOffsetSetNoStrict() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $stack[8] = 6;
        
        $this->assertEquals(array(1,2,3,4,5), $stack->getList());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testArrayAccessImproperOffsetSetWithStrict() {
        $stack = new Types\Stack(array(1,2,3,4,5), array('strict' => true));
        
        $stack[8] = 6;
    }
    
    public function testArrayAccessOffsetSetPush() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $stack[] = 6;
        
        $this->assertEquals(array(1,2,3,4,5,6), $stack->getList());
    }
    
    public function testArrayAccessProperOffsetGet() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $this->assertEquals(4, $stack[1]);
        
    }
    
    public function testArrayAccessImproperOffsetGetNoStrict() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $this->assertInstanceOf(Types\None::class, $stack[8]);
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testArrayAccessImproperOffsetGetWithStrict() {
        $stack = new Types\Stack(array(1,2,3,4,5), array('strict' => true));
        
        $i = $stack[8];
        
    }
    
    public function testArrayAccessOffsetUnset() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        unset($stack[1]);
        
        $this->assertEquals(array(1,2,3,5), $stack->getList());
        
    }
    
    public function testArrayAccessOffsetExists() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        $this->assertTrue(isset($stack[1]));
        $this->assertFalse(isset($stack[8]));
        
    }
    
    public function testSetDebugInfo() {
        $stack = new Types\Stack(array(1,2,3,4,5));
        
        ob_start();
        
        var_dump($stack);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?list\"?\]?.*=\\>\n.*array\\(5\\)/", $output);

    }
    
}
