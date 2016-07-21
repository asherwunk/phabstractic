<?php

require_once('src/Phabstractic/Data/Types/Structure.php');
require_once('src/Phabstractic/Data/Types/Type.php');
require_once('src/Phabstractic/Data/Types/Restrictions.php');
require_once('src/Phabstractic/Data/Types/TaggedUnion.php');
require_once('src/Phabstractic/Data/Types/None.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Type;

class StructureTest extends TestCase
{
    public function testEmptyInstantiation() {
        $structure = new Types\Structure();
        
        $this->assertInstanceOf(Types\Structure::class, $structure);
        
    }
    
    public function testVersion2Instantiation() {
        $structure = new Types\Structure(array('field1', 'field2', 'field3'), array('version2' => true));
        
        $this->assertInstanceOf(Types\Structure::class, $structure);
        $this->assertEquals(array('field1', 'field2', 'field3'), $structure->getElements());
    }
    
    public function testVersion2InstantiationWithRestrictions() {
        $structure = new Types\Structure(array('field1', 'field2', 
                        array(new Types\Restrictions(array(Type::BASIC_STRING)), 'field3')),
                     array('version2' => true));
        
        $this->assertInstanceOf(Types\Structure::class, $structure);
        $this->assertEquals(array('field1', 'field2', 'field3'), $structure->getElements());
        $this->assertTrue(Types\Restrictions::compare(new Types\Restrictions(array(Type::BASIC_STRING)),
                                                      $structure->getElementRestrictions('field3')));
    }
    
    public function testVersion2InstantiationWithTaggedUnion() {
        $union = new Types\TaggedUnion(new Types\Restrictions(array(Type::BASIC_STRING)));
        $structure = new Types\Structure(array('field1', 'field2', 
                        array($union, 'field3')),
                     array('version2' => true));
        
        $this->assertInstanceOf(Types\Structure::class, $structure);
        $this->assertEquals(array('field1', 'field2', 'field3'), $structure->getElements());
        $this->assertTrue(Types\Restrictions::compare(new Types\Restrictions(array(Type::BASIC_STRING)),
                                                      $structure->getElementRestrictions('field3')));
    }
    
    public function testVersion2WithoutInsensitive() {
        $structure = new Types\Structure(array('field1', 'field2', 'field3'), array('version2' => true));
        
        $structure->setElement('field1', 5);
        
        // should denormalize
        $this->assertEquals(5, $structure->getElement('FiElD1'));
        
    }
    
    public function testVersion2WithInsensitive() {
        $structure = new Types\Structure(array('FiElD1', 'fIeLd2', 'field3'),
                        array('version2' => true, 'insensitive' => true));
        
        $this->assertEquals(array('field1', 'field2', 'field3'), $structure->getElements());
        
    }
    
    public function testBasicInsantiation() {
        // version 3 instantiation here
        $structure = new Types\Structure(array('field1'=>1,'field2'=>2,'field3'=>3));
        
        $this->assertEquals(array('field1', 'field2', 'field3'), $structure->getElements());
        
    }
    
    public function testBasicInstantiationWithRestrictions() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>new Types\Restrictions(array(Type::BASIC_STRING)),
        ));
        
        $this->assertEquals(array('field1', 'field2', 'field3'), $structure->getElements());
        $this->assertTrue(Types\Restrictions::compare(new Types\Restrictions(array(Type::BASIC_STRING)),
                                                      $structure->getElementRestrictions('field3')));
    }
    
    public function testBasicInstantiationWithTaggedUnion() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>new Types\TaggedUnion(new Types\Restrictions(array(Type::BASIC_STRING))),
        ));
        
        $this->assertEquals(array('field1', 'field2', 'field3'), $structure->getElements());
        $this->assertTrue(Types\Restrictions::compare(new Types\Restrictions(array(Type::BASIC_STRING)),
                                                      $structure->getElementRestrictions('field3')));
    }
    
    public function testBasicInstantiationWithoutInsensitive() {
        $structure = new Types\Structure(array('FiElD1'=>1, 'fIeLd2'=>2, 'field3'=>3), array('insensitive' => false));
        
        $this->assertEquals(array('FiElD1', 'fIeLd2', 'field3'), $structure->getElements());
        
        // this should fail silently
        $structure->setElement('field1', 5);
        $this->assertEquals(1, $structure->getElement('FiElD1'));
        
    }
    
    public function testBasicInstantiationWithInsensitive() {
        $structure = new Types\Structure(array('FiElD1'=>1, 'fIeLd2'=>2, 'field3'=>3), array('insensitive' => true));
        
        $this->assertEquals(array('field1', 'field2', 'field3'), $structure->getElements());
        
        // this should fail silently
        $structure->setElement('field1', 5);
        $this->assertEquals(5, $structure->getElement('field1'));
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testWithoutInsensitiveImproperKey() {
        $structure = new Types\Structure(array('FiElD1'=>1, 'fIeLd2'=>2, 'field3'=>3),
                        array('insensitive' => false, 'strict' => true));
        
        $structure->setElement('field1', 5);
        
    }
    
    public function testToArray() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>new Types\TaggedUnion(new Types\Restrictions(array(Type::BASIC_STRING))),
        ));
        
        $arr = $structure->toArray();
        $arr['field3'] = $arr['field3']->get();
        
        $this->assertEquals(array('field1'=>1, 'field2'=>2, 'field3'=>null), $arr);
        
    }
    
    public function testSetTaggedUnionWithProper() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>new Types\TaggedUnion(new Types\Restrictions(array(Type::BASIC_STRING))),
        ));
        
        $structure->setElement('field3', 'teststring');
        $this->assertEquals('teststring', $structure->getElement('field3'));
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testSetTaggedUnionWithImproper() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>new Types\TaggedUnion(new Types\Restrictions(array(Type::BASIC_STRING))),
        ));
        
        $structure->setElement('field3', 7);
        
    }
    
    public function testSetImproperKeyNoStrict() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>3,
        ));
        
        $structure->setElement('nonexist', 5);
        
        $this->assertEquals(array('field1', 'field2', 'field3'), $structure->getElements());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testSetImproperKeyStrict() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>3,
        ), array('strict' => true));
        
        $structure->setElement('nonexist', 5);
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testClearWithUnsettableTaggedUnion() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>new Types\TaggedUnion(new Types\Restrictions(array(Type::BASIC_STRING))),
        ));
        
        $structure->clear();
    }
    
    public function testClearWithSettableTaggedUnion() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>new Types\TaggedUnion(new Types\Restrictions(array(Type::BASIC_STRING, Type::BASIC_NULL))),
        ));
        
        $structure->clear();
        
        $this->assertEquals(null, $structure->getElement('field1'));
        $this->assertEquals(null, $structure->getElement('field2'));
        $this->assertEquals(null, $structure->getElement('field3'));
    }
    
    public function testReturnNoneWhenNoRestrictions() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>new Types\TaggedUnion(new Types\Restrictions(array(Type::BASIC_STRING, Type::BASIC_NULL))),
        ));
        
        $this->assertInstanceOf(Types\None::class, $structure->getElementRestrictions('field1'));
        
    }
    
    public function testGetNonexistElementNoStrict() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>3,
        ));
        
        $this->assertInstanceOf(Types\None::class, $structure->getElement('field4'));
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testGetNonexistElementStrict() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>3,
        ), array('strict' => true));
        
        $structure->getElement('field4');
        
    }
    
    public function testBasicOffsetSet() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>3,
        ));
        
        $structure['field1'] = 5;
        
        $this->assertEquals(5, $structure->getElement('field1'));
        
    }
    
    public function testTaggedUnionProperOffsetSet() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>new Types\TaggedUnion(new Types\Restrictions(array(Type::BASIC_INT, Type::BASIC_NULL))),
        ));
        
        $structure['field3'] = 5;
        
        $this->assertEquals(5, $structure->getElement('field3'));
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testTaggedUnionImproperOffsetSetNoStrict() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>new Types\TaggedUnion(new Types\Restrictions(array(Type::BASIC_INT, Type::BASIC_NULL))),
        ));
        
        $structure['field3'] = 'teststring';
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testTaggedUnionImproperOffsetSetStrict() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>new Types\TaggedUnion(new Types\Restrictions(array(Type::BASIC_INT, Type::BASIC_NULL))),
        ), array('strict' => true));
        
        $structure['field3'] = 'teststring';
        
    }
    
    public function testBasicOffsetGet() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>3,
        ));
        
        $this->assertEquals(2, $structure['field2']);
        $this->assertInstanceOf(Types\None::class, $structure['nonexist']);
        
    }
    
    public function testBasicOffsetUnset() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>3,
        ));
        
        unset($structure['field2']);
        
        $this->assertEquals(array('field1', 'field2', 'field3'), $structure->getElements());
        
    }
    
    public function testBasicOffsetIsset() {
        // version 3 instantiation here
        $structure = new Types\Structure(array(
            'field1'=>1,
            'field2'=>2,
            'field3'=>3,
        ));
        
        $this->assertTrue(isset($structure['field2']));
        $this->assertFalse(isset($structure['nonexist']));
        
    }
    
}
