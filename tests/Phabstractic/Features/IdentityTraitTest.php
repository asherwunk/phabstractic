<?php

require_once('src/Phabstractic/Features/IdentityTrait.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Features;

class TestIdentityClass {
    use Features\IdentityTrait {getNewIdentity as public;}
    
    public function changePrefix($prefix) {
        $this->identityPrefix = $prefix;
    }
}

class IdentityTraitTest extends TestCase
{
    public function testIdentityStaticProperty() {
        $obj1 = new TestIdentityClass();
        $obj2 = new TestIdentityClass();
        $obj3 = new TestIdentityClass();
        
        $this->assertEquals(1, $obj1->getNewIdentity());
        $this->assertEquals(2, $obj2->getNewIdentity());
        $this->assertEquals(3, $obj3->getNewIdentity());
        $this->assertEquals(4, $obj2->getNewIdentity());
        
        return array( $obj2, $obj3 );
    }
    
    /**
     * @depends testIdentityStaticProperty
     * 
     */
    public function testIdentityFeaturePrefix($arr) {
        list( $obj2, $obj3 ) = $arr;
        $obj2->changePrefix('test');
        $this->assertEquals('test5', $obj2->getNewIdentity());
        $this->assertEquals('6', $obj3->getNewIdentity());
    }
    
    
}