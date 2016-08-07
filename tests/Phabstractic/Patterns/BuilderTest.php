<?php
namespace TestBuilderNamespace;

require_once('src/Phabstractic/Patterns/Builder.php');
require_once('src/Phabstractic/Patterns/Resource/BuilderInterface.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Patterns;
use Phabstractic\Patterns\Resource as PatternsResource;
use Phabstractic\Features\Resource as FeaturesResource;

Patterns\Builder::buildBuilder(
    'testBuilder',
    array('methodOne', 'methodTwo'),
    array('namespace' => 'TestBuilderNamespace')
);

class TestBuilder extends AbstractTestBuilderBuildable {
    public function setMethodOne($method) {
        
    }
    
    public function setMethodTwo($method) {
        
    }
}

class BuilderTest extends TestCase
{
    public function testEmptyInstantiation() {
        $f = new Patterns\Builder('test', array(), array('strict' => true, 'namespace' => 'TestBuilderNamespace'));
        
        $this->assertInstanceOf(Patterns\Builder::class, $f);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $f);
        
        return $f;
    }
    
    public function testPopulatedInstantiation() {
        $f = new Patterns\Builder('test',
                                  array('methodOne', 'methodTwo'));
        
        $this->assertInstanceOf(Patterns\Builder::class, $f);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $f);
        $this->assertEquals(array('methodOne', 'methodTwo'), $f->getMethods());
        
    }
    
    public function testWithNamespaceInstantiation() {
        $f = new Patterns\Builder('test',
                                  array('methodOne', 'methodTwo'),
                                  array('namespace' => 'unittest'));
        
        $this->assertInstanceOf(Patterns\Builder::class, $f);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $f);
        $this->assertEquals(array('methodOne', 'methodTwo'), $f->getMethods());
        $this->assertEquals('unittest', $f->getNamespace());
    }
    
    /**
     * @depends testEmptyInstantiation
     * 
     */
    public function testProperBuilderName($builder) {
        $builder->setBuilderName('anotherTestBuilder');
        
        $this->assertEquals('anotherTestBuilder', $builder->getBuilderName());
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * @expectedException Phabstractic\Patterns\Exception\RangeException
     * 
     */
    public function testImproperBuilderName($builder) {
        $builder->setBuilderName('testBuilder');
        
        exit();
        
    }
    
    public function testBaked() {
        $b = new Patterns\Builder('testBuilderTwo',
                                  array('methodOne', 'methodTwo'),
                                  array('namespace' => 'unittest'));
        
        $this->assertFalse($b->isBaked());
        
        $b->bake();
        
        $this->assertTrue($b->isBaked());
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * 
     */
    public function testSetMethods($builder) {
        $builder->setMethods(array('methodOne', 'methodTwo', 'methodThree'));
        
        $this->assertEquals(array('methodOne', 'methodTwo', 'methodThree'), $builder->getMethods());
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testSetImproperMethods($builder) {
        $builder->setMethods(array('methodOne', 'methodTwo', 'methodTwo'));
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * 
     */
    public function testAddProperMethod($builder) {
        $builder->addMethod('additionalMethod');
        
        $this->assertEquals(array('methodOne', 'methodTwo', 'methodThree', 'additionalMethod'), $builder->getMethods());
    }
    
    /**
     * @depends testEmptyInstantiation
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testAddImproperMethod($builder) {
        $builder->addMethod('methodOne');
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * 
     */
    public function testAddProperMethods($builder) {
        $builder->addMethods(array('anotherMethodOne', 'anotherMethodTwo'));
        
        $this->assertEquals(array('methodOne', 'methodTwo', 'methodThree', 'additionalMethod',
                                  'anotherMethodOne', 'anotherMethodTwo'), $builder->getMethods());
    }
    
    /**
     * @depends testEmptyInstantiation
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     * NOTE:  The error stops anotherMethodTwo, not uniqueMethod  NOT ATOMIC!
     */
    public function testAddImproperMethods($builder) {
        $builder->addMethods(array('uniqueMethod', 'anotherMethodTwo'));
        
    }
    
    /**
     * @depends testEmptyInstantiation
     * 
     */
    public function testRemoveMethod($builder) {
        $builder->removeMethod('methodOne');
        
        $this->assertEquals(array('methodTwo', 'methodThree', 'additionalMethod',
                                  'anotherMethodOne', 'anotherMethodTwo', 'uniqueMethod'), $builder->getMethods());
    }
    
    /**
     * @depends testEmptyInstantiation
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testNonexistentRemoveMethod($builder) {
        $builder->removeMethod('nonExistingMethod');
        
    }
    
    public function testSetProperNamespace() {
        $b = new Patterns\Builder('testBuilderThree',
                                  array('methodOne', 'methodTwo'),
                                  array('namespace' => ''));
        
        $b->setNamespace('testnamespace');
        
        $this->assertEquals('testnamespace', $b->getNamespace());
        
    }
    
    /**
     * @expectedException Phabstractic\Patterns\Exception\RangeException
     * 
     */
    public function testSetImproperNamespace() {
        $b = new Patterns\Builder('testBuilderTwo',
                                  array('methodOne', 'methodTwo'),
                                  array('namespace' => '', 'strict' => true));
        
        $b->setNamespace('unittest');
        
    }
    
    public function testBuildBuilder() {
        Patterns\Builder::buildBuilder('staticFunctionTest',
                                       array('methodOne', 'methodTwo'),
                                       array('namespace' => 'unittest'));
        
        $this->assertTrue(class_exists('unittest\\AbstractStaticFunctionTestBuildable'));
        
    }
    
    public function testGeneratedInstantiation() {
        $test = new TestBuilder();
        
        $this->assertInstanceOf(TestBuilderBuildableInterface::class, $test);
        $this->assertInstanceOf(AbstractTestBuilderBuildable::class, $test);
        
        $builder = new TestBuilderBuilder();
        
        $this->assertInstanceOf(PatternsResource\BuilderInterface::class, $builder);
        
        $obj = $builder->setMethodOne(5)->setMethodTwo(6)->getBuiltObject();
        
        $this->assertInstanceOf(TestBuilder::class, $obj);
        
    }
    
    public function testSetDebugInfo() {
        $b = new Patterns\Builder('TestBuilderTwo',
                                  array('methodOne', 'methodTwo', 'methodThree'),
                                  array('namespace' => ''));
        
        ob_start();
        
        var_dump($b);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?methods\"?\]?.*=\\>\n.*array\\(3\\)/", $output);

    }
    
}
