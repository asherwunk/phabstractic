<?php

require_once('src/Phabstractic/Data/Types/RestrictedSet.php');
require_once('src/Phabstractic/Data/Types/Set.php');
require_once('src/Phabstractic/Data/Types/Resource/SetInterface.php');
require_once('src/Phabstractic/Data/Types/Type.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractFilter.php');
require_once('src/Phabstractic/Data/Types/Exception/InvalidArgumentException.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Type;
use Phabstractic\Data\Types\Resource as TypesResource;
use Phabstractic\Features\Resource as FeaturesResource;

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
        $this->assertInstanceOf(Types\Set::class, $rset);
        $this->assertInstanceOf(TypesResource\SetInterface::class, $rset);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $rset);
        
    }
    
    public function testBasicInstantiation() {
        $rset = new Types\RestrictedSet(array(1,2,3));
        
        $this->assertInstanceOf(Types\RestrictedSet::class, $rset);
        $this->assertEquals(array(1,2,3), $rset->getPlainArray());
        
        $this->assertInstanceOf(Types\RestrictedSet::class, $rset);
        $this->assertInstanceOf(Types\Set::class, $rset);
        $this->assertInstanceOf(TypesResource\SetInterface::class, $rset);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $rset);
    }
    
    public function testUniqueInstantiation() {
        $rset = new Types\RestrictedSet(array(1,2,3),null,array('unique'=>true));
        
        $this->assertInstanceOf(Types\RestrictedSet::class, $rset);
        $this->assertEquals(array(1,2,3), $rset->getPlainArray());
        
        $this->assertInstanceOf(Types\RestrictedSet::class, $rset);
        $this->assertInstanceOf(Types\Set::class, $rset);
        $this->assertInstanceOf(TypesResource\SetInterface::class, $rset);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $rset);
    }
    
    public function testNotReferencedInstantiation() {
        $testref = 7;
        
        $set = new Types\RestrictedSet(array(1,&$testref,3),null,array('reference'=>false));
        
        $testref = 3;
        
        $this->assertEquals(array(1,7,3), $set->getPlainArray());
        
        $testref = 7;
        
        $set = new Types\RestrictedSet(array(1,&$testref,3),null,array('reference'=>true));
        
        $testref = 3;
        
        $this->assertEquals(array(1,3,3), $set->getPlainArray());
        
        // default behavior is to reference
        
        $testref = 7;
        
        $set = new Types\RestrictedSet(array(1,&$testref,3));
        
        $testref = 3;
        
        $this->assertEquals(array(1,3,3), $set->getPlainArray());
        
    }
    
    public function testCustomRestrictionsClassInsantiation() {
        $rset = new Types\RestrictedSet(array(), null, array('filter_class'=>'TestRestrictedSetClass'));
        
        $this->assertInstanceOf(Types\RestrictedSet::class, $rset);
        
        ob_start();
        
        var_dump($rset->getRestrictions());
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/TestRestrictedSetClass/", $output);
        
        $this->assertInstanceOf(Types\RestrictedSet::class, $rset);
        $this->assertInstanceOf(Types\Set::class, $rset);
        $this->assertInstanceOf(TypesResource\SetInterface::class, $rset);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $rset);

    }
    
    public function testReturnPlainArray() {
        $set = new Types\RestrictedSet(array(1,2,3,2));
        
        $this->assertEquals(array(1,2,3,2), $set->getPlainArray());
        
    }
    
    public function testConstructWithArray() {
        $data = array('testone', 'testtwo', 'testthree');
        $set = new Types\RestrictedSet($data,null,array('strict'=>true,'unique'=>true,'reference'=>false));
        
        foreach ($set->getArray() as $key => $value) {
            if (strpos($key, 'Phabstractic\\Data\\Types\\Set::Element') === false) {
                $this->assertFalse(true);
            }
        }
        
    }
    
    public function testEnumerate() {
        $set = new Types\RestrictedSet(array(1,2,3,2));
        
        $this->assertEquals(array(1,2,3,2), $set->enumerate());
        
    }
    
    public function testReturnArray() {
        $set = new Types\RestrictedSet(array(1,2,3,2));
        
        $keys = array_keys($set->getArray());
        
        $this->assertEquals(array(
            $keys[0] => 1,
            $keys[1] => 2,
            $keys[2] => 3,
            $keys[3] => 2,), $set->getArray());
            
    }
    
    public function testReturnArrayReference() {
        $testref = 6;
        
        $set = new Types\RestrictedSet(array(1,2,&$testref,3,4,2));
        
        $arrayref = $set->getArrayReference();
        
        $testref = 7;
        
        $this->assertTrue(in_array(7,$arrayref));
        
    }
    
    public function testIdentifierPrefix() {
        $set = new Types\RestrictedSet();
        
        $set->setIdentifierPrefix('TestPrefix');
        
        for ($i = 0; $i < 5; $i++) {
            $set->add($i);
        }
        
        foreach (array_keys($set->getArray()) as $key) {
            $this->assertTrue(strpos($key, 'TestPrefix') !== false);
            
        }
    }
    
    public function testGetReference() {
        $testref = 6;
        
        $set = new Types\RestrictedSet(array(1,2,3,4,2));
        $id = $set->addReference($testref);
        
        $cbtest = &$set->retrieveReference($id);
        
        $cbtest = 7;
        
        $this->assertEquals(7, $testref);
        
    }
    
    
    
    public function testPropertSetWithRestrictions() {
        $rset = new Types\RestrictedSet(array(1,2,3,'red','blue'),
                        new Types\Restrictions(array(Type::BASIC_INT, Type::BASIC_STRING)));
        
        $this->assertEquals(array(1,2,3,'red','blue'), $rset->getPlainArray());
        
    }
    
    public function testProperSetClassSetWithRestrictions() {
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
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testAddWithUniqueWithError() {
        $set = new Types\RestrictedSet(array(),null,array('unique'=>true, 'strict'=>true));
        
        $set->add(1);
        $set->add(2);
        $set->add(3);
        $set->add(2);
        
    }
    
    public function testAddWithUniqueNoError() {
        $set = new Types\RestrictedSet(array(),null,array('unique'=>true));
        
        $set->add(1);
        $set->add(2);
        $set->add(3);
        $set->add(2);
        
        $this->assertEquals(array(1, 2, 3),$set->getPlainArray());
    }
    
    /**
     * @depends testReturnPlainArray
     * 
     */
    public function testRemoveValue() {
        $set = new Types\RestrictedSet(array(1,2,3,4,5,6,7));
        
        $set->remove(4);
        
        $this->assertEquals(array(1,2,3,5,6,7), $set->getPlainArray());
        
        $set->add(9);
        $set->add(9);
        
        // removes all versions
        
        $set->remove(9);
        
        $this->assertEquals(array(1,2,3,5,6,7), $set->getPlainArray());
    }
    
    /**
     * @depends testReturnPlainArray
     * 
     */
    public function testRemoveValueByIdentifier() {
        $set = new Types\RestrictedSet(array(1,2,3,4,5,6,7));
        
        $set->remove(4);
        
        $this->assertEquals(array(1,2,3,5,6,7), $set->getPlainArray());
        
        $set->add(9);
        $identifier = $set->add(9);
        
        // removes specific identifier element
        
        $set->removeByIdentifier($identifier);
        
        $this->assertEquals(array(1,2,3,5,6,7,9), $set->getPlainArray());
    }
    
    public function testInSet() {
        $set = new Types\RestrictedSet(array(1,2,3,4,5,6,7));
        
        $this->assertTrue($set->in(4));
        $this->assertFalse($set->in(42));
        
    }
    
    public function testIsEmpty() {
        $set = new Types\RestrictedSet(array(1,2,3,4,5,6,7));
        
        $this->assertFalse($set->isEmpty());
        
        $set = new Types\RestrictedSet();
        
        $this->assertTrue($set->isEmpty());
        
    }
    
    public function testSize() {
        $set = new Types\RestrictedSet(array(1,2,3,4,5,6,7));
        
        $this->assertEquals(7, $set->size());
        
        $set->remove(4);
        
        $this->assertEquals(6, $set->size());
        
    }
    
    public function testIterator() {
        $set = new Types\RestrictedSet(array(1,2,3,4,5,6,7));
        
        $testarray = array();
        
        foreach ($set->iterate() as $value) {
            $array[] = $value;
        }
        
        $this->assertEquals(array(1,2,3,4,5,6,7), $array);
        
    }
    
    /**
     * @depends testReturnPlainArray
     * 
     */
    public function testPop() {
        $set = new Types\RestrictedSet(array(1,2,3,4,5,6,7));
        
        $testval = $set->pop();
        
        $this->assertTrue(in_array($testval, array(1,2,3,4,5,6,7), true));
        
        $testarr = array_merge($set->getPlainArray(), array($testval));
        
        $this->assertEquals(array(1,2,3,4,5,6,7), $testarr);
        
    }
    
    /**
     * @depends testReturnPlainArray
     * 
     */
    public function testClear() {
        $set = new Types\RestrictedSet(array(1,2,3,4,5,6,7));
        
        $set->clear();
        
        $this->assertEmpty($set->getPlainArray());
        
    }
    
    public function testHash() {
        $set = new Types\RestrictedSet(array(1,2,3,5,6,7,9));
        
        $this->assertEquals('432e03e3a394c1f5240281ac34c847ac', $set->hash());
        
        $set = new Types\RestrictedSet(array(1,4,2,9,7));
        
        $this->assertEquals('ceb4514aad5416877c1555f0420d2181', $set->hash());
        
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
    
    public function testStaticEqual() {
        $s1 = new Types\RestrictedSet(array(1,2,3,5,6,7,9));
        $s2 = new Types\RestrictedSet(array(1,2,3,5,6,7,9));
        $s3 = new Types\RestrictedSet(array(1,2,3,7,9));
        
        $this->assertTrue(Types\Set::equal($s1, $s2));
        $this->assertFalse(Types\Set::equal($s1, $s3));
        
        /* for in depth testing of array element comparison see elementComparison
           in ArrayUtilities.php */
    }
    
    public function testStaticFold() {
        $f = function($carry, $item) { return $carry + $item; };
        
        $s1 = new Types\RestrictedSet(array(1,2,3,5,6,7,9));
        
        // 1+2=3+3=6+5=11+6=17+7=24+9=33
        
        $res = Types\Set::fold($f, $s1);
        
        $this->assertEquals(33, $res);
    }

    public function testStaticFilter() {
        $s1 = new Types\RestrictedSet(array(1,2,3,5,6,7,8,9));
        
        $oddFunc = function ($var) {return($var & 1);};
        $evenFunc = function ($var) {return(!($var & 1));};
        
        $oddsarr = Types\Set::filter($oddFunc, $s1);
        
        $odds = array_merge($oddsarr, array());
        
        $this->assertEquals(array(1,3,5,7,9), $odds);
        
        $evensarr = Types\Set::filter($evenFunc, $s1);
        
        $evens = array_merge($evensarr, array());
        
        $this->assertEquals(array(2,6,8), $evens);
    
    }
    
    public function testStaticMap() {
        // unique values
        
        $s1 = new Types\RestrictedSet(array(1,2,3,4,5,6,7,8,9));
        
        $f = function ($var) {return $var*$var;};
        
        $res = Types\Set::map($f, $s1);
        
        $this->assertEquals(array(1,4,9,16,25,36,49,64,81), $res);
        
        // two similar values
        
        $s1 = new Types\Set(array(1,2,3,3,5,6,7,8,9));
        
        $f = function ($var) {return $var*$var;};
        
        $res = Types\Set::map($f, $s1);
        
        $this->assertEquals(array(1,4,9,25,36,49,64,81), $res);
    }
    
    /**
     * @depends testReturnPlainArray
     * 
     */
    public function testStaticWalk() {
        $s1 = new Types\RestrictedSet(array(1,2,3,4,5,6,7,8,9));
        
        $userdata = 3;
        
        $f = function (&$var, $key, $userdata) {$var += $userdata;};
        
        $this->assertTrue(Types\Set::walk($s1, $f, $userdata));
        
        $this->assertEquals(array(4,5,6,7,8,9,10,11,12), $s1->getPlainArray());
        
    }
    
    public function testStaticUnion() {
        $s1 = new Types\RestrictedSet(array(1,3,5,7));
        $s2 = new Types\RestrictedSet(array(2,4,5,9));
        $a1 = array(5,8,12);
        $a2 = array(8,10,13);
        $v1 = 14;
        
        $this->assertEquals(array(1,3,5,7,2,4,9), Types\Set::union($s1,$s2));
        $this->assertEquals(array(1,3,5,7,8,12), Types\Set::union($s1,$a1));
        $this->assertEquals(array(1,3,5,7,8,12,14,10,13), Types\Set::union($s1,$a1,$v1,$a2));
        
    }
    
    public function testStaticIntersection() {
        $s1 = new Types\RestrictedSet(array(1,3,5,7,42));
        $s2 = new Types\RestrictedSet(array(2,7,5,9,42));
        $a1 = array(3,8,12,42);
        $a2 = array(13,1,42);
        $v1 = 42;
        
        $this->assertEquals(array(5,7,42), array_merge(Types\Set::intersection($s1,$s2), array()));
        $this->assertEquals(array(3,42), array_merge(Types\Set::intersection($s1,$a1), array()));
        $this->assertEquals(array(42), array_merge(Types\Set::intersection($s1,$s2,$a1,$a2,$v1), array()));
        
    }
    
    public function testStaticDifference() {
        $s1 = new Types\RestrictedSet(array(1,3,5,7));
        $s2 = new Types\RestrictedSet(array(2,7,5,9));
        $a1 = array(3,8,12);
        $v1 = 1;
        
        $this->assertEquals(array(1,3), array_merge(Types\Set::difference($s1,$s2)));
        $this->assertEquals(array(1), array_merge(Types\Set::difference($s1,$s2,$a1)));
        $this->assertEmpty(Types\Set::difference($s1,$s2,$a1,$v1));
        
    }
    
    public function testStaticSubset() {
        $s1 = new Types\RestrictedSet(array(1,3,5,7,2,5,9));
        $s2 = new Types\RestrictedSet(array(2,7,5,9));
        $s3 = new Types\RestrictedSet(array(2,7,5,13));
        
        $this->assertTrue(Types\Set::subset($s1, $s2));
        $this->assertFalse(Types\Set::subset($s3, $s1));
        
    }
    
    public function testSetDebugInfo() {
        $rset = Types\RestrictedSet::build(array(1,2,3),
                    new Types\Restrictions(array(Type::BASIC_INT)));
        
        ob_start();
        
        var_dump($rset);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?restrictions\"?\]?.*=\\>\n.*(object|class)?\\s?\\(?Phabstractic\\\\Data\\\Types\\\\Restrictions\\)?/", $output);

    }
}
