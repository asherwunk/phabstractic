<?php

require_once('src/Phabstractic/Data/Types/Map.php');
require_once('src/Phabstractic/Data/Types/None.php');
require_once('src/Phabstractic/Resource/ArrayUtilities.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Resource as PhabstracticResource;
use Phabstractic\Features\Resource as FeaturesResource;

class TestMapKeyClass {
    
}

class MapTest extends TestCase
{
    
    public function testBasicEmptyInstantiation() {
        $map = new Types\Map();
        
        $this->assertInstanceOf(Types\Map::class, $map);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $map);
        
    }
    
    public function testStdClassInstantiation() {
        $pair1 = new stdClass();
        $pair1->key = 1;
        $pair1->value = 5;
        $pair2 = new stdClass();
        $tk = new TestMapKeyClass();
        $pair2->key = $tk;
        $pair2->value = 'test';
        $map = new Types\Map(array($pair1, $pair2));
        
        $this->assertEquals(array(5, 'test'), $map->getValues());
        $this->assertEquals(array(1, $tk), $map->getKeys());
        $this->assertEquals(array(array(1, 5), array($tk, 'test')), $map->flatten());
        
        $this->assertInstanceOf(Types\Map::class, $map);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $map);
    }
    
    public function testArrayInstantiation() {
        $pair1 = array();
        $pair1['key'] = 1;
        $pair1['value'] = 5;
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $map = new Types\Map(array($pair1, $pair2));
        
        $this->assertEquals(array(5, 'test'), $map->getValues());
        $this->assertEquals(array(1, $tk), $map->getKeys());
        $this->assertEquals(array(array(1, 5), array($tk, 'test')), $map->flatten());
        
        $this->assertInstanceOf(Types\Map::class, $map);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $map);
    }
    
    public function testStringInstantiation() {
        $pair1 = '1=5';
        $pair2 = 'a=b';
        $map = new Types\Map(array($pair1, $pair2));
        
        $this->assertEquals(array(5, 'b'), $map->getValues());
        $this->assertEquals(array(1, 'a'), $map->getKeys());
        $this->assertEquals(array(array(1, 5), array('a', 'b')), $map->flatten());
        
        $this->assertInstanceOf(Types\Map::class, $map);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $map);
    }
    
    public function testMixedInstantiation() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        $this->assertEquals(array(5, 'test', 'stringvalue'), $map->getValues());
        $this->assertEquals(array(1, $tk, 'stringkey'), $map->getKeys());
        $this->assertEquals(array(
                array(1, 5),
                array($tk, 'test'),
                array('stringkey', 'stringvalue')
            ), $map->flatten());
        
        $this->assertInstanceOf(Types\Map::class, $map);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $map);
    }
    
    public function testIteration() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        $testkeys = array();
        $testvalues = array();
        
        foreach ($map as $key => $value) {
            $testkeys[] = $key;
            $testvalues[] = $value;
        }
        
        $this->assertEmpty(
            array_udiff(array('1', $tk, 'stringkey'), $testkeys,
                array('Phabstractic\Resource\ArrayUtilities', 'elementComparison'))
        );
        
        $this->assertEmpty(
            array_udiff(array('5', 'test', 'stringvalue'), $testvalues,
                array('Phabstractic\Resource\ArrayUtilities', 'elementComparison'))
        );
        
    }
    
    public function testExists() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        $this->assertTrue($map->exists('1'));
        $this->assertFalse($map->exists(2));
        $this->assertTrue($map->exists($tk));
        $this->assertFalse($map->exists($sc));
        
    }
    
    public function testSet() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        $map->set('1', 8);
        
        $this->assertEquals(array(
                array(1, 8),
                array($tk, 'test'),
                array('stringkey', 'stringvalue')
            ), $map->flatten());
        
        $map->set($tk, 'newtest');
        
        $this->assertEquals(array(
                array(1, 8),
                array($tk, 'newtest'),
                array('stringkey', 'stringvalue')
            ), $map->flatten());
        
        $map->set($sc, 'newvalue');
        
        $this->assertEquals(array(
                array(1, 8),
                array($tk, 'newtest'),
                array('stringkey', 'stringvalue'),
                array($sc, 'newvalue')
            ), $map->flatten());
    }
    
    public function testProperRemove() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        $map->remove($tk);
        
        $this->assertEquals(array(
                array(1, 5),
                array('stringkey', 'stringvalue'),
            ), $map->flatten());
    }
    
    public function testImproperRemoveNoStrict() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        $map->remove($sc);
        
        $this->assertEquals(array(
                array(1, 5),
                array($tk, 'test'),
                array('stringkey', 'stringvalue'),
            ), $map->flatten());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperRemoveWithStrict() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3), array('strict' => true));
        
        $map->remove($sc);
        
    }
    
    public function testProperFind() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        $this->assertEquals('test', $map->find($tk));
        $this->assertEquals('stringvalue', $map->find('stringkey'));
        $this->assertEquals('5', $map->find('1'));
        
    }
    
    public function testImproperFindNoStrict() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        $this->assertInstanceOf(Types\None::class, $map->find($sc));
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperFindWithStrict() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3), array('strict' => true));
        
        $i = $map->find($sc);
        
    }
    
    public function testProperFindReference() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        $testref = &$map->findReference($tk);
        
        $testref = 'modified';
        
        $this->assertEquals('modified', $map->find($tk));
        
    }
    
    public function testImproperFindReferenceNoStrict() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        $this->assertInstanceOf(Types\None::class, $map->findReference($sc));
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testImproperFindReferenceWithStrict() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3), array('strict' => true));
        
        $i = $map->findReference($sc);
        
    }
    
    // TEST ARRAY ACCESS FUNCTIONS
    
    public function testArrayAccessProperGet() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        $this->assertEquals('test', $map[$tk]);
        $this->assertEquals('stringvalue', $map['stringkey']);
        $this->assertEquals('5', $map['1']);
        
    }
    
    public function testArrayAccessImproperGetNoStrict() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        $this->assertInstanceOf(Types\None::class, $map[$sc]);
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testArrayAccessImproperGetWithStrict() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3), array('strict' => true));
        
        $i = $map[$sc];
        
    }
    
    public function testArrayAccessSet() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3), array('strict' => true));
        
        $map[$tk] = 'newtest';
        $map['stringkey'] = 'newstringvalue';
        $map['1'] = 8;
        
        $this->assertEquals(array(
                array(1, 8),
                array($tk, 'newtest'),
                array('stringkey', 'newstringvalue'),
            ), $map->flatten());
        
        $map['newproperkey'] = 'newpropervalue';
        
        $this->assertEquals(array(
                array(1, 8),
                array($tk, 'newtest'),
                array('stringkey', 'newstringvalue'),
                array('newproperkey', 'newpropervalue'),
            ), $map->flatten());
        
        $map[] = 'newgeneratedkeyvalue';
        
        $this->assertEquals(array(
                array(1, 8),
                array($tk, 'newtest'),
                array('stringkey', 'newstringvalue'),
                array('newproperkey', 'newpropervalue'),
                array(0, 'newgeneratedkeyvalue',)
            ), $map->flatten());
        
        $map[] = 'newgeneratedkeyvaluetwo';
        
        $this->assertEquals(array(
                array(1, 8),
                array($tk, 'newtest'),
                array('stringkey', 'newstringvalue'),
                array('newproperkey', 'newpropervalue'),
                array(0, 'newgeneratedkeyvalue'),
                array(2, 'newgeneratedkeyvaluetwo'),
            ), $map->flatten());
    }
    
    public function testArrayAccessProperUnset() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        unset($map[1]);
        
        $this->assertEquals(array(
                array($tk, 'test'),
                array('stringkey', 'stringvalue'),
            ), $map->flatten());
    }
    
    public function testArrayAccessImproperUnsetNoStrict() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        unset($map[$sc]);
        
        $this->assertEquals(array(
                array(1, 5),
                array($tk, 'test'),
                array('stringkey', 'stringvalue'),
            ), $map->flatten());
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\RangeException
     * 
     */
    public function testArrayAccessImproperUnsetWithStrict() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3), array('strict' => true));
        
        unset($map[$sc]);
        
    }
    
    public function testArrayAccessExists() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map = new Types\Map(array($pair1, $pair2, $pair3));
        
        $this->assertTrue(isset($map[1]));
        $this->assertTrue(isset($map['1']));
        $this->assertTrue(isset($map[$tk]));
        $this->assertFalse(isset($map[$sc]));
        $this->assertFalse(isset($map[2]));
        
    }
    
    public function testStaticDifference() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map1 = new Types\Map(array($pair1, $pair2, $pair3));
        $map2 = new Types\Map(array($pair2, $pair3));
        
        $result = Types\Map::difference($map1, $map2);
        
        $this->assertEquals(array(
                array(1, 5),
            ), $result->flatten());
        
        $map2 = new Types\Map(array($pair1, $pair3));
        
        $result = Types\Map::difference($map1, $map2);
        
        $this->assertEquals(array(
                array($tk, 'test'),
            ), $result->flatten());
        
        $map2 = new Types\Map(array($pair1, $pair2));
        
        $result = Types\Map::difference($map1, $map2);
        
        $this->assertEquals(array(
                array('stringkey', 'stringvalue'),
            ), $result->flatten());
        
        $map3 = new Types\Map(array($pair3));
        
        $result = Types\Map::difference($map1, $map2, $map3);
        
        $this->assertEmpty($result->flatten());
        
    }
    
    public function testStaticIntersection() {
        $pair1 = '1=5';
        $pair2 = array();
        $tk = new TestMapKeyClass();
        $sc = new StdClass();
        $pair2[0] = $tk;
        $pair2[1] = 'test';
        $pair3 = new stdClass();
        $pair3->key = 'stringkey';
        $pair3->value = 'stringvalue';
        $map1 = new Types\Map(array($pair1, $pair2, $pair3));
        
        $map2 = new Types\Map(array($pair2));
        
        $result = Types\Map::intersect($map1, $map2); // should be $pair2
        
        $this->assertEquals(array(
                array($tk, 'test')
            ), $result->flatten());
        
        $map2 = new Types\Map(array($pair3));
        
        $result = Types\Map::intersect($map1, $map2); // should be $pair3
        
        $this->assertEquals(array(
                array('stringkey', 'stringvalue')
            ), $result->flatten());
        
        $map2 = new Types\Map(array($pair2));
        $map3 = new Types\Map(array($pair3));
        
        $result = Types\Map::intersect($map1, $map2, $map3); // should be empty
        
        $this->assertEmpty($result->flatten());
        
    }
    
    public function testStaticCombine() {
        $tk = new TestMapKeyClass();
        $map = Types\Map::combine(array(1, $tk, 'stringkey'), array(5, 'test', 'stringvalue'));
        
        $this->assertEquals(array(
                array(1, 5),
                array($tk, 'test'),
                array('stringkey', 'stringvalue'),
            ), $map->flatten());
            
    }
    
    /**
     * @depends testStaticCombine
     *
     */
    public function testStaticCountValues() {
        $tk = new TestMapKeyClass();
        $map = Types\Map::combine(array(1, 2, 3, 4, 5, 6, 7, 8, 9),
                                  array($tk, 'string', $tk, $tk, 'string', 'string', $tk, $tk, 'string'));
        
        $result = Types\Map::countValues($map);
        
        $this->assertEquals(array(
                array($tk, 5),
                array('string', 4),
            ), $result->flatten());
    }
    
    /**
     * @depends testStaticCombine
     * 
     */
    public function testStaticMerge() {
        $map1 = Types\Map::combine(array(1,2,3,4,5,6,7,8,9),
            array('one','two','three','four','five','six','seven','eight','nine'));
        
        $map2 = Types\Map::combine(array(6,7),array('newsix','newseven'));
        
        $map3 = Types\Map::combine(array(12,15),array('twelve','fifteen'));
        
        $result = Types\Map::merge($map1, $map2);
        
        $this->assertEquals(array(
                array(1, 'one'),
                array(2, 'two'),
                array(3, 'three'),
                array(4, 'four'),
                array(5, 'five'),
                array(6, 'newsix'),
                array(7, 'newseven'),
                array(8, 'eight'),
                array(9, 'nine'),
            ), $result->flatten());
            
        $result = Types\Map::merge($map1, $map3);
        
        $this->assertEquals(array(
                array(1, 'one'),
                array(2, 'two'),
                array(3, 'three'),
                array(4, 'four'),
                array(5, 'five'),
                array(6, 'six'),
                array(7, 'seven'),
                array(8, 'eight'),
                array(9, 'nine'),
                array(12, 'twelve'),
                array(15, 'fifteen'),
            ), $result->flatten());
        
        $result = Types\Map::merge($map1, $map2, $map3);
        
        $this->assertEquals(array(
                array(1, 'one'),
                array(2, 'two'),
                array(3, 'three'),
                array(4, 'four'),
                array(5, 'five'),
                array(6, 'newsix'),
                array(7, 'newseven'),
                array(8, 'eight'),
                array(9, 'nine'),
                array(12, 'twelve'),
                array(15, 'fifteen'),
            ), $result->flatten());
    }
    
}