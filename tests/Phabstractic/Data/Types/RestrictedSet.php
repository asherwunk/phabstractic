<?php

require_once('src/Phabstractic/Data/Types/RestrictedSet.php');
require_once('src/Phabstractic/Data/Types/Type.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractFilter.php');
require_once('src/Phabstractic/Data/Types/Exception/InvalidArgumentException.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Type;
use Phabstractic\Data\Types\Resource as TypesResource;

class TestRestrictedSetClass extends TypesResource\AbstractFilter {
    
    public function isAllowed($type, $strict = false) {
        if ($type == 'allowed') {
            return true;
        } else {
            if ($strict) {
                 throw new TypesException\InvalidArgumentException(
                            'Phabstractic\\Data\\Types\\Resource\\' .
                            'AbstractFilter->checkElements: Illegal Value');
            } else {
                return false;
            }
        }
    }
    
    public static function getDefaultRestrictions() {
        return new TestRestrictedSetClass();
    }
}

class TestRestrictedSetMapInternalClass {
    
    public static $counter = 0;
    
    public function addCounter() {
        self::$counter++;
    }
}

class RestrictedSetTest extends TestCase
{
    public function testEmptyInstantiation() {
        $rset = new Types\RestrictedSet();
        
        $this->assertInstanceOf(Types\RestrictedSet::class, $rset);
        
    }
    
    public function testCustomRestrictionsClassInsantiation() {
        $rset = new Types\RestrictedSet(array(), null, array('filter_class'=>'TestRestrictedSetClass'));
        
        $this->assertInstanceOf(Types\RestrictedSet::class, $rset);
        
        ob_start();
        
        var_dump($rset->getRestrictions());
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/TestRestrictedSetClass/", $output);

    }
    
    public function testPropertSetWithRestrictions() {
        $rset = new Types\RestrictedSet(array(1,2,3,'red','blue'),
                        new Types\Restrictions(array(Type::BASIC_INT, Type::BASIC_STRING)));
        
        $this->assertEquals(array(1,2,3,'red','blue'), $rset->getPlainArray());
        
    }
    
    public function testPropertClassSetWithRestrictions() {
        $rset = new Types\RestrictedSet(array(),
                        new Types\Restrictions(array(Type::TYPED_OBJECT),
                                               array('TestRestrictedSetMapInternalClass')));
        
        $obj1 = new TestRestrictedSetMapInternalClass();
        $obj2 = new TestRestrictedSetMapInternalClass();
        $obj3 = new TestRestrictedSetMapInternalClass();
        
        $rset->add($obj1);
        $rset->add($obj2);
        $rset->add($obj3);
        
        $this->assertEquals(array($obj1, $obj2, $obj3), $rset->getPlainArray());
        
    }
    
    public function testImpropertClassSetWithRestrictions() {
        $rset = new Types\RestrictedSet(array(),
                        new Types\Restrictions(array(Type::TYPED_OBJECT),
                                               array('TestRestrictedSetMapInternalClass')));
        
        $obj1 = new TestRestrictedSetMapInternalClass();
        $obj2 = new TestRestrictedSetMapInternalClass();
        $obj3 = new TestRestrictedSetClass();
        
        $rset->add($obj1);
        $rset->add($obj2);
        $rset->add($obj3);
        
        $this->assertEquals(array($obj1, $obj2), $rset->getPlainArray());
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImpropertClassSetWithRestrictionsWithStrict() {
        $rset = new Types\RestrictedSet(array(),
                        new Types\Restrictions(array(Type::TYPED_OBJECT),
                                               array('TestRestrictedSetMapInternalClass')),
                                        array('strict'=>true));
        
        $obj1 = new TestRestrictedSetMapInternalClass();
        $obj2 = new TestRestrictedSetMapInternalClass();
        $obj3 = new TestRestrictedSetClass();
        
        $rset->add($obj1);
        $rset->add($obj2);
        $rset->add($obj3);
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImpropertSetWithRestrictions() {
        $rset = new Types\RestrictedSet(array(1,2,3,'red','blue'),
                        new Types\Restrictions(array(Type::BASIC_INT)));
        
        $this->assertEquals(array(1,2,3,'red','blue'), $rset->getPlainArray());
    }
    
    public function testProperAddWithDefaultRestrictions() {
        $rset = new Types\RestrictedSet();
        
        $o = new stdClass();
        
        $rset->add(1);
        $rset->add('two');
        $rset->add($o);
        
        $this->assertEquals(array(1, 'two', $o), $rset->getPlainArray());
    }
    
    public function testProperAddWithRestrictions() {
        $rset = new Types\RestrictedSet(array(),
                    new Types\Restrictions(array(Type::BASIC_STRING)));
        
        $rset->add('one');
        $rset->add('two');
        $rset->add('red');
        $rset->add('blue');
        
        $this->assertEquals(array('one', 'two', 'red', 'blue'), $rset->getPlainArray());
    }
    
    public function testImproperAddWithRestrictionsNotStrict() {
        $rset = new Types\RestrictedSet(array(),
                    new Types\Restrictions(array(Type::BASIC_INT)));
        
        $rset->add(1);
        $rset->add(2);
        $rset->add(3);
        $rset->add('four');
        
        $this->assertEquals(array(1,2,3), $rset->getPlainArray());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperAddWithRestrictionsStrict() {
        $rset = new Types\RestrictedSet(array(),
                    new Types\Restrictions(array(Type::BASIC_INT)),
                    array('strict'=>true));
        
        $rset->add(1);
        $rset->add(2);
        $rset->add(3);
        $rset->add('four');
        
    }

    public function testProperAddReferenceWithRestrictions() {
        $rset = new Types\RestrictedSet(array(),
                    new Types\Restrictions(array(Type::BASIC_STRING)));
        
        $testref = 'one';
        
        $rset->addReference($testref);
        $rset->add('two');
        $rset->add('red');
        $rset->add('blue');
        
        $testref = 'surprise';
        
        $this->assertEquals(array('surprise', 'two', 'red', 'blue'), $rset->getPlainArray());
    }
    
    public function testProperBuildRestrictedSet() {
        $rset = Types\RestrictedSet::build(array(1,2,3),
                    new Types\Restrictions(array(Type::BASIC_INT)));
                    
        $this->assertInstanceOf(Types\RestrictedSet::class, $rset);
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperBuildRestrictedSet() {
        $rset = Types\RestrictedSet::build(array(1,2,'three'),
                    new Types\Restrictions(array(Type::BASIC_INT)));
        
    }
    
    public function testRestrictedSetMapInternal() {
        $obj1 = new TestRestrictedSetMapInternalClass();
        $obj2 = new TestRestrictedSetMapInternalClass();
        $obj3 = new TestRestrictedSetMapInternalClass();
        
        $rset = new Types\RestrictedSet(array($obj1, $obj2, $obj3),
                        new Types\Restrictions(array(Type::TYPED_OBJECT),
                                               array('TestRestrictedSetMapInternalClass')));
        
        Types\RestrictedSet::mapInternal('addCounter', array(), $rset);
        
        $this->assertEquals(3, TestRestrictedSetMapInternalClass::$counter);
        
    }
    
    public function testSetDebugInfo() {
        $rset = Types\RestrictedSet::build(array(1,2,3),
                    new Types\Restrictions(array(Type::BASIC_INT)));
        
        ob_start();
        
        var_dump($rset);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?restrictions\"?\]?.*=\\>\n.*object\\(Phabstractic\\\\Data\\\Types\\\\Restrictions\\)/", $output);

    }
}
