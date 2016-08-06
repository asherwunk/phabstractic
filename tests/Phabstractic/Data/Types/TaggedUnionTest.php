<?php

require_once('src/Phabstractic/Data/Types/TaggedUnion.php');
require_once('src/Phabstractic/Data/Types/Type.php');
require_once('src/Phabstractic/Data/Types/Restrictions.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Type;

class TaggedUnionTestClass {
    
}

class TaggedUnionTestSubClass extends TaggedUnionTestClass {
    
}

class TaggedUnionTestOtherClass {
    
}

class TaggedUnionTest extends TestCase
{
    public function testEmptyInstantiation() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_NULL, Type::BASIC_INT));
        $union = new Types\TaggedUnion($restrictions);
        
        $this->assertInstanceOf(Types\TaggedUnion::class, $union);
        
    }
    
    public function testProperTypeValueSetting() {
        $restrictions = Types\Restrictions::getDefaultRestrictions();
        
        $union = new Types\TaggedUnion($restrictions);
        
        $union->set(true);
        
        $this->assertEquals(true, $union->get());
        $this->assertEquals(Type::BASIC_BOOL, (string) $union->getType());
        
        $union->set(7);
        
        $this->assertEquals(7, $union->get());
        $this->assertEquals(Type::BASIC_INT, (string) $union->getType());
        
        $union->set('test');
        
        $this->assertEquals('test', $union->get());
        $this->assertEquals(Type::BASIC_STRING, (string) $union->getType());
        
        $union->set(array(1,2,3));
        
        $this->assertEquals(array(1,2,3), $union->get());
        $this->assertEquals(Type::BASIC_ARRAY, (string) $union->getType());
        
        $c = new stdClass();
        
        $union->set($c);
        
        $this->assertInstanceOf(stdClass::class, $union->get());
        $this->assertEquals($c, $union->get());
        $this->assertEquals(Type::BASIC_OBJECT, (string) $union->getType());
        
        $i = imagecreate(10, 10);
        
        $union->set($i);
        
        $this->assertEquals($i, $union->get());
        $this->assertEquals(Type::BASIC_RESOURCE, (string) $union->getType());
        
        $union->set(null);
        
        $this->assertEquals(null, $union->get());
        $this->assertEquals(Type::BASIC_NULL, (string) $union->getType());
        
        $f = function(){return 1;};
        
        $union->set($f);
        
        $this->assertEquals($f, $union->get());
        $this->assertEquals(Type::BASIC_CLOSURE, (string) $union->getType());
        
        $union->set('Phabstractic\\Data\\Types\\Type\\getValueType');
        
        $this->assertEquals('Phabstractic\\Data\\Types\\Type\\getValueType', $union->get());
        $this->assertEquals(Type::BASIC_FUNCTION, (string) $union->getType());
        
        $union->set(3.1415);
        
        $this->assertEquals(3.1415, $union->get());
        $this->assertEquals(Type::BASIC_FLOAT, (string) $union->getType());
        
        $l = array($restrictions, 'isAllowed');
        
        $union->set($l);
        
        $this->assertEquals($l, $union->get());
        $this->assertEquals(Type::BASIC_CALLABLE, (string) $union->getType());
        
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperTypeValueSetting() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_STRING));
        
        $union = new Types\TaggedUnion($restrictions);
        
        $union->set(7);
        
    }
    
    public function testProperClassValueSetting() {
        $restrictions = new Types\Restrictions(array(Type::TYPED_OBJECT),
                                               array('TaggedUnionTestClass'));
        $union = new Types\TaggedUnion($restrictions);
        
        $p = new TaggedUnionTestClass();
        
        $union->set($p);
        
        $this->assertEquals($p, $union->get());
        $this->assertEquals(array(new Type(Type::TYPED_OBJECT), $p), $union->getType());
        
        $p = new TaggedUnionTestSubClass();
        
        $union->set($p);
        
        $this->assertEquals($p, $union->get());
        $this->assertEquals(array(new Type(Type::TYPED_OBJECT), $p), $union->getType());
        
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperClassValueSetting() {
        $restrictions = new Types\Restrictions(array(Type::TYPED_OBJECT),
                                               array('TaggedUnionTestClass'));
        $union = new Types\TaggedUnion($restrictions);
        
        $p = new TaggedUnionTestOtherClass();
        
        $union->set($p);
        
    }
    
    public function testGetRestrictions() {
        $union = new Types\TaggedUnion(new Types\Restrictions(array(Type::BASIC_INT,
                                                                    Type::BASIC_NULL,)));
        $this->assertTrue(Types\Restrictions::compare(new Types\Restrictions(array(Type::BASIC_INT,
                                                                    Type::BASIC_NULL,)),
                                                      $union->getRestrictions()));
                                                      
    }
    
    public function testChangeRestrictions() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_STRING,));
        $union = new Types\TaggedUnion($restrictions);
        
        $union->set('teststring');
        
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT,));
        $union->setRestrictions($restrictions);
        
        $union->set(7);
        
    }
    
    public function testInvocation() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT,
                                                     Type::BASIC_NULL,
                                                     Type::BASIC_STRING,));
        
        $union = new Types\TaggedUnion($restrictions);
        
        $union->set('teststring');
        
        $this->assertEquals('teststring', $union());
        
    }
    
    public function testDebugInfo() {
        $restrictions = new Types\Restrictions(array(Type::BASIC_INT,
                                                     Type::BASIC_NULL,
                                                     Type::BASIC_STRING,),
                                               array('TaggedUnionTestClass'));
        
        $union = new Types\TaggedUnion($restrictions);
        
        $union->set(7);
        
        ob_start();
        
        var_dump($union);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?value\"?\]?.*=\\>\n.*string\\(9\\).*\"BASIC_INT\"/", $output);
    }
    
}
