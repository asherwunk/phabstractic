<?php

require_once('src/Phabstractic/Resource/ArrayUtilities.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Resource as PhabstracticResource;

class FirstArrayUtilitiesTestClass {
    
}

class SecondArrayUtilitiesTestClass {
    
}

class ThirdArrayUtilitiesTestClass {
    public $testProperty = 0;
}

class ArrayUtilitiesTest extends TestCase
{
    public function testReturnUnique()
    {
        $arr = array(1,2,3,2,6,7,1,6);
        
        $arr = PhabstracticResource\ArrayUtilities::returnUnique($arr);
        
        $this->assertEquals(array(1,2,3,6,7), $arr);
        
        $arr = array('one'=>1,'two'=>2,'three'=>3,'dup'=>2,'dup2'=>1,'four'=>4);
        
        $arr = PhabstracticResource\ArrayUtilities::returnUnique($arr, true);
        
        $this->assertEquals(array('one'=>1,'two'=>2,'three'=>3,'four'=>4), $arr);
        
    }

    public function testReturnUniqueByReference()
    {
        $arr = array(1,2,3,2,6,7,1,6);
        
        PhabstracticResource\ArrayUtilities::returnUniqueByReference($arr);
        
        $this->assertEquals(array(1,2,3,6,7), $arr);
        
        $arr = array('one'=>1,'two'=>2,'three'=>3,'dup'=>2,'dup2'=>1,'four'=>4);
        
        PhabstracticResource\ArrayUtilities::returnUniqueByReference($arr, true);
        
        $this->assertEquals(array('one'=>1,'two'=>2,'three'=>3,'four'=>4), $arr);
        
        $testref = 42;
        
        $arr = array(1,2,&$testref,3,4,2,&$testref);
        
        PhabstracticResource\ArrayUtilities::returnUniqueByReference($arr);
        
        $testref = 21;
        
        $this->assertEquals(array(1,2,21,3,4), $arr);
        
    }
    
    public function testElementComparison() {
        $a = new FirstArrayUtilitiesTestClass();
        $b = new SecondArrayUtilitiesTestClass();
        $c = new ThirdArrayUtilitiesTestClass();
        $c->testProperty = 5;
        $d = new ThirdArrayUtilitiesTestClass();
        $d->testProperty = 2;
        $e = new ThirdArrayUtilitiesTestClass();
        $e->testProperty = 5;
        
        $this->assertEquals(0, PhabstracticResource\ArrayUtilities::elementComparison($a,$a));
        
        $this->assertThat(
          PhabstracticResource\ArrayUtilities::elementComparison($a,$b),
          $this->logicalNot(
            $this->equalTo(0)
          )
        );
        
        $this->assertThat(
          PhabstracticResource\ArrayUtilities::elementComparison($c,$d),
          $this->logicalNot(
            $this->equalTo(0)
          )
        );
        
        $this->assertThat(
          PhabstracticResource\ArrayUtilities::elementComparison($e,$c),
          $this->logicalNot(
            $this->equalTo(0)
          )
        );
        
        $a = 1;
        $b = 2;
        $c = 3;
        $d = 1;
        
        $this->assertEquals(0, PhabstracticResource\ArrayUtilities::elementComparison($a,$d));
        $this->assertEquals(-1, PhabstracticResource\ArrayUtilities::elementComparison($a,$b));
        $this->assertEquals(1, PhabstracticResource\ArrayUtilities::elementComparison($c,$a));
        
    }
    
    public function testObjectToArray() {
        $obj = new stdClass();
        $obj->one = 1;
        $obj->two = 2;
        $obj->three = new stdClass();
        $obj->three->sub = 'sub';
        
        $this->assertEquals(array('one'=>1,'two'=>2,'three'=>array('sub'=>'sub')),
            PhabstracticResource\ArrayUtilities::objectToArray($obj));
            
    }
    
    public function testArrayToObject() {
        $arr = array( 'one' => 1,
              'two' => array( 'three' => 3,
                              'four' => 4,),
              'five' => 5,
              'six' => 6,
              'seven' => array( 'eight' => 8,
                                'nine' => array( 'ten' => 10),),
              'eleven' => 11,);
        
        $cmp = new stdClass();
        $cmp->one = 1; $cmp->two = new stdClass();
        $cmp->two->three = 3; $cmp->two->four = 4;
        $cmp->five = 5; $cmp->six = 6;
        $cmp->seven = new stdClass();
        $cmp->seven->eight = 8;
        $cmp->seven->nine = new stdClass();
        $cmp->seven->nine->ten = 10;
        $cmp->eleven = 11;

        $this->assertEquals($cmp, PhabstracticResource\ArrayUtilities::arrayToObject($arr));
    }

    public function testChangeValueCase() {
        $arr = array( 'key1' => 'ValUe', 'key2' => 'vaLuE2', 'anotherkey' => 'aNothErVAluE');
        
        $this->assertEquals(array('key1'=>'value','key2'=>'value2','anotherkey'=>'anothervalue'),
            PhabstracticResource\ArrayUtilities::arrayChangeValueCase($arr));
        
        $this->assertEquals(array('key1'=>'VALUE','key2'=>'VALUE2','anotherkey'=>'ANOTHERVALUE'),
            PhabstracticResource\ArrayUtilities::arrayChangeValueCase($arr, CASE_UPPER));
        
        $arr2 = array( 'key1' => array( 'innerkey' => 'ValUe', 'innerkey2' => 'valUE2' ), 'anotherkey' => 'AnoThErValuE');
        
        $this->assertEquals(array('key1'=>array('innerkey' => 'value', 'innerkey2' => 'value2'),'anotherkey'=>'anothervalue'),
            PhabstracticResource\ArrayUtilities::arrayChangeValueCase($arr2));
    }

}
