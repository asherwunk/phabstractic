<?php

require_once('src/Phabstractic/Data/Types/Enumeration.php');

use Phabstractic\Data\Types;

$enum = new Types\Enumeration(
            'TestEnumInstance',
            array('ONE'=>1,'TWO'=>2,'RED'=>'red','BLUE'=>'blue'),
            array('default'=>'ONE',
                  'namespace'=>'TestNamespace',
                  'bake'=>true)
        );
        
$value = $enum->getInstance('TWO');

