<?php

require_once('src/Data/Types/None.php');

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
}
