<?php

require_once('src/Phabstractic/Data/Types/Resource/AbstractFilter.php');
require_once('src/Phabstractic/Data/Types/Exception/InvalidArgumentException.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types\Resource as TypesResource;
use Phabstractic\Data\Types\Exception as TypesException;

class TestFilterClass extends TypesResource\AbstractFilter {
    
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
        
    }
}

class TestFilterNoThrowClass extends TypesResource\AbstractFilter {
    
    public function isAllowed($type, $strict = false) {
        if ($type == 'allowed') {
            return true;
        } else {
            return false;
        }
    }
    
    public static function getDefaultRestrictions() {
        
    }
}

class AbstractFilterTest extends TestCase
{
    public function testFilterInstantiation() {
        $filter = new TestFilterClass();
    }
    
    public function testFilterAllowed() {
        $filter = new TestFilterClass();
        
        $this->assertTrue($filter->isAllowed('allowed'));
        $this->assertFalse($filter->isAllowed('notallowed'));
        
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testFilterAllowedException() {
        $filter = new TestFilterClass();
        
        $filter->isAllowed('notallowed', true);
        
    }
    
    /**
     * @expectedException \Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testFilterStaticAllowedException() {
        $filter = new TestFilterNoThrowClass();
        
        TypesResource\AbstractFilter::checkElements(array('notallowed'),$filter,true);
        
    }
    
    public function testFilterStaticAllowed() {
        $filter = new TestFilterNoThrowClass();
        
        $this->assertTrue(TypesResource\AbstractFilter::checkElements(array('allowed'),$filter));
        $this->assertFalse(TypesResource\AbstractFilter::checkElements(array('notallowed'),$filter));
        
    }
    
}
