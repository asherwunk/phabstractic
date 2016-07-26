<?php

require_once('src/Phabstractic/Data/Types/LinkedListElement.php');
require_once('src/Phabstractic/Data/Types/Resource/LinkedListElementInterface.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractLinkedListElement.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Resource as TypesResource;

class LinkedListElementTest extends TestCase
{
    
    public function testBasicInstantiationWithoutElements() {
        $data = 'testdata';
        $element = new Types\LinkedListElement($data);
        
        $this->assertInstanceOf(Types\LinkedListElement::class, $element);
        $this->assertInstanceOf(TypesResource\AbstractLinkedListElement::class, $element);
        $this->assertInstanceOf(TypesResource\LinkedListElementInterface::class, $element);
        
        $this->assertEquals('testdata', $element->getData());
    }
    
    /**
     * @depends testBasicInstantiationWithoutElements
     * 
     */
    public function testDataReferencing() {
        $data = 'testdata';
        $element = new Types\LinkedListElement($data);
        
        $data = 'modified';
        
        $this->assertEquals('modified', $element->getData());
        
        $testref = 'newdata';
        
        $element->setData($testref);
        
        $testref = 'modified';
        
        $this->assertEquals('newdata', $element->getData());
        
        $testref = &$element->getDataReference();
        
        $testref = 'modified';
        
        $this->assertEquals('modified', $element->getData());
        
    }
    
    public function testBasicBuildWithoutElements() {
        $element = Types\LinkedListElement::buildElement('testdata');
        
        $this->assertInstanceOf(Types\LinkedListElement::class, $element);
        $this->assertInstanceOf(TypesResource\AbstractLinkedListElement::class, $element);
        $this->assertInstanceOf(TypesResource\LinkedListElementInterface::class, $element);
        
        $this->assertEquals('testdata', $element->getData());
    }
    
    /**
     * @depends testBasicBuildWithoutElements
     * 
     */
    public function testInstantiationWithElements() {
        $prevelement = Types\LinkedListElement::buildElement('previous');
        $nextelement = Types\LinkedListElement::buildElement('next');
        $element = Types\LinkedListElement::buildElement('testdata', $prevelement, $nextelement);
        
        
        $this->assertInstanceOf(Types\LinkedListElement::class, $element);
        $this->assertInstanceOf(TypesResource\AbstractLinkedListElement::class, $element);
        $this->assertInstanceOf(TypesResource\LinkedListElementInterface::class, $element);
        
        $this->assertEquals('testdata', $element->getData());
        $this->assertEquals('previous', $element->getPreviousElement()->getData());
        $this->assertEquals('next', $element->getNextElement()->getData());
        
    }
    
    /**
     * @depends testBasicBuildWithoutElements
     * 
     */
    public function testSetNextElement() {
        $element = Types\LinkedListElement::buildElement('testdata');
        $next = Types\LinkedListElement::buildElement('next');
        
        $element->setNextElement($next);
        
        $this->assertEquals('testdata', $element->getData());
        $this->assertEquals($next, $element->getNextElement());
        $this->assertEquals('next', $element->getNextElement()->getData());
        
    }
    
    /**
     * @depends testBasicBuildWithoutElements
     * 
     */
    public function testSetPreviousElement() {
        $element = Types\LinkedListElement::buildElement('testdata');
        $prev = Types\LinkedListElement::buildElement('previous');
        
        $element->setPreviousElement($prev);
        
        $this->assertEquals('testdata', $element->getData());
        $this->assertEquals($prev, $element->getPreviousElement());
        $this->assertEquals('previous', $element->getPreviousElement()->getData());
        
    }
    
    /**
     * @depends testSetNextElement
     * 
     */
    public function testNullNextElement() {
        $element = Types\LinkedListElement::buildElement('testdata');
        $next = Types\LinkedListElement::buildElement('next');
        
        $element->setNextElement($next);
        
        $this->assertEquals('testdata', $element->getData());
        $this->assertEquals($next, $element->getNextElement());
        $this->assertEquals('next', $element->getNextElement()->getData());
        
        $element->nullNextElement();
        
        $this->assertEquals(null, $element->getNextElement());
        $this->assertEquals('next', $next->getData());
        
    }
    
    /**
     * @depends testSetPreviousElement
     * 
     */
    public function testNullPreviousElement() {
        $element = Types\LinkedListElement::buildElement('testdata');
        $prev = Types\LinkedListElement::buildElement('previous');
        
        $element->setPreviousElement($prev);
        
        $this->assertEquals('testdata', $element->getData());
        $this->assertEquals($prev, $element->getPreviousElement());
        $this->assertEquals('previous', $element->getPreviousElement()->getData());
        
        $element->nullPreviousElement();
        
        $this->assertEquals(null, $element->getPreviousElement());
        $this->assertEquals('previous', $prev->getData());
        
    }
    
    public function testDebugInfo() {
        $prevelement = Types\LinkedListElement::buildElement('previous');
        $nextelement = Types\LinkedListElement::buildElement('next');
        $element = Types\LinkedListElement::buildElement('testdata', $prevelement, $nextelement);
        
        ob_start();
        
        var_dump($element);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?data\"?\]?.*=\\>\n.*string\\(8\\)/", $output);
        $this->assertRegExp("/\\[?\"?previous\"?\]?.*=\\>\n.*(object|class).*\\(?Phabstractic\\\\Data\\\\Types\\\\LinkedListElement\\)?/", $output);
        $this->assertRegExp("/\\[?\"?next\"?\]?.*=\\>\n.*(object|class).*\\(?Phabstractic\\\\Data\\\\Types\\\\LinkedListElement\\)?/", $output);
        
    }
    
}
