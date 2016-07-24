<?php

require_once('src/Phabstractic/Data/Types/Priority.php');
require_once('src/Phabstractic/Data/Types/Resource/PriorityInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Resource as TypesResource;

class PriorityTest extends TestCase
{
    public function testBasicInstantiation() {
        $data = 'testdata';
        
        $priority = new Types\Priority($data);
        
        $this->assertInstanceOf(Types\Priority::class, $priority);
        $this->assertInstanceOf(TypesResource\PriorityInterface::class, $priority);
        $this->assertEquals('testdata', $priority->getData());
    }
    
    public function testPrioritizedInstantiation() {
        $data = 'testdata';
        
        $priority = new Types\Priority($data, 6);
        
        $this->assertInstanceOf(Types\Priority::class, $priority);
        $this->assertEquals('testdata', $priority->getData());
        $this->assertEquals(6, $priority->getPriority());
    }
    
    public function testSetPriority() {
        $data = 'testdata';
        
        $priority = new Types\Priority($data, 6);
        
        $this->assertInstanceOf(Types\Priority::class, $priority);
        $this->assertEquals('testdata', $priority->getData());
        $this->assertEquals(6, $priority->getPriority());
        
        $priority->setPriority(42);
        
        $this->assertEquals(42, $priority->getPriority());
        
    }
    
    public function testGetDataReference() {
        $data = 'testdata';
        
        $priority = new Types\Priority($data, 6);
        
        $this->assertInstanceOf(Types\Priority::class, $priority);
        $this->assertEquals('testdata', $priority->getData());
        $this->assertEquals(6, $priority->getPriority());
        
        $testref =&$priority->getDataReference();
        
        $testref = 'modified';
        
        $this->assertEquals('modified', $priority->getData());
    }
    
    public function testBuildPriority() {
        $priority = Types\Priority::buildPriority('testdata', 6);
        
        $this->assertInstanceOf(Types\Priority::class, $priority);
        $this->assertEquals('testdata', $priority->getData());
        $this->assertEquals(6, $priority->getPriority());
    }
    
    public function testDebugInfo() {
        $data = 'testdata';
        
        $priority = new Types\Priority($data, 6);
        
        ob_start();
        
        var_dump($priority);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?priority\"?\]?.*=\\>\n.*int\\(6\\)/", $output);
    }
    
}
