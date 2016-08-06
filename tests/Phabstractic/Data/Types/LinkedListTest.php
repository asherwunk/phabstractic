<?php

require_once('src/Phabstractic/Data/Types/LinkedList.php');
require_once('src/Phabstractic/Data/Types/LinkedListElement.php');
require_once('src/Phabstractic/Data/Types/Resource/LinkedListInterface.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractLinkedList.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Resource as TypesResource;
use Phabstractic\Features\Resource as FeaturesResource;

class LinkedListTest extends TestCase
{
    
    public function testBasicEmptyInstantiation() {
        $list = new Types\LinkedList();
        
        $this->assertInstanceOf(Types\LinkedList::class, $list);
        $this->assertInstanceOf(TypesResource\AbstractLinkedList::class, $list);
        $this->assertInstanceOf(TypesResource\LinkedListInterface::class, $list);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $list);
        
    }
    
    public function testInstantiationWithElement() {
        $element = Types\LinkedListElement::buildElement('testdata');
        
        $list = new Types\LinkedList($element);
        
        $this->assertInstanceOf(Types\LinkedList::class, $list);
        $this->assertInstanceOf(TypesResource\AbstractLinkedList::class, $list);
        $this->assertInstanceOf(TypesResource\LinkedListInterface::class, $list);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $list);
        
        $this->assertFalse($list->getSentinelElement() === null);
        
    }
    
    public function testInsertElementBefore() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $list = new Types\LinkedList();
        
        // should insert at beginning of list (sentinel)
        $list->insertElementBefore($firstelement);
        
        $this->assertEquals($firstelement, $list->getSentinelElement());
        
        $secondelement = Types\LinkedListElement::buildElement('second');
        
        // should insert at beginning of list (sentinel)
        $list->insertElementBefore($secondelement);
        
        $this->assertEquals($secondelement, $list->getSentinelElement());
        
        $thirdelement = Types\LinkedListElement::buildElement('third');
        
        // should insert one element after secondelement, and one before firstelement
        $list->insertElementBefore($thirdelement, $firstelement);
        
        $this->assertEquals(array(
            'second',
            'third',
            'first'), $list->flatten());
        
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        // should insert one element after secondelement, and one before firstelement
        $list->insertElementBefore($fourthelement);
        
        $this->assertEquals(array(
            'fourth',
            'second',
            'third',
            'first'), $list->flatten());
            
        // insert already existing element
        
        $this->assertFalse($list->insertElementBefore($secondelement));
        
    }
    
    public function testInsertElementAfter() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $list = new Types\LinkedList();
        
        // should insert at beginning of list (sentinel)
        $list->insertElementAfter($firstelement);
        
        $this->assertEquals($firstelement, $list->getSentinelElement());
        
        $secondelement = Types\LinkedListElement::buildElement('second');
        
        // should insert at end of list
        $list->insertElementAfter($secondelement);
        
        $this->assertEquals(array(
            'first',
            'second',), $list->flatten());
        
        $thirdelement = Types\LinkedListElement::buildElement('third');
        
        // should insert one element after firstelement, and one before secondelement
        $list->insertElementAfter($thirdelement, $firstelement);
        
        $this->assertEquals(array(
            'first',
            'third',
            'second'), $list->flatten());
        
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        // should insert one element after secondelement, and one before firstelement
        $list->insertElementAfter($fourthelement, $thirdelement);
        
        $this->assertEquals(array(
            'first',
            'third',
            'fourth',
            'second'), $list->flatten());
        
        // insert already existing element
        
        $list->insertElementAfter($secondelement);
        
        $this->assertFalse($list->insertElementAfter($secondelement));
        
    }
    
    public function testRemoveElement() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list = new Types\LinkedList();
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        $list->removeElement($firstelement);
        
        $this->assertEquals(array(
            'second',
            'third',
            'fourth',), $list->flatten());
        
        $list->removeElement($thirdelement);
        
        $this->assertEquals(array(
            'second',
            'fourth',), $list->flatten());
        
        $list->removeElement($fourthelement);
        
        $this->assertEquals(array(
            'second',), $list->flatten());
        
        $list->removeElement($secondelement);
        
        $this->assertEquals(array(), $list->flatten());
        
        // test remove element doesn't exist
        $this->assertFalse($list->removeElement($secondelement));
        
    }
    
    public function testFindElement() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list = new Types\LinkedList();
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        $this->assertEquals($secondelement, $list->findElement('second'));
        $this->assertEquals($fourthelement, $list->findElement('fourth'));
        $this->assertEquals($firstelement, $list->findElement('first'));
        $this->assertEquals($thirdelement, $list->findElement('third'));
        
    }
    
    public function testIteration() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list = new Types\LinkedList();
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        $comparr = array('first','second','third','fourth');
        
        $list->rewind();
        $this->assertEquals('Phabstractic\\Data\\Types\\LinkedList::currentElement', $list->key());
        $this->assertEquals($firstelement, current($list));
        $list->next();
        $this->assertEquals($secondelement, current($list));
        
        // Am I Missing Something?  Maybe I Dont Understand Something...
        
        /* reset($list);  <--- this didn't work as expected
        $this->assertEquals($firstelement, current($list)); */
        $list->rewind();
        $this->assertEquals($firstelement, current($list));
        
        $each = each($list);
        $this->assertEquals($firstelement, $each[1]);
        /* $each = each($list);  <--- didn't work as expected
        $this->assertEquals($secondelement, $each[1]); */
        
        reset($comparr);
        $same = true;
        foreach ($list as $element) {
            if ($element->getData() != each($comparr)[1]) {
                $same = false;
            }
        }
        
        $this->assertTrue($same);
        
    }
    
    public function testCount() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list = new Types\LinkedList();
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        $this->assertEquals(4, count($list));
        
        $list->removeElement($secondelement);
        
        $this->assertEquals(3, count($list));
        
    }
    
    public function testOffsetGetNoStrict() {
        $list = new Types\LinkedList();
        
        $i = $list[5];
        
        $this->assertNull($i);
        
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        $i = $list[2];
        
        $this->assertEquals($thirdelement, $i);
        
        $i = $list[9];
        
        $this->assertNull($i);
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testOffsetGetEmptyWithStrict() {
        $list = new Types\LinkedList(null, array('strict' => true));
        
        $i = $list[5];
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testOffsetGetOverWithStrict() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        
        $list = new Types\LinkedList(null, array('strict' => true));
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        
        $i = $list[5];
        
    }
    
    public function testOffsetUnset() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list = new Types\LinkedList();
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        unset($list[2]);
        
        $this->assertEquals(array(
            'first',
            'second',
            'fourth',), $list->flatten());
        
        unset($list[9]);
        
        $this->assertEquals(array(
            'first',
            'second',
            'fourth',), $list->flatten());
    }
    
    public function testOffsetExists() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        
        $list = new Types\LinkedList();
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        
        $this->assertTrue(isset($list[1]));
        $this->assertTrue(isset($list[0]));
        $this->assertFalse(isset($list[5]));
        
    }
    
    public function testOffsetSet() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list = new Types\LinkedList();
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        $replacement = Types\LinkedListElement::buildElement('replacement');
        
        $list[2] = $replacement;
        
        $this->assertEquals(array(
            'first',
            'second',
            'replacement',
            'fourth',), $list->flatten());
        
        $newelement = Types\LinkedListElement::buildElement('new');
        
        $list[7] = $newelement;
        
        $this->assertEquals(array(
            'first',
            'second',
            'replacement',
            'fourth',), $list->flatten());
        
        $list[] = $newelement;
        
        $this->assertEquals(array(
            'first',
            'second',
            'replacement',
            'fourth',
            'new',), $list->flatten());
            
    }
    
    public function testAddNoStrict() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list = new Types\LinkedList();
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        $newelement = Types\LinkedListElement::buildElement('new');
        
        $list->add(9, $newelement);
        
        $this->assertEquals(array(
            'first',
            'second',
            'third',
            'fourth',), $list->flatten());
        
        $list->add(1, $newelement);
        
        $this->assertEquals(array(
            'first',
            'new',
            'second',
            'third',
            'fourth',), $list->flatten());
            
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\OutOfRangeException
     * 
     */
    public function testAddWithStrict() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list = new Types\LinkedList(null, array('strict' => true));
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        $newelement = Types\LinkedListElement::buildElement('new');
        
        $list->add(9, $newelement);
        
    }
    
    public function testBottom() {
        $list = new Types\LinkedList();
        
        $this->assertNull($list->bottom());
        
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        
        $list->insertElementAfter($firstelement);
        $this->assertEquals($firstelement, $list->bottom());
        
        $list->insertElementAfter($secondelement);
        $this->assertEquals($firstelement, $list->bottom());
        
        $list->insertElementAfter($thirdelement);
        $this->assertEquals($firstelement, $list->bottom());
        
    }
    
    public function testBottomReference() {
        $list = new Types\LinkedList();
        
        $this->assertNull($list->bottomReference());
        
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        $bottom = &$list->bottomReference();
        $this->assertEquals($firstelement, $bottom);
        
        $bottom->setData('modified');
        
        $this->assertEquals(array(
            'modified',
            'second',
            'third',
            'fourth',), $list->flatten());
        
    }
    
    public function testTop() {
        $list = new Types\LinkedList();
        
        $this->assertNull($list->top());
        
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        
        $list->insertElementAfter($firstelement);
        $this->assertEquals($firstelement, $list->top());
        
        $list->insertElementAfter($secondelement);
        $this->assertEquals($secondelement, $list->top());
        
        $list->insertElementAfter($thirdelement);
        $this->assertEquals($thirdelement, $list->top());
        
        
    }
    
    public function testTopReference() {
        $list = new Types\LinkedList();
        
        $this->assertNull($list->topReference());
        
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        $top = &$list->topReference();
        $this->assertEquals($fourthelement, $top);
        
        $top->setData('modified');
        
        $this->assertEquals(array(
            'first',
            'second',
            'third',
            'modified',), $list->flatten());
        
    }
    
    public function testCurrent() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list = new Types\LinkedList();
        
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        foreach ($list as $element) {
            $this->assertEquals($list->current(), $element);
        }
        
    }
    
    public function testPop() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list = new Types\LinkedList();
        
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        $this->assertEquals($fourthelement, $list->pop());
        
        $this->assertEquals(array(
            'first',
            'second',
            'third',), $list->flatten());
        
        $this->assertEquals($thirdelement, $list->pop());
        
        $this->assertEquals(array(
            'first',
            'second',), $list->flatten());
        
        $this->assertEquals($secondelement, $list->pop());
        
        $this->assertEquals(array(
            'first',), $list->flatten());
        
        $this->assertEquals($firstelement, $list->pop());
        
        $this->assertEquals(array(), $list->flatten());
        
    }
    
    public function testPush() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list = new Types\LinkedList();
        
        $list->insertElementAfter($firstelement);
        
        $list->push($secondelement);
        
        $this->assertEquals(array(
            'first',
            'second',), $list->flatten());
        
        $list->push($thirdelement);
        
        $this->assertEquals(array(
            'first',
            'second',
            'third',), $list->flatten());
        
        $list->push($fourthelement);
        
        $this->assertEquals(array(
            'first',
            'second',
            'third',
            'fourth',), $list->flatten());
        
    }
    
    public function testShift() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list = new Types\LinkedList();
        
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        $this->assertEquals($firstelement, $list->shift());
        
        $this->assertEquals(array(
            'second',
            'third',
            'fourth'), $list->flatten());
        
        $this->assertEquals($secondelement, $list->shift());
        
        $this->assertEquals(array(
            'third',
            'fourth'), $list->flatten());
        
        $this->assertEquals($thirdelement, $list->shift());
        
        $this->assertEquals(array(
            'fourth'), $list->flatten());
        
        $this->assertEquals($fourthelement, $list->shift());
        
        $this->assertEquals(array(), $list->flatten());
        
    }
    
    public function testUnshift() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list = new Types\LinkedList();
        
        $list->insertElementAfter($firstelement);
        
        $list->unshift($secondelement);
        
        $this->assertEquals(array(
            'second',
            'first',), $list->flatten());
        
        $list->unshift($thirdelement);
        
        $this->assertEquals(array(
            'third',
            'second',
            'first',), $list->flatten());
        
        $list->unshift($fourthelement);
        
        $this->assertEquals(array(
            'fourth',
            'third',
            'second',
            'first',), $list->flatten());
        
    }
    
    public function testDebugInfo() {
        $firstelement = Types\LinkedListElement::buildElement('first');
        $secondelement = Types\LinkedListElement::buildElement('second');
        $thirdelement = Types\LinkedListElement::buildElement('third');
        $fourthelement = Types\LinkedListElement::buildElement('fourth');
        
        $list = new Types\LinkedList();
        
        $list->insertElementAfter($firstelement);
        $list->insertElementAfter($secondelement);
        $list->insertElementAfter($thirdelement);
        $list->insertElementAfter($fourthelement);
        
        ob_start();
        
        var_dump($list);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?data\"?\]?.*=\\>\n.*array\\(4\\)/", $output);
    }
    
}
