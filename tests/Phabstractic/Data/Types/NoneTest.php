<?php

require_once('src/Phabstractic/Data/Types/None.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;

class NoneTest extends TestCase
{
    protected $none;

    protected function setUp()
    {
        $this->none = new Types\None();
    }

    public function testConstant()
    {
        $this->assertNull(Types\None::NULL);
    }

    public function testProperty()
    {
        $this->assertNull($this->none->null);
    }

    public function testMethod()
    {
        $this->assertNull($this->none->null());
    }
    
    public function testDebugInfo() {
        ob_start();
        
        var_dump($this->none);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\"?NONE\"?.*=\>\n.*NULL/", $output);
        
    }
    
}
