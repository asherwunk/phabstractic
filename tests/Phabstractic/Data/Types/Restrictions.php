<?php

require_once('src/Phabstractic/Data/Types/Restrictions.php');
require_once('src/Phabstractic/Data/Types/Type.php');
require_once('src/Phabstractic/Data/Types/Set.php');
require_once('src/Phabstractic/Data/Types/None.php');
require_once('src/Phabstractic/Data/Types/Enumeration.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;

class TestCustomSetClass extends Types\Set {
    
}

class TestImproperSetClass {
    
}

class TestRestrictionsCallable {
    public function testMethod() {
        
    }
}

class TestRestrictionsClass {
    
}

interface TestRestrictionsInterface {
    
}

class TestRestrictionsSubClass extends TestRestrictionsClass implements TestRestrictionsInterface {
    
}

Types\Enumeration::createEnumerator('CustomType', array( 'BASIC_BOOL',
                                          'BASIC_INT',
                                          'BASIC_FLOAT',
                                          'BASIC_STRING',
                                          'BASIC_ARRAY',
                                          'BASIC_OBJECT',
                                          'TYPED_OBJECT',
                                          'BASIC_RESOURCE',
                                          'BASIC_NULL',
                                          'BASIC_CLOSURE',
                                          'BASIC_FUNCTION',
                                          'BASIC_CALLABLE'),
                                    array('namespace' => 'RestrictionsTests' ) );

define('CUSTOMTYPECONSTANT', 256);

class RestrictionsTest extends TestCase
{
    
    public function testBasicInstantiation()
    {
        $restrictions = new Types\Restrictions(array(Types\Type::BASIC_BOOL));
        
        $this->assertInstanceOf(Types\Restrictions::class, $restrictions);
        
    }
    
    public function testCustomTypeInstantiation() {
        $restrictions = new Types\Restrictions(
            array(RestrictionsTests\CustomType::BASIC_BOOL),
            array(),
            array('type_class' => 'RestrictionsTests\\CustomType')
        );
        
        $this->assertInstanceOf(Types\Restrictions::class, $restrictions);
    }
    
    public function testProperCustomSetInstantiation() {
        $set = new TestCustomSetClass(
            array(Types\Type::BASIC_BOOL, Types\Type::BASIC_INT),
            array('unique' => true,
                  'strict' => true,)
        );
        
        $restrictions = new Types\Restrictions(
            array(),
            array(),
            array('allowed' => $set)
        );
        
        $this->assertInstanceOf(Types\Restrictions::class, $restrictions);
        
        $set = new TestCustomSetClass(
            array('Phabstractic\\Data\\Types\\Set', 'Phabstractic\\Data\\Types\\None'),
            array('unique' => true,
                  'strict' => true)
        );
        
        $restrictions = new Types\Restrictions(
            array(Types\Type::TYPED_OBJECT),
            array(),
            array('classes' => $set)
        );
        
        $this->assertInstanceOf(Types\Restrictions::class, $restrictions);
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testImproperCustomAllowedSetInstantiation() {
        $set = new TestImproperSetClass();
        
        $restrictions = new Types\Restrictions(
            array(),
            array(),
            array('allowed' => $set)
        );
        
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testImproperCustomClassesSetInstantiation() {
        $set = new TestImproperSetClass();
        
        $restrictions = new Types\Restrictions(
            array(),
            array(),
            array('classes' => $set)
        );
        
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testImproperClassInstantiation() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::TYPED_OBJECT),
            array('Phabstractic\\Data\\Types\\Set', 'Phabstractic\\NoExist'));
            
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperTypeInstantiation() {
        $restrictions = new Types\Restrictions(array(CUSTOMTYPECONSTANT));
        
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testStrictSetsInstantiation() {
        $restrictions = new Types\Restrictions(array(Types\Type::BASIC_NULL));
        
        // not in set
        $restrictions->removeAllowedType(Types\Type::BASIC_BOOL);
        
    }
    
    public function testNotStrictSetsInstantiation() {
        $restrictions = new Types\Restrictions(array(Types\Type::BASIC_NULL),
            array(),
            array('strict_sets' => false)
        );
        
        // not in set
        $restrictions->removeAllowedType(Types\Type::BASIC_BOOL);
        
    }
    
    public function testGetAllowedTypes() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL, Types\Type::BASIC_BOOL));
        
        $this->assertEquals(
            array(Types\Type::BASIC_NULL, Types\Type::BASIC_BOOL),
            $restrictions->getAllowedTypes());
            
    }
    
    public function testGetAllowedClasses() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::TYPED_OBJECT),
            array('Phabstractic\\Data\\Types\\Set', 'Phabstractic\\Data\\Types\\None'));
        
        $this->assertEquals(
            array('Phabstractic\\Data\\Types\\Set', 'Phabstractic\\Data\\Types\\None'),
            $restrictions->getAllowedClasses());
        
    }
    
    /**
     * @depends testGetAllowedTypes
     * 
     */
    public function testSetAllowedTypes() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL, Types\Type::BASIC_BOOL));
        
        $restrictions->setAllowedTypes(array(Types\Type::BASIC_INT, Types\Type::BASIC_NULL));
        
        $this->assertEquals(
            array(Types\Type::BASIC_INT, Types\Type::BASIC_NULL),
            $restrictions->getAllowedTypes());
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperSetAllowedTypes() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL, Types\Type::BASIC_BOOL));
        
        $restrictions->setAllowedTypes(array(CUSTOMTYPECONSTANT));
        
    }
    
    /**
     * @depends testGetAllowedTypes
     * 
     */
    public function testAddAllowedType() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL, Types\Type::BASIC_BOOL));
        
        $restrictions->addAllowedType(Types\Type::BASIC_INT);
        
        $this->assertEquals(
            array(Types\Type::BASIC_NULL,
                  Types\Type::BASIC_BOOL,
                  Types\Type::BASIC_INT,),
            $restrictions->getAllowedTypes());
            
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperAddAllowedType() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL, Types\Type::BASIC_BOOL));
        
        $restrictions->addAllowedType(CUSTOMTYPECONSTANT);
        
    }
    
    /**
     * @depends testGetAllowedTypes
     * 
     */
    public function testRemoveAllowedType() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL, Types\Type::BASIC_BOOL));
        
        $restrictions->removeAllowedType(Types\Type::BASIC_NULL);
        
        $this->assertEquals(
            array(Types\Type::BASIC_BOOL,),
            $restrictions->getAllowedTypes());
            
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperRemoveAllowedType() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL, Types\Type::BASIC_BOOL));
        
        $restrictions->removeAllowedType(CUSTOMTYPECONSTANT);
        
    }
    
    /**
     * @depends testGetAllowedTypes
     * 
     */
    public function testAutomaticTypedObject() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL),
            array('Phabstractic\\Data\\Types\\Set'));
        
        $this->assertEquals(
            array(Types\Type::BASIC_NULL,
                  Types\Type::TYPED_OBJECT),
            $restrictions->getAllowedTypes());
        
    }
    
    /**
     * @depends testGetAllowedClasses
     * 
     */
    public function testSetAllowedClasses() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::TYPED_OBJECT),
            array('Phabstractic\\Data\\Types\\None'));
        
        $restrictions->setAllowedClasses(array('Phabstractic\\Data\\Types\\Set',
                                               'Phabstractic\\Data\\Types\\Enumeration'));
        
        $this->assertEquals(
            array('Phabstractic\\Data\\Types\\Set',
                  'Phabstractic\\Data\\Types\\Enumeration'),
            $restrictions->getAllowedClasses());
            
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testImproperSetAllowedClasses() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::TYPED_OBJECT),
            array('Phabstractic\\Data\\Types\\None'));
        
        $restrictions->setAllowedClasses(array(CUSTOMTYPECONSTANT));
        
    }
    
    /**
     * @depends testGetAllowedClasses
     * 
     */
    public function testAddAllowedClass() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::TYPED_OBJECT),
            array('Phabstractic\\Data\\Types\\None'));
        
        $restrictions->addAllowedClass('Phabstractic\\Data\\Types\\Enumeration');
        
        $this->assertEquals(
            array('Phabstractic\\Data\\Types\\None',
                  'Phabstractic\\Data\\Types\\Enumeration'),
            $restrictions->getAllowedClasses());
            
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\RuntimeException
     * 
     */
    public function testImproperAddAllowedClass() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::TYPED_OBJECT),
            array('Phabstractic\\Data\\Types\\None'));
        
        $restrictions->addAllowedClass('Phabstractic\\NoExist');
            
    }
    
    /**
     * @depends testGetAllowedClasses
     * 
     */
    public function testRemoveAllowedClass() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::TYPED_OBJECT),
            array('Phabstractic\\Data\\Types\\None',
                  'Phabstractic\\Data\\Types\\Enumeration',));
        
        $restrictions->removeAllowedClass('Phabstractic\\Data\\Types\\Enumeration');
        
        $this->assertEquals(
            array('Phabstractic\\Data\\Types\\None',),
            $restrictions->getAllowedClasses());
            
    }
    
    public function testIsAllowedProperBasicTypes() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL));
        
        $n = null;
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($n)));
        
        $b = true;
        
        $this->assertFalse($restrictions->isAllowed(Types\Type\getValueType($b)));
        
        $restrictions->addAllowedType(Types\Type::BASIC_BOOL);
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($b)));
        
        $i = 7;
        
        $this->assertFalse($restrictions->isAllowed(Types\Type\getValueType($i)));
        
        $restrictions->addAllowedType(Types\Type::BASIC_INT);
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($i)));
        
        $f = 9.234;
        
        $this->assertFalse($restrictions->isAllowed(Types\Type\getValueType($f)));
        
        $restrictions->addAllowedType(Types\Type::BASIC_FLOAT);
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($f)));
        
        $s = 'test_string';
        
        $this->assertFalse($restrictions->isAllowed(Types\Type\getValueType($s)));
        
        $restrictions->addAllowedType(Types\Type::BASIC_STRING);
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($s)));
        
        $a = array(1,2,3,4);
        
        $this->assertFalse($restrictions->isAllowed(Types\Type\getValueType($a)));
        
        $restrictions->addAllowedType(Types\Type::BASIC_ARRAY);
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($a)));
        
        $o = new stdClass();
        $o->property = 'value';
        
        $this->assertFalse($restrictions->isAllowed(Types\Type\getValueType($o)));
        
        $restrictions->addAllowedType(Types\Type::BASIC_OBJECT);
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($o)));
        
        $r = imagecreate(10,10);
        
        $this->assertFalse($restrictions->isAllowed(Types\Type\getValueType($r)));
        
        $restrictions->addAllowedType(Types\Type::BASIC_RESOURCE);
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($r)));
        
        $c = function() { return 1; };
        
        $this->assertFalse($restrictions->isAllowed(Types\Type\getValueType($c)));
        
        $restrictions->addAllowedType(Types\Type::BASIC_CLOSURE);
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($c)));
        
        $c = array(new TestRestrictionsCallable(), 'testMethod');
        
        $this->assertFalse($restrictions->isAllowed(Types\Type\getValueType($c)));
        
        $restrictions->addAllowedType(Types\Type::BASIC_CALLABLE);
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($c)));
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\UnexpectedValueException
     * 
     */
    public function testIsAllowedImproperTypeInteger() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL,
                  Types\Type::BASIC_INT,));
        
        $restrictions->isAllowed(CUSTOMTYPECONSTANT, true);
        
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\UnexpectedValueException
     * 
     */
    public function testIsAllowedImproperTypeString() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL,
                  Types\Type::BASIC_INT,));
        
        $restrictions->isAllowed('NONEXISTENT_TYPE', true);
        
    }
    
    public function testIsAllowedProperClasses() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::TYPED_OBJECT,),
            array('Phabstractic\\Data\\Types\\Set',
                  'TestRestrictionsClass',));
        
        $set = new Types\Set(array(1,2,3,));
        $test = new TestRestrictionsClass();
        $enum = new Types\Enumeration('TestEnum', array('CONST'));
        
        $this->assertTrue($restrictions->isAllowed(array(Types\Type::TYPED_OBJECT, 'Phabstractic\\Data\\Types\\Set')));
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($set)));
        $this->assertTrue($restrictions->isAllowed(array(Types\Type::TYPED_OBJECT, 'TestRestrictionsClass')));
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($test)));
        $this->assertFalse($restrictions->isAllowed(array(Types\Type::TYPED_OBJECT, 'Phabstractic\\Data\\Types\\Enumeration')));
        $this->assertFalse($restrictions->isAllowed(Types\Type\getValueType($enum)));
    }
    
    public function testIsAllowedInterfaceClasses() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::TYPED_OBJECT,),
            array('TestRestrictionsInterface',));
        
        $test = new TestRestrictionsSubClass();
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($test)));
        
    }
    
    public function testIsAllowedSubClasses() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::TYPED_OBJECT,),
            array('TestRestrictionsClass',));
        
        $test = new TestRestrictionsSubClass();
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($test)));
        
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\UnexpectedValueException
     * 
     */
    public function testIsAllowedTypeArrayTooManyElements() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL,
                  Types\Type::BASIC_INT,
                  Types\Type::TYPED_OBJECT,),
            array('Phabstractic\\Data\\Types\\Set'));
                  
        
        $restrictions->isAllowed(array(Types\Type::TYPED_OBJECT, 'Phabstractic\\Data\\Types\\Set', 'extra'), true);
        
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\UnexpectedValueException
     * 
     */
    public function testIsAllowedTypeArrayNoTypedObject() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL,
                  Types\Type::BASIC_INT,
                  Types\Type::TYPED_OBJECT,),
            array('Phabstractic\\Data\\Types\\Set'));
        
        $restrictions->isAllowed(array(Types\Type::BASIC_NULL, 'Phabstractic\\Data\\Types\\Set'), true);
        
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\UnexpectedValueException
     * 
     */
    public function testIsAllowedTypeArrayNotString() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL,
                  Types\Type::BASIC_INT,
                  Types\Type::TYPED_OBJECT),
            array('Phabstractic\\Data\\Types\\Set'));
        
        $restrictions->isAllowed(array(Types\Type::TYPED_OBJECT, 5), true);
        
    }
    
    public function testRestrictionsDebugInfo() {
        $restrictions = new Types\Restrictions(
            array(Types\Type::BASIC_NULL,
                  Types\Type::BASIC_INT,
                  Types\Type::TYPED_OBJECT),
            array('Phabstractic\\Data\\Types\\Set'));
        
        ob_start();
        
        var_dump($restrictions);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/BASIC_NULL/", $output);
        $this->assertRegExp("/BASIC_INT/", $output);
        $this->assertRegExp("/TYPED_OBJECT/", $output);
        
    }
    
    public function testGetDefaultRestrictions() {
        $restrictions = Types\Restrictions::getDefaultRestrictions();
        
        $n = null;
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($n)));
        
        $b = true;
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($b)));
        
        $i = 7;
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($i)));
        
        $f = 9.234;
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($f)));
        
        $s = 'test_string';
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($s)));
        
        $a = array(1,2,3,4);
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($a)));
        
        $o = new stdClass();
        $o->property = 'value';
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($o)));
        
        $r = imagecreate(10,10);
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($r)));
        
        $c = function() { return 1; };
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($c)));
        
        $c = array(new TestRestrictionsCallable(), 'testMethod');
        
        $this->assertTrue($restrictions->isAllowed(Types\Type\getValueType($c)));
    }
    
    public function testCheckElements() {
        $defaultRestrictions = Types\Restrictions::getDefaultRestrictions();
        $specificRestrictions = new Types\Restrictions(array(Types\Type::BASIC_BOOL, Types\Type::BASIC_INT,));
        
        $defaultElements = array(
            null,
            7,
            3.1415,
            'test_string',
            array(1,2,3,4),
            new stdClass(),
            imagecreate(10,10),
            function() { return 1; },
            array(new TestRestrictionsCallable(), 'testMethod'),
        );
        
        $specificElements = array(
            true,
            false,
            5,
            7
        );
        
        $this->assertTrue(Types\Restrictions::checkElements($defaultElements, $defaultRestrictions));
        $this->assertTrue(Types\Restrictions::checkElements($specificElements, $specificRestrictions));
        
        $specificElements[] = 'test_string';
        
        $this->assertFalse(Types\Restrictions::checkElements($specificElements, $specificRestrictions));
        
    }
    
    public function testCompareRestrictions() {
        $aRestrictions = Types\Restrictions::getDefaultRestrictions();
        $bRestrictions = Types\Restrictions::getDefaultRestrictions();
        $cRestrictions = new Types\Restrictions(array(Types\Type::BASIC_ARRAY,
                                                      Types\Type::BASIC_BOOL,));
        $dRestrictions = new Types\Restrictions(array(Types\Type::BASIC_ARRAY,
                                                      Types\Type::BASIC_BOOL,));
        $eRestrictions = new Types\Restrictions(array(Types\Type::BASIC_INT,
                                                      Types\Type::BASIC_NULL,));
        $fRestrictions = new Types\Restrictions(array(Types\Type::TYPED_OBJECT,),
                                                array('Phabstractic\\Data\\Types\\Set',
                                                      'Phabstractic\\Data\\Types\\Enumeration'));
        $gRestrictions = new Types\Restrictions(array(Types\Type::TYPED_OBJECT,),
                                                array('Phabstractic\\Data\\Types\\Set',
                                                      'Phabstractic\\Data\\Types\\Enumeration'));
        $hRestrictions = new Types\Restrictions(array(Types\Type::TYPED_OBJECT,),
                                                array('Phabstractic\\Data\\Types\\Set',
                                                      'TestRestrictionsClass'));
        
        $this->assertTrue(Types\Restrictions::compare($aRestrictions, $bRestrictions));
        $this->assertFalse(Types\Restrictions::compare($bRestrictions, $cRestrictions));
        $this->assertTrue(Types\Restrictions::compare($cRestrictions, $dRestrictions));
        $this->assertFalse(Types\Restrictions::compare($dRestrictions, $eRestrictions));
        $this->assertTrue(Types\Restrictions::compare($fRestrictions, $gRestrictions));
        $this->assertFalse(Types\Restrictions::compare($gRestrictions, $hRestrictions));
        $this->assertFalse(Types\Restrictions::compare($aRestrictions, $hRestrictions));
        
    }
    
}
