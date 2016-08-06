<?php

require_once('src/Phabstractic/Data/Types/Range.php');
require_once('src/Phabstractic/Data/Types/Exception/InvalidArgumentException.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;

class RangeTest extends TestCase
{
    
    public function testProperConstruction()
    {
        $range = new Types\Range(1, 5);
        
        $this->assertInstanceOf(Types\Range::class, $range);
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testInvalidArgumentConstruction()
    {
        $range = new Types\Range('min', 'max');
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testInvalidConstruction()
    {
        $range = new Types\Range(6, 1);
    }
    
    public function testAccessors()
    {
        $range = new Types\Range(1, 7);
        
        $range->setMinimum(2);
        
        $this->assertEquals($range->getMinimum(), 2);
        
        $range->setMaximum(6);
        
        $this->assertEquals($range->getMaximum(), 6);
        
    }
    
    /**
     * @depends testAccessors
     * @expectedException \Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperMinimum() {
        $range = new Types\Range(1, 7);
        
        $range->setMinimum(8);
    }
    
    /**
     * @depends testAccessors
     * @expectedException \Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperMaximum() {
        $range = new Types\Range(3, 7);
        
        $range->setMaximum(2);
    }

    /**
     * @depends testProperConstruction
     * 
     */
    public function testIsInRange() {
        $range = new Types\Range(3,7);
        
        $this->assertTrue($range->isInRange(5));
        $this->assertFalse($range->isInRange(8));
        $this->assertFalse($range->isInRange(3));
        $this->assertTrue($range->isInRange(3, array('minimum'=>true)));
        $this->assertTrue($range->isInRange(7, array('maximum'=>true)));
        $this->assertTrue($range->isInRange(4, array('minimum'=>true,'maximum'=>true)));
        
    }
    
    public function testDebugInfo() {
        $range = new Types\Range(1, 5);
        
        ob_start();
        
        var_dump($range);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?max\"?\]?.*=\\>\n.*int\\(5\\)/", $output);
    }
    
}
