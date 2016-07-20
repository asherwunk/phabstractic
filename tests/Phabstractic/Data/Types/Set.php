<?php

require_once('src/Phabstractic/Data/Types/Set.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;

class SetTest extends TestCase
{
    public function testEmptyInstantiation() {
        $set = new Types\Set();
        
        $this->assertInstanceOf(Types\Set::class, $set);
        
    }
    
    public function testBasicInstantiation() {
        $set = new Types\Set(array(1,2,3));
        
        $this->assertInstanceOf(Types\Set::class, $set);
        $this->assertEquals(array(1,2,3), $set->getPlainArray());
    }
    
    public function testUniqueInstantiation() {
        $set = new Types\Set(array(1,2,3),array('unique'=>true));
        
        $this->assertInstanceOf(Types\Set::class, $set);
        $this->assertEquals(array(1,2,3), $set->getPlainArray());
    }
    
    public function testNotReferencedInstantiation() {
        $testref = 7;
        
        $set = new Types\Set(array(1,&$testref,3),array('reference'=>false));
        
        $testref = 3;
        
        $this->assertEquals(array(1,7,3), $set->getPlainArray());
        
        $testref = 7;
        
        $set = new Types\Set(array(1,&$testref,3),array('reference'=>true));
        
        $testref = 3;
        
        $this->assertEquals(array(1,3,3), $set->getPlainArray());
        
        // default behavior is to reference
        
        $testref = 7;
        
        $set = new Types\Set(array(1,&$testref,3));
        
        $testref = 3;
        
        $this->assertEquals(array(1,3,3), $set->getPlainArray());
        
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testUniqueInstantiationWithError() {
        $set = new Types\Set(array(1,2,3,2),array('unique'=>true));
        
    }
    
    public function testReturnPlainArray() {
        $set = new Types\Set(array(1,2,3,2));
        
        $this->assertEquals(array(1,2,3,2), $set->getPlainArray());
        
    }
    
    public function testConstructWithArray() {
        $data = array('testone', 'testtwo', 'testthree');
        $set = new Types\Set($data,array('strict'=>true,'unique'=>true,'reference'=>false));
        
        foreach ($set->getArray() as $key => $value) {
            if (strpos($key, 'Phabstractic\\Data\\Types\\Set::Element') === false) {
                $this->assertFalse(true);
            }
        }
        
    }
    
    public function testEnumerate() {
        $set = new Types\Set(array(1,2,3,2));
        
        $this->assertEquals(array(1,2,3,2), $set->enumerate());
        
    }
    
    public function testReturnArray() {
        $set = new Types\Set(array(1,2,3,2));
        
        $keys = array_keys($set->getArray());
        
        $this->assertEquals(array(
            $keys[0] => 1,
            $keys[1] => 2,
            $keys[2] => 3,
            $keys[3] => 2,), $set->getArray());
            
    }
    
    public function testReturnArrayReference() {
        $testref = 6;
        
        $set = new Types\Set(array(1,2,&$testref,3,4,2));
        
        $arrayref = $set->getArrayReference();
        
        $testref = 7;
        
        $this->assertTrue(in_array(7,$arrayref));
        
    }
    
    public function testIdentifierPrefix() {
        $set = new Types\Set();
        
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
        
        $set = new Types\Set(array(1,2,3,4,2));
        $id = $set->addReference($testref);
        
        $cbtest = &$set->retrieveReference($id);
        
        $cbtest = 7;
        
        $this->assertEquals(7, $testref);
        
    }
    
    /**
     * @depends testReturnPlainArray
     * 
     */
    public function testAdd() {
        $set = new Types\Set();
        
        for ($i = 0; $i < 10; $i += 2) {
            $set->add($i);
        }
        
        $this->assertEquals(array(0,2,4,6,8),$set->getPlainArray());
        
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testAddWithUnique() {
        $set = new Types\Set(array(),array('unique'=>true));
        
        $set->add(1);
        $set->add(2);
        $set->add(3);
        $set->add(2);
        
    }
    
    /**
     * @depends testReturnPlainArray
     * 
     */
    public function testAddReference() {
        $set = new Types\Set();
        
        $testref = 5;
        $testref2 = 3;
        $testref3 = 7;
        
        $set->addReference($testref);
        $set->addReference($testref2);
        $set->addReference($testref3);
        
        $testref = 19;
        
        $this->assertTrue(in_array(19, $set->getPlainArray()));
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testAddReferenceWithUnique() {
        $set = new Types\Set(array(),array('unique'=>true));
        
        $testref = 5;
        $testref2 = 3;
        $testref3 = 3;
        
        $set->addReference($testref);
        $set->addReference($testref2);
        $set->addReference($testref3);
        
    }
    
    /**
     * @depends testReturnPlainArray
     * 
     */
    public function testRemoveValue() {
        $set = new Types\Set(array(1,2,3,4,5,6,7));
        
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
        $set = new Types\Set(array(1,2,3,4,5,6,7));
        
        $set->remove(4);
        
        $this->assertEquals(array(1,2,3,5,6,7), $set->getPlainArray());
        
        $set->add(9);
        $identifier = $set->add(9);
        
        // removes specific identifier element
        
        $set->removeByIdentifier($identifier);
        
        $this->assertEquals(array(1,2,3,5,6,7,9), $set->getPlainArray());
    }
    
    public function testInSet() {
        $set = new Types\Set(array(1,2,3,4,5,6,7));
        
        $this->assertTrue($set->in(4));
        $this->assertFalse($set->in(42));
        
    }
    
    public function testIsEmpty() {
        $set = new Types\Set(array(1,2,3,4,5,6,7));
        
        $this->assertFalse($set->isEmpty());
        
        $set = new Types\Set();
        
        $this->assertTrue($set->isEmpty());
        
    }
    
    public function testSize() {
        $set = new Types\Set(array(1,2,3,4,5,6,7));
        
        $this->assertEquals(7, $set->size());
        
        $set->remove(4);
        
        $this->assertEquals(6, $set->size());
        
    }
    
    public function testIterator() {
        $set = new Types\Set(array(1,2,3,4,5,6,7));
        
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
        $set = new Types\Set(array(1,2,3,4,5,6,7));
        
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
        $set = new Types\Set(array(1,2,3,4,5,6,7));
        
        $set->clear();
        
        $this->assertEmpty($set->getPlainArray());
        
    }
    
    public function testHash() {
        $set = new Types\Set(array(1,2,3,5,6,7,9));
        
        $this->assertEquals('432e03e3a394c1f5240281ac34c847ac', $set->hash());
        
        $set = new Types\Set(array(1,4,2,9,7));
        
        $this->assertEquals('ceb4514aad5416877c1555f0420d2181', $set->hash());
        
    }
    
    public function testStaticEqual() {
        $s1 = new Types\Set(array(1,2,3,5,6,7,9));
        $s2 = new Types\Set(array(1,2,3,5,6,7,9));
        $s3 = new Types\Set(array(1,2,3,7,9));
        
        $this->assertTrue(Types\Set::equal($s1, $s2));
        $this->assertFalse(Types\Set::equal($s1, $s3));
        
        /* for in depth testing of array element comparison see elementComparison
           in ArrayUtilities.php */
    }
    
    public function testStaticFold() {
        $f = function($carry, $item) { return $carry + $item; };
        
        $s1 = new Types\Set(array(1,2,3,5,6,7,9));
        
        // 1+2=3+3=6+5=11+6=17+7=24+9=33
        
        $res = Types\Set::fold($f, $s1);
        
        $this->assertEquals(33, $res);
    }

    public function testStaticFilter() {
        $s1 = new Types\Set(array(1,2,3,5,6,7,8,9));
        
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
        
        $s1 = new Types\Set(array(1,2,3,4,5,6,7,8,9));
        
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
        $s1 = new Types\Set(array(1,2,3,4,5,6,7,8,9));
        
        $userdata = 3;
        
        $f = function (&$var, $key, $userdata) {$var += $userdata;};
        
        $this->assertTrue(Types\Set::walk($s1, $f, $userdata));
        
        $this->assertEquals(array(4,5,6,7,8,9,10,11,12), $s1->getPlainArray());
        
    }
    
    /**
     * @depends testReturnPlainArray
     * 
     */
    public function testStaticBuild() {
        $set = Types\Set::build(array(1,2,3,4,5,6,7,8,9));
        
        $this->assertInstanceOf(Types\Set::class, $set);
        $this->assertEquals(array(1,2,3,4,5,6,7,8,9), $set->getPlainArray());
        
    }
    
    public function testStaticUnion() {
        $s1 = new Types\Set(array(1,3,5,7));
        $s2 = new Types\Set(array(2,4,5,9));
        $a1 = array(5,8,12);
        $a2 = array(8,10,13);
        $v1 = 14;
        
        $this->assertEquals(array(1,3,5,7,2,4,9), Types\Set::union($s1,$s2));
        $this->assertEquals(array(1,3,5,7,8,12), Types\Set::union($s1,$a1));
        $this->assertEquals(array(1,3,5,7,8,12,14,10,13), Types\Set::union($s1,$a1,$v1,$a2));
        
    }
    
    public function testStaticIntersection() {
        $s1 = new Types\Set(array(1,3,5,7,42));
        $s2 = new Types\Set(array(2,7,5,9,42));
        $a1 = array(3,8,12,42);
        $a2 = array(13,1,42);
        $v1 = 42;
        
        $this->assertEquals(array(5,7,42), array_merge(Types\Set::intersection($s1,$s2), array()));
        $this->assertEquals(array(3,42), array_merge(Types\Set::intersection($s1,$a1), array()));
        $this->assertEquals(array(42), array_merge(Types\Set::intersection($s1,$s2,$a1,$a2,$v1), array()));
        
    }
    
    public function testStaticDifference() {
        $s1 = new Types\Set(array(1,3,5,7));
        $s2 = new Types\Set(array(2,7,5,9));
        $a1 = array(3,8,12);
        $v1 = 1;
        
        $this->assertEquals(array(1,3), array_merge(Types\Set::difference($s1,$s2)));
        $this->assertEquals(array(1), array_merge(Types\Set::difference($s1,$s2,$a1)));
        $this->assertEmpty(Types\Set::difference($s1,$s2,$a1,$v1));
        
    }
    
    public function testStaticSubset() {
        $s1 = new Types\Set(array(1,3,5,7,2,5,9));
        $s2 = new Types\Set(array(2,7,5,9));
        $s3 = new Types\Set(array(2,7,5,13));
        
        $this->assertTrue(Types\Set::subset($s1, $s2));
        $this->assertFalse(Types\Set::subset($s3, $s1));
        
    }
    
    public function testSetDebugInfo() {
        $s1 = new Types\Set(array(1,3,5,7,2,5,9));
        
        ob_start();
        
        var_dump($s1);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?reference\"?\]?.*=\\>\n.*bool\\(true\\)/", $output);

    }
    
}
