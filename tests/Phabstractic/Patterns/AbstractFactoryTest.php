<?php
require_once('src/Phabstractic/Patterns/AbstractFactory.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Patterns;
use Phabstractic\Features\Resource as FeaturesResource;

Patterns\AbstractFactory::buildAbstractFactory(
    'unitTest',
    array('plain', 'fancy', 'special'),
    array('ACONSTANT' => 5)
);

class TestAbstractFactory extends AbstractUnitTestFactory {
    public function makePlain() {
        
    }
    
    public function makeFancy() {
        
    }
    
    public function makeSpecial() {
        
    }
}

class AbstractFactoryTest extends TestCase
{
    public function testEmptyInstantiation() {
        $f = new Patterns\AbstractFactory('test');
        
        $this->assertInstanceOf(Patterns\AbstractFactory::class, $f);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $f);
        
        return $f;
    }
    
    public function testPopulatedInstantiation() {
        $f = new Patterns\AbstractFactory('test',
                                                  array('red', 'blue', 'yellow'),
                                                  array('color' => 2));
        
        $this->assertInstanceOf(Patterns\AbstractFactory::class, $f);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $f);
    }
    
    public function testWithNamespaceInstantiation() {
        $f = new Patterns\AbstractFactory('test',
                                                  array('red', 'blue', 'yellow'),
                                                  array('color' => 2),
                                                  array('namespace' => 'unittest'));
        
        $this->assertInstanceOf(Patterns\AbstractFactory::class, $f);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $f);
    }
    
    /**
     * @depends testEmptyInstantiation
     * 
     */
    public function testProperFactoryName($factory) {
        $factory->setFactoryName('anotherTestFactory');
        
        $this->assertEquals('anotherTestFactory', $factory->getFactoryName());
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * @expectedException Phabstractic\Patterns\Exception\RangeException
     * 
     */
    public function testImproperFactoryName($factory) {
        $factory->setFactoryName('unitTest');
        
        
    }
    
    public function testBaked() {
        $f = new Patterns\AbstractFactory('unitTestTwo',
                                                  array('red', 'blue', 'yellow'),
                                                  array('color' => 2),
                                                  array('namespace' => 'unittest'));
        
        $this->assertFalse($f->isBaked());
        
        $f->bake();
        
        $this->assertTrue($f->isBaked());
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * 
     */
    public function testSetConstants($factory) {
        $factory->setConstants(array('one' => 1, 'two' => 2, 'three' => 3));
        
        $this->assertEquals(array('one'=>1,'two'=>2,'three'=>3), $factory->getConstants());
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * 
     */
    public function testAddConstant($factory) {
        $factory->addConstant('four', 4);
        
        $this->assertEquals(array('one'=>1,'two'=>2,'three'=>3,'four'=>4), $factory->getConstants());
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * 
     */
    public function testAddConstants($factory) {
        $factory->addConstants(array('one'=>5,'five'=>6));
        
        $this->assertEquals(array('one'=>5,'two'=>2,'three'=>3,'four'=>4,'five'=>6), $factory->getConstants());
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * 
     */
    public function testRemoveConstant($factory) {
        $factory->removeConstant('five');
        
        $this->assertEquals(array('one'=>5,'two'=>2,'three'=>3,'four'=>4), $factory->getConstants());
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * 
     */
    public function testSetMethods($factory) {
        $factory->setMethods(array('methodOne', 'methodTwo', 'methodThree'));
        
        $this->assertEquals(array('methodOne', 'methodTwo', 'methodThree'), $factory->getMethods());
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testSetImproperMethods($factory) {
        $factory->setMethods(array('methodOne', 'methodTwo', 'methodTwo'));
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * 
     */
    public function testAddProperMethod($factory) {
        $factory->addMethod('additionalMethod');
        
        $this->assertEquals(array('methodOne', 'methodTwo', 'methodThree', 'additionalMethod'), $factory->getMethods());
    }
    
    /**
     * @depends testEmptyInstantiation
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testAddImproperMethod($factory) {
        $factory->addMethod('methodOne');
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * 
     */
    public function testAddProperMethods($factory) {
        $factory->addMethods(array('anotherMethodOne', 'anotherMethodTwo'));
        
        $this->assertEquals(array('methodOne', 'methodTwo', 'methodThree', 'additionalMethod',
                                  'anotherMethodOne', 'anotherMethodTwo'), $factory->getMethods());
    }
    
    /**
     * @depends testEmptyInstantiation
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     * NOTE:  The error stops anotherMethodTwo, not uniqueMethod  NOT ATOMIC!
     */
    public function testAddImproperMethods($factory) {
        $factory->addMethods(array('uniqueMethod', 'anotherMethodTwo'));
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * 
     */
    public function testRemoveMethod($factory) {
        $factory->removeMethod('methodOne');
        
        $this->assertEquals(array('methodTwo', 'methodThree', 'additionalMethod',
                                  'anotherMethodOne', 'anotherMethodTwo', 'uniqueMethod'), $factory->getMethods());
    }
    
    /**
     * @depends testEmptyInstantiation
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testNonexistentRemoveMethod($factory) {
        $factory->removeMethod('nonExistingMethod');
        
    }
    
    public function testSetProperNamespace() {
        $f = new Patterns\AbstractFactory('unitTestTwo',
                                                  array('red', 'blue', 'yellow'),
                                                  array('color' => 2),
                                                  array('namespace' => ''));
        
        $f->setNamespace('testnamespace');
        
        $this->assertEquals('testnamespace', $f->getNamespace());
        
    }
    
    /**
     * @expectedException Phabstractic\Patterns\Exception\RangeException
     * 
     */
    public function testSetImproperNamespace() {
        $f = new Patterns\AbstractFactory('unitTestTwo',
                                                  array('red', 'blue', 'yellow'),
                                                  array('color' => 2),
                                                  array('namespace' => ''));
        
        $f->setNamespace('unittest');
        
    }
    
    public function testBuildAbstractFactory() {
        Patterns\AbstractFactory::buildAbstractFactory('staticFunctionTest',
                                                  array('red', 'blue', 'yellow'),
                                                  array('color' => 2),
                                                  array('namespace' => 'unittest'));
        
        $this->assertTrue(class_exists('unittest\\AbstractStaticFunctionTestFactory'));
        
    }
    
    public function testGeneratedInstantiation() {
        $f = new TestAbstractFactory();
        
        $this->assertEquals(array('makePlain', 'makeFancy', 'makeSpecial'),
                            get_class_methods($f));
    }
    
    public function testSetDebugInfo() {
        $f = new Patterns\AbstractFactory('unitTestTwo',
                                                  array('red', 'blue', 'yellow'),
                                                  array('color' => 2),
                                                  array('namespace' => ''));
        
        ob_start();
        
        var_dump($f);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?methods\"?\]?.*=\\>\n.*array\\(3\\)/", $output);

    }
    
}
