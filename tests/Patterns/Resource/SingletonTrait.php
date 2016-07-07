<?php
require_once('src/Patterns/Resource/SingletonTrait.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Patterns\Resource as Patterns;

class TestSingleton {
    use Patterns\SingletonTrait;
    
    public $configuration;
    
    protected function init() {
        $args = func_get_args();
        $this->configuration = $args[0];
    }
}

class SingletonTraitTest extends TestCase
{
    public function testInstantiation() {
        $this->assertFalse(TestSingleton::hardened());
        
        $singleton = TestSingleton::instantiate('conf');
        
        $this->assertInstanceOf(TestSingleton::class, $singleton);
        $this->assertEquals($singleton->configuration, 'conf');
        
        $this->assertTrue(TestSingleton::hardened());
    }
    
    /**
     * @depends testInstantiation
     * @expectedException \Phabstractic\Patterns\Exception\RuntimeException
     * 
     */
    public function testDoubleInstantiation() {
        $singleton = TestSingleton::instantiate('error');
    }
    
    /**
     * @expectedException Error
     * 
     */
    public function testConstruction()
    {
        $singleton = new TestSingleton();
    }
    
    /**
     * @expectedException Error
     */
    public function testClone()
    {
        $singleton = TestSingleton::instantiate();
        $singletonclone = clone $singleton;
    }
}
