<?php
require_once('src/Phabstractic/Patterns/Prototype.php');
require_once('src/Phabstractic/Data/Types/Map.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Patterns;
use Phabstractic\Data\Types;

class PrototypeTest extends TestCase
{
    public function testInstantiation()
    {
        $prototype = new Patterns\Prototype();
        
        $this->assertInstanceOf(Patterns\Prototype::class, $prototype);
        $this->assertInstanceOf(\StdClass::class, $prototype);
        
    }
    
    public function testInstantiationWithPrefix()
    {
        $prototype = new Patterns\Prototype(array('prefix' => 'CustomPrefix_'));
        
        $this->assertInstanceOf(Patterns\Prototype::class, $prototype);
        $this->assertInstanceOf(\StdClass::class, $prototype);
        
        $this->assertEquals(0, strpos($prototype->getIdentifier(), 'CustomPrefix_'));
        
    }
    
    public function testSetRegistry()
    {
        $map = new Types\Map();
        Patterns\Prototype::setRegistry($map);
        
        $prototype = new Patterns\Prototype();
        
        Patterns\Prototype::addToRegistry($prototype);
        
        $this->assertEquals(array($prototype->getIdentifier()), $map->getKeys());
        
        $map = new \ArrayObject();
        
        Patterns\Prototype::setRegistry($map);
    }
    
    public function testAddToRegistry()
    {
        $prototype = new Patterns\Prototype();
        
        Patterns\Prototype::addToRegistry($prototype);
        
        $this->assertEquals($prototype, Patterns\Prototype::getFromRegistry($prototype->getIdentifier()));
        
    }
    
    /**
     * @depends testAddToRegistry
     * 
     */
    public function testRemoveFromRegistry()
    {
        $prototype = new Patterns\Prototype();
        
        Patterns\Prototype::addToRegistry($prototype);
        
        $this->assertEquals($prototype, Patterns\Prototype::getFromRegistry($prototype->getIdentifier()));
        
        Patterns\Prototype::removeFromRegistry($prototype->getIdentifier());
        
        $this->assertEquals(null, Patterns\Prototype::getFromRegistry($prototype->getIdentifier()));
        
    }
    
    public function testGetProperties()
    {
        $prototype1 = new Patterns\Prototype();
        $prototype2 = Patterns\Prototype::fromPrototype($prototype1);
        
        $prototype1->newProperty = 'test';
        $prototype2->anotherProperty = 'anothertest';
        
        $this->assertEquals('test', $prototype2->newProperty);
        $this->assertEquals('test', $prototype1->newProperty);
        $this->assertEquals('anothertest', $prototype2->anotherProperty);
        $this->assertEquals(null, $prototype1->unknownProperty);
        $this->assertEquals(null, $prototype2->unknownProperty);
        
    }
    
    /**
     * @expectedException Phabstractic\Patterns\Exception\RuntimeException
     * 
     */
    public function testGetPropertiesWithStrict()
    {
        $prototype1 = new Patterns\Prototype(array('strict' => true));
        $prototype2 = Patterns\Prototype::fromPrototype($prototype1, array('strict' => true));
        
        $this->assertEquals(null, $prototype1->unknownProperty);
        
    }
    
    /**
     * @expectedException Phabstractic\Patterns\Exception\RuntimeException
     * 
     */
    public function testGetMorePropertiesWithStrict()
    {
        $prototype1 = new Patterns\Prototype(array('strict' => true));
        $prototype2 = Patterns\Prototype::fromPrototype($prototype1, array('strict' => true));
        
        $this->assertEquals(null, $prototype2->unknownProperty);
        
    }
    
    public function testCallMethods()
    {
        $testvar = 0;
        
        $prototype1 = new Patterns\Prototype();
        $prototype2 = Patterns\Prototype::fromPrototype($prototype1);
        
        $prototype1->newMethod = function () use (&$testvar) {$testvar = 1;};
        $prototype2->anotherMethod = function () use (&$testvar) {$testvar = 2;};
        
        $prototype2->newMethod();
        
        $this->assertEquals(1, $testvar);
        
        $prototype2->anotherMethod();
        
        $this->assertEquals(2, $testvar);
        
        $prototype1->unknownMethod();
        
    }
    
    /**
     * @expectedException Phabstractic\Patterns\Exception\RuntimeException
     * 
     */
    public function testCallMethodsWithStrict()
    {
        $prototype1 = new Patterns\Prototype(array('strict' => true));
        $prototype2 = Patterns\Prototype::fromPrototype($prototype1, array('strict' => true));
        
        $prototype2->unknownMethod();
        
    }
    
    /**
     * @expectedException \LogicException
     * 
     */
    public function testThrowException()
    {
        $prototype1 = new Patterns\Prototype();
        $prototype2 = Patterns\Prototype::fromPrototype($prototype1);
        
        $prototype1->newMethod = function () {throw new \LogicException();};
        
        $prototype2->newMethod();
        
    }
    
    /**
     * @expectedException \LogicException
     * 
     */
    public function testThrowMoreException()
    {
        $prototype1 = new Patterns\Prototype();
        $prototype2 = Patterns\Prototype::fromPrototype($prototype1);
        
        $prototype1->newMethod = function () {throw new \LogicException();};
        
        $prototype1->newMethod();
        
    }
    
    public function testDebugInfo() {
        $prototype1 = new Patterns\Prototype();
        $prototype2 = Patterns\Prototype::fromPrototype($prototype1);
        
        ob_start();
        
        var_dump($prototype2);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?options\"?\]?.*=\\>\n.*array\\(2\\)/", $output);
        $this->assertRegExp("/\\[?\"?strict\"?\]?.*=\\>/", $output);
        $this->assertRegExp("/\\[?\"?prefix\"?\]?.*=\\>/", $output);
        $this->assertRegExp("/\\[?\"?properties\"?\]?.*=\\>\n.*array\\(2\\)/", $output);
        $this->assertRegExp("/\\[?\"?identity\"?\]?.*=\\>\n.*string/", $output);
    }
    
}
