<?php

require_once('src/Phabstractic/Data/Types/Type.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;

function testFunction() {
    
}

class TestClass {
    public function testMethod() {
        
    }
}

class TypeTest extends TestCase
{
    
    public function testEnumerationExistence()
    {
        $this->assertTrue(class_exists('\\Phabstractic\\Data\\Types\\Type'));
        
    }
    
    public function testGetDataTypes()
    {
        $value = null;
        
        $this->assertEquals(Types\Type::BASIC_NULL, (string) Types\Type\getValueType($value));
        
        $value = false;
        
        $this->assertEquals(Types\Type::BASIC_BOOL, (string) Types\Type\getValueType($value));
        
        $value = 5;
        
        $this->assertEquals(Types\Type::BASIC_INT, (string) Types\Type\getValueType($value));
        
        $value = 'testFunction';
        
        $this->assertEquals(Types\Type::BASIC_FUNCTION, (string) Types\Type\getValueType($value));
        
        $value = 'foobar';
        
        $this->assertEquals(Types\Type::BASIC_STRING, (string) Types\Type\getValueType($value));
        
        $value = array('element','key'=>'value');
        
        $this->assertEquals(Types\Type::BASIC_ARRAY, (string) Types\Type\getValueType($value));
        
        $value = imagecreate(10,10);
        
        $this->assertEquals(Types\Type::BASIC_RESOURCE, (string) Types\Type\getValueType($value));
        
        $value = function () { return 1; };
        
        $this->assertEquals(Types\Type::BASIC_CLOSURE, (string) Types\Type\getValueType($value));
        
        $value = new stdClass();
        
        $this->assertEquals(Types\Type::BASIC_OBJECT, (string) Types\Type\getValueType($value));
        
        $value = new TestClass();
        
        $type = Types\Type\getValueType($value);
        
        $this->assertInstanceOf(Types\Type::class, $type[0]);
        
        $this->assertInstanceOf(TestClass::class, $type[1]);
        
        $value = array(new TestClass(), 'testMethod');
        
        $this->assertEquals(Types\Type::BASIC_CALLABLE, (string) Types\Type\getValueType($value));
    }
    
    public function testStringToTypeBoolean()
    {
        $this->assertEquals(Types\Type::BASIC_BOOL, (string) Types\Type\stringToType('BASIC_BOOL'));
        $this->assertEquals(Types\Type::BASIC_BOOL, (string) Types\Type\stringToType('BOOL'));
        $this->assertEquals(Types\Type::BASIC_BOOL, (string) Types\Type\stringToType('BOOLEAN'));
        
    }
    
    public function testStringToTypeInteger()
    {
        $this->assertEquals(Types\Type::BASIC_INT, (string) Types\Type\stringToType('BASIC_INT'));
        $this->assertEquals(Types\Type::BASIC_INT, (string) Types\Type\stringToType('INT'));
        $this->assertEquals(Types\Type::BASIC_INT, (string) Types\Type\stringToType('INTEGER'));
        
    }
    
    public function testStringToTypeString()
    {
        $this->assertEquals(Types\Type::BASIC_STRING, (string) Types\Type\stringToType('BASIC_STRING'));
        $this->assertEquals(Types\Type::BASIC_STRING, (string) Types\Type\stringToType('STRING'));
        $this->assertEquals(Types\Type::BASIC_STRING, (string) Types\Type\stringToType('STR'));
        
    }
    
    public function testStringToTypeArray()
    {
        $this->assertEquals(Types\Type::BASIC_ARRAY, (string) Types\Type\stringToType('BASIC_ARRAY'));
        $this->assertEquals(Types\Type::BASIC_ARRAY, (string) Types\Type\stringToType('ARRAY'));
        $this->assertEquals(Types\Type::BASIC_ARRAY, (string) Types\Type\stringToType('ARR'));
        
    }
    
    public function testStringToTypeObject()
    {
        $this->assertEquals(Types\Type::BASIC_OBJECT, (string) Types\Type\stringToType('BASIC_OBJECT'));
        $this->assertEquals(Types\Type::BASIC_OBJECT, (string) Types\Type\stringToType('OBJECT'));
        $this->assertEquals(Types\Type::BASIC_OBJECT, (string) Types\Type\stringToType('OBJ'));
        
    }
    
    public function testStringToTypeResource()
    {
        $this->assertEquals(Types\Type::BASIC_RESOURCE, (string) Types\Type\stringToType('BASIC_RESOURCE'));
        $this->assertEquals(Types\Type::BASIC_RESOURCE, (string) Types\Type\stringToType('RESOURCE'));
        $this->assertEquals(Types\Type::BASIC_RESOURCE, (string) Types\Type\stringToType('RSRC'));
        
    }
    
    public function testStringToTypeNull()
    {
        $this->assertEquals(Types\Type::BASIC_NULL, (string) Types\Type\stringToType('BASIC_NULL'));
        $this->assertEquals(Types\Type::BASIC_NULL, (string) Types\Type\stringToType('NULL'));
        
    }
    
    public function testStringToTypeClosure()
    {
        $this->assertEquals(Types\Type::BASIC_CLOSURE, (string) Types\Type\stringToType('BASIC_CLOSURE'));
        $this->assertEquals(Types\Type::BASIC_CLOSURE, (string) Types\Type\stringToType('CLOSURE'));
        
    }
    
    public function testStringToTypeFunction()
    {
        $this->assertEquals(Types\Type::BASIC_FUNCTION, (string) Types\Type\stringToType('BASIC_FUNCTION'));
        $this->assertEquals(Types\Type::BASIC_FUNCTION, (string) Types\Type\stringToType('FUNCTION'));
        $this->assertEquals(Types\Type::BASIC_FUNCTION, (string) Types\Type\stringToType('FUNC'));
        
    }
    
}
