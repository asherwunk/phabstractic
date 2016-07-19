<?php

require_once('src/Phabstractic/Data/Types/Enumeration.php');
require_once('src/Phabstractic/Data/Types/Exception/CodeGenerationException.php');
require_once('src/Phabstractic/Data/Types/Exception/RuntimeException.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Exception;

Types\Enumeration::createEnumerator('TestEnumeration', array( 'RED',
                                        'GREEN',
                                        'BLUE',
                                        'YELLOW',
                                        'ORANGE',),
                                    array('namespace' => 'EnumerationTests' ) );

Types\Enumeration::createEnumerator('TestEnumerationWithDefault', array( 'RED',
                                        'GREEN',
                                        'BLUE',
                                        'YELLOW',
                                        'ORANGE',),
                                    array('namespace' => 'EnumerationTests',
                                          'default' => 'YELLOW',) );

class EnumerationTest extends TestCase
{
    
    public function testInstantiationWithoutConfig()
    {
        $enum = new Types\Enumeration('TestEnum', array('ONE'=>1,'TWO'=>2));
        
        $this->assertInstanceOf(Types\Enumeration::class, $enum);
    }
    
    public function testInstantiationWithConfig()
    {
        $enum = new Types\Enumeration(
            'TestEnum',
            array('ONE','TWO'),
            array('default'=>'ONE',
                  'namespace'=>'TestNamespace',)
        );
        
        $this->assertInstanceOf(Types\Enumeration::class, $enum);
        $this->assertEquals($enum->getDefault(), 'ONE');
        
        return $enum;
        
    }
    
    public function testInstantiationWithBake() {
        $enum = new Types\Enumeration(
            'TestImmediateBake',
            array('RED','BLUE','GREEN',),
            array('default'=>'BLUE',
                  'namespace'=>'TestNamespace',
                  'bake'=>true,)
        );
        
        $this->assertTrue(class_exists('\\TestNamespace\\TestImmediateBake'));
        
    }
    
    /**
     * @depends testInstantiationWithConfig
     *
     */
    public function testClassnameAccessors($enum) {
        $this->assertEquals('TestEnum', $enum->getClassName());
        $enum->setClassName('ModifiedEnum');
        $this->assertEquals('ModifiedEnum', $enum->getClassName());
    }
    
    /**
     * @depends testInstantiationWithConfig
     * 
     */
    public function testConstants($enum) {
        $this->assertEmpty(array_diff(array('ONE','TWO'), $enum->getConstants()));
        $enum->addConstant('THREE');
        $this->assertEmpty(array_diff(array('ONE','TWO','THREE'), $enum->getConstants()));
        $enum->addConstants(array('FOUR','TWO'));
        $this->assertEmpty(array_diff(array('ONE','TWO','THREE','FOUR'), $enum->getConstants()));
        $enum->removeConstant('TWO');
        $this->assertEmpty(array_diff(array('ONE','THREE','FOUR'), $enum->getConstants()));
        $enum->setConstants(array('SIX','SEVEN','EIGHT'));
        $this->assertEmpty(array_diff(array('SIX','SEVEN','EIGHT'), $enum->getConstants()));
        
        return $enum;
        
    }
    
    /**
     * @depends testConstants
     * 
     */
    public function testDefaultAccessors($enum) {
        $enum->setDefault('THREE'); // this doesn't exist
        $this->assertEquals('', $enum->getDefault());
        $enum->setDefault('SEVEN');
        $this->assertEquals('SEVEN', $enum->getDefault());
        
        return $enum;
        
    }
    
    /**
     * @depends testDefaultAccessors
     * 
     */
    public function testNamespaceAccessors($enum) {
        $this->assertEquals('TestNamespace', $enum->getNamespace());
        $enum->setNamespace('ModifiedNamespace');
        $this->assertEquals('ModifiedNamespace', $enum->getNamespace());
        
    }
    
    public function testEnumerationBake() {
        $enum = new Types\Enumeration(
            'TestEnumBake',
            array('ONE','TWO','RED'),
            array('default'=>'ONE',
                  'namespace'=>'TestNamespace',)
        );
        
        $enum->bake();
        
        $this->assertTrue(class_exists('\\TestNamespace\\TestEnumBake'));
        
        return $enum;
    }
    
    /**
     * @depends testEnumerationBake
     * @expectedException \Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testEnumerationRebake($enum)
    {
        $enum = new Types\Enumeration(
            'TestEnumBake',
            array('ONE','TWO','RED'),
            array('default'=>'RED',
                  'namespace'=>'TestNamespace',)
        );
        
        $enum->bake();
        
    }
    
    public function testEnumerationInstance() {
        $enum = new Types\Enumeration(
            'TestEnumInstance',
            array('ONE','TWO','BLUE'),
            array('default'=>'ONE',
                  'namespace'=>'TestNamespace',
                  'bake'=>true)
        );
        
        $enum2 = new Types\Enumeration(
            'TestEnumInstanceBake',
            array('THREE','FOUR','RED','BLUE'),
            array('default'=>'THREE',
                  'namespace'=>'TestNamespace')
        );
        
        $value = $enum->getInstance('TWO');
        $this->assertEquals(\TestNamespace\TestEnumInstance::TWO, $value->get());
        
        $value = $enum2->getInstance('RED');
        $this->assertEquals(\TestNamespace\TestEnumInstanceBake::RED, $value->get());
        
        $value = $enum2->getInstance('FOUR');
        $this->assertEquals(\TestNamespace\TestEnumInstanceBake::FOUR, $value->get());
        
        return $enum2;
    }
    
    /**
     * @depends testEnumerationInstance
     * @expectedException \UnexpectedValueException
     *
     */
    public function testEnumerationInstanceFalseValue($enum) {
        $value = $enum->getInstance('ONE');
        
    }
    
    /**
     * @depends testEnumerationInstance
     * @expectedException \Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testClassnameAfterBaked($enum) {
        $enum->setClassname('AlreadyBakedTest');
        
    }
    
    /**
     * @depends testEnumerationInstance
     * @expectedException \Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testSetConstantsAfterBaked($enum) {
        $enum->setConstants(array('BLAH'));
        
    }
    
    /**
     * @depends testEnumerationInstance
     * @expectedException \Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testAddConstantAfterBaked($enum) {
        $enum->addConstant('BLAH');
        
    }
    
    /**
     * @depends testEnumerationInstance
     * @expectedException \Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testAddConstantsAfterBaked($enum) {
        $enum->addConstants(array('BLAH'));
        
    }
    
    /**
     * @depends testEnumerationInstance
     * @expectedException \Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testRemoveConstantAfterBaked($enum) {
        $enum->removeConstant('FOUR');
        
    }
    
    /**
     * @depends testEnumerationInstance
     * @expectedException \Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testSetDefaultAfterBaked($enum) {
        $enum->setDefault('FOUR');
        
    }
    
    /**
     * @depends testEnumerationInstance
     * @expectedException \Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testSetNamespaceAfterBaked($enum) {
        $enum->setNamespace('BadNamespace');
        
    }
    
    /**
     * @depends testEnumerationInstance
     * 
     */
    public function testBaked($enum) {
        $this->assertTrue($enum->isBaked());
        
    }
    
    /**
     * @depends testEnumerationInstance
     * 
     */
    public function testCreateEnumeration($enum) {
        $value = Types\Enumeration::createEnumeration('TestNamespace\\TestEnumInstanceBake', 'RED');
        $this->assertEquals(\TestNamespace\TestEnumInstanceBake::RED, $value->get());
    }
    
    public function testCreateEnumerator() {
        Types\Enumeration::createEnumerator(
            'TestStaticEnumBake',
            array('GOOGLE','YAHOO','MSN'),
            array('default'=>'GOOGLE',
                  'namespace'=>'TestNamespace')
        );
        
        $this->assertTrue(class_exists('\\TestNamespace\\TestStaticEnumBake'));
    }
    
    /**
     * @depends testEnumerationInstance
     */
    public function testEnumerationClassDebugInfo($enum) {
        ob_start();
        
        var_dump($enum);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?default\"?\]?.*=\\>\n.*string\\(5\\).*\"THREE\"/", $output);
    }
    
    // GENERATED CODE TESTED HERE
    
    /**
     * @expectedException \UnexpectedValueException
     * 
     */
    public function testInstantiateImproperEnumerationElementInt() {
        $e = new EnumerationTests\TestEnumeration(256);
        
    }
    
    public function testInstantiateProperEnumerationElementInt() {
        $e = new EnumerationTests\TestEnumeration(EnumerationTests\TestEnumeration::RED);
        
    }
    
    public function testInstantiateProperEnumerationElementString() {
        $e = new EnumerationTests\TestEnumeration('GREEN');
    }
    
    /**
     * @expectedException \UnexpectedValueException
     * 
     */
    public function testInstantiateImproperEnumerationElementString() {
        $e = new EnumerationTests\TestEnumeration('BLACK');
    }
    
    
    public function testInstantiateEnumerationElementWithDefault() {
        $e = new EnumerationTests\TestEnumerationWithDefault();
        
        $this->assertEquals($e->get(), EnumerationTests\TestEnumerationWithDefault::YELLOW);
    }
    
    public function testEnumerationSetProperString() {
        $e = new EnumerationTests\TestEnumerationWithDefault();
        
        $e->set('BLUE');
        
        $this->assertEquals($e->get(), EnumerationTests\TestEnumerationWithDefault::BLUE);
    }
    
    public function testEnumerationSetProperInt() {
        $e = new EnumerationTests\TestEnumerationWithDefault();
        
        $e->set(EnumerationTests\TestEnumerationWithDefault::BLUE);
        
        $this->assertEquals($e->get(), EnumerationTests\TestEnumerationWithDefault::BLUE);
    }
    
    /**
     * @expectedException \UnexpectedValueException
     * 
     */
    public function testEnumerationSetImproperString() {
        $e = new EnumerationTests\TestEnumerationWithDefault();
        
        $e->set('BLACK');
    }
    
    /**
     * @expectedException \UnexpectedValueException
     * 
     */
    public function testEnumerationSetImproperInt() {
        $e = new EnumerationTests\TestEnumerationWithDefault();
        
        $e->set(256);
    }
    
    public function testEnumerationCount() {
        $e = new EnumerationTests\TestEnumerationWithDefault();
        
        $this->assertEquals(5, $e->count());
        
    }
    
    public function testEnumerationGetConstants() {
        $consts = EnumerationTests\TestEnumerationWithDefault::getConstants();
        
        $this->assertEquals(array('RED'=>0,
                                  'GREEN'=>1,
                                  'BLUE'=>2,
                                  'YELLOW'=>3,
                                  'ORANGE'=>4,
                                  '__default'=>3,), $consts);
                                  
    }
    
    // since 3.0.2
    
    public function testEnumerationGetConst() {
        $e = new EnumerationTests\TestEnumerationWithDefault();
        
        $this->assertEquals('YELLOW', $e->getConst());
        
        $e->set('GREEN');
        
        $this->assertEquals('GREEN', $e->getConst());
        
        $e->set(EnumerationTests\TestEnumerationWithDefault::RED);
        
        $this->assertEquals('RED', $e->getConst());
        
    }
    
    public function testEnumerationDebugInfo() {
        $e = new EnumerationTests\TestEnumerationWithDefault();
        
        ob_start();
        
        var_dump($e);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?value\"?\]?.*=\\>\n.*string\\(6\\).*\"YELLOW\"/", $output);
    }
    
}
