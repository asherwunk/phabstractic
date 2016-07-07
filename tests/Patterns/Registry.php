<?php
require_once('src/Patterns/Registry.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Patterns;

class RegistryTest extends TestCase
{
    public function testInstantiation() {
        if (!Patterns\Registry::hardened()) {
            $registry = Patterns\Registry::instantiate(array('testkey'=>'testdata'));
        } else {
            $registry = Patterns\Registry::instantiate();
            $registry['testkey'] = 'testdata';
        }
        
        $this->assertInstanceOf(Patterns\Registry::class, $registry);
        
        return $registry;
    }
    
    /**
     * @depends testInstantiation
     * @expectedException \Phabstractic\Patterns\Exception\RuntimeException
     * 
     */
    public function testDoubleInstantiation() {
        $registry = Patterns\Registry::instantiate(array('errorkey'=>'errorvalue'));
    }
    
    /**
     * @depends testInstantiation
     * 
     */
    public function testRetrieval($registry) {
        $test = $registry->get('testkey');
        
        $this->assertEquals($test, 'testdata');
        
        $testref =& $registry->getReference('testkey');
        
        $this->assertEquals($test, 'testdata');
        
        $testref = 'referenceddata';
        
        $this->assertEquals($registry->get('testkey'), 'referenceddata');
        
        $testref = 'testdata';
        
    }
    
    /**
     * @depends testInstantiation
     *
     */
    public function testSetting($registry) {
        $registry->set('newkey', 'newvalue');
        
        $this->assertEquals($registry->get('newkey'), 'newvalue');
        
        $testref = 'referenceddata';
        
        $registry->setReference('testref', $testref);
        
        $testref = 'toucheddata';
        
        $this->assertEquals($registry->get('testref'), 'toucheddata');
        
    }
    
    /**
     * @depends testInstantiation
     * 
     */
    public function testArrayFunctionality($registry) {
        $this->assertEquals($registry['testkey'], 'testdata');
        
        $registry['testkey'] = 'modifieddata';
        
        $this->assertEquals($registry['testkey'], 'modifieddata');
    }
    
    /**
     * @depends testInstantiation
     * @expectedException \Phabstractic\Patterns\Exception\RangeException
     * 
     */
    public function testArrayRange($registry) {
        $val = $registry['unknownkey'];
    }
    
    /**
     * @expectedException Error
     * 
     */
    public function testClone()
    {
        $registry = Patterns\Registry::instantiate();
        $registryclone = clone $registry;
    }
    
    
    public static function tearDownAfterClass()
    {
        $registry = Patterns\Registry::instantiate();
        unset($registry['testkey']);
        unset($registry['newkey']);
        unset($registry['testref']);
    }
}
