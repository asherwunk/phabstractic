<?php

require_once('src/Features/ConfigurationTrait.php');
require_once('src/Features/Resource/ConfigurationInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Features;
use Phabstractic\Features\Resource as FeaturesResource;
use Zend\Config;

define('TESTCONFIGURABLE_CONSTANT', 'testconstant');

class TestConfigurable implements FeaturesResource\ConfigurationInterface {
    use Features\ConfigurationTrait;
    
    public $configReaders = array();
    
    public $configWriters = array();
    
    public function __construct($options) {
        
        $this->configure($options);
        
    }
    
    public function getConfiguration() {
        return $this->conf;
    }
}

class TestConfigurableReader {
    
}

class TestConfigurableWriter {
    
}

class TestConfigurableProcessor {
    
}

class ConfigurationTraitTest extends TestCase
{
    public function testInstantiation() {
        $testobject = new TestConfigurable(array('option1'=>array('suboption'=>4),'option2'=>3));
        
        $this->assertTrue($testobject instanceof Features\Resource\ConfigurationInterface);
        
        return $testobject;
    }
    
    /**
     * @depends testInstantiation
     *
     */
    public function testIniString($testobject) {
        $inistring = $testobject->getSettings('ini');
        
        $this->assertEquals("option2 = 3\n[option1]\nsuboption = 4\n\n", $inistring);
    }
    
    public function testIniSetString() {
        $testobject = new TestConfigurable(
            array(
                'configuration' => "option2 = 3\n[option1]\nsuboption = 4\n\n",
                '#confformat' => 'ini',
            )
        );
        
        $this->assertEquals(4, $testobject->getConfiguration()->option1->suboption);
        
    }
    
    /**
     * @depends testInstantiation
     *
     */
    public function testXMLString($testobject) {
        $xmlstring = $testobject->getSettings('xml');
        
        $this->assertEquals("<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<zend-config>
    <option1>
        <suboption>4</suboption>
    </option1>
    <option2>3</option2>
</zend-config>\n", $xmlstring);

    }
    
    public function testXMLSetString() {
        $testobject = new TestConfigurable(
            array(
                'configuration' => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<zend-config>
    <option1>
        <suboption>4</suboption>
    </option1>
    <option2>3</option2>
</zend-config>\n",
                '#confformat' => 'xml',
            )
        );
        
        $this->assertEquals(4, $testobject->getConfiguration()->option1->suboption);
        
    }
    
    /**
     * @depends testInstantiation
     * 
     */
    public function testArrayString($testobject) {
        $arraystring = $testobject->getSettings('array');
        
        $this->assertEquals("<?php
return array (
  'option1' => 
  array (
    'suboption' => 4,
  ),
  'option2' => 3,
);\n", $arraystring);
    }
    
    public function testJSONSetString() {
        $testobject = new TestConfigurable(
            array(
                'configuration' => '{"option1":{"suboption":4},"option2":3}',
                '#confformat' => 'json',
            )
        );
        
        $this->assertEquals(4, $testobject->getConfiguration()->option1->suboption);
        
    }
    
    /**
     * @depends testInstantiation
     * 
     */
    public function testJSONString($testobject) {
        $jsonstring = $testobject->getSettings('json');
        
        $this->assertEquals('{"option1":{"suboption":4},"option2":3}', $jsonstring);
        
    }
    
    /**
     * @depends testInstantiation
     * 
     */
    public function testYamlString($testobject) {
        $yamlstring = $testobject->getSettings('yaml', array('Spyc','YAMLDump'));
        
        $this->assertEquals("---
option1:
  suboption: 4
option2: 3\n", $yamlstring);

    }
    
    public function testYamlSetString() {
        $testobject = new TestConfigurable(
            array(
                'configuration' => "---
option1:
  suboption: 4
option2: 3\n",
                '#confformat' => 'yaml',
                '#confcontext' => array('Spyc','YAMLLoadString'),
            )
        );
        
        $this->assertEquals(4, $testobject->getConfiguration()->option1->suboption);
        
    }
    
    /**
     * @depends testInstantiation
     *
     */
    public function testConfigConfigPassing($testobject) {
        $testobject->configure($testobject->getConfiguration());
        
        $this->assertInstanceOf(\Zend\Config\Config::class, $testobject->getConfiguration());
        
    }
    
    /**
     * @expectedException \Phabstractic\Features\Exception\ClassDependencyException
     * 
     */
    public function testYamlSetLackContext() {
        $testobject = new TestConfigurable(
            array(
                'configuration' => "---
option1:
  suboption: 4
option2: 3\n",
                '#confformat' => 'yaml',
            )
        );
    }
    
    /**
     * @depends testInstantiation
     * @expectedException \Phabstractic\Features\Exception\ClassDependencyException
     *
     */
    public function testNonexistentReader($testobject) {
        $testobject->configReaders = array('test' => '\\Zend\\Config\\Reader\\Test',);
        
        $testobject->configure(array('configuration'=>'{"option1":{"suboption":4},"option2":3}',
                                     '#confformat'=>'test',));
    }
    
    /**
     * @depends testInstantiation
     * @expectedException \Phabstractic\Features\Exception\ClassDependencyException
     * 
     */
    public function testWrongReaderInterface($testobject) {
        $testobject->configReaders = array('test' => 'TestConfigurableReader',);
        
        $testobject->configure(array('configuration'=>'{"option1":{"suboption":4},"option2":3}',
                                     '#confformat'=>'test',));
                                     
    }
    
    /**
     * @depends testInstantiation
     * @expectedException \Phabstractic\Features\Exception\ClassDependencyException
     * 
     */
    public function testNonexitentWriter($testobject) {
        $testobject->configWriters = array('test' => '\\Zend\\Config\\Writer\\Test',);
        
        $teststring = $testobject->getSettings('test');
    }
    
    /**
     * @depends testInstantiation
     * @expectedException \Phabstractic\Features\Exception\ClassDependencyException
     * 
     */
    public function testWrongWriterInterface($testobject) {
        $testobject->configWriters = array('test' => 'TestConfigurableWriter',);
        
        $teststring = $testobject->getSettings('test');
        
    }
    
    /**
     * @depends testInstantiation
     * @expectedException \Phabstractic\Features\Exception\ClassDependencyException
     *
     */
    public function testWrongProcessorInterface($testobject) {
        $testobject->processSettings(new TestConfigurableProcessor());
        
    }
    
    public function testConfigurationProcessing() {
        $testobject = new TestConfigurable(array('option1'=>array('suboption'=>'TESTCONFIGURABLE_CONSTANT'),'option2'=>3));
        
        $testobject->processSettings(new Config\Processor\Constant());
        
        $this->assertEquals('testconstant', $testobject->getConfiguration()->option1->suboption);
        
    }
    
}
