<?php
require_once('src/Phabstractic/Data/Types/Leaf.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractLeaf.php');
require_once('src/Phabstractic/Data/Types/Resource/LeafInterface.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');
require_once('src/Phabstractic/Data/Types/Exception/InvalidArgumentException.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Resource as TypesResource;
use Phabstractic\Features\Resource as FeaturesResource;

class LeafTest extends TestCase
{
    
    public function testEmptyConstruction()
    {
        $leaf = new Types\Leaf();
        
        $this->assertInstanceOf(Types\Leaf::class, $leaf);
        $this->assertInstanceOf(TypesResource\AbstractLeaf::class, $leaf);
        $this->assertInstanceOf(TypesResource\LeafInterface::class, $leaf);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $leaf);
    }
    
    public function testBasicConstruction()
    {
        $leaf1 = new Types\Leaf();
        $leaf2 = new Types\Leaf();
        $leaf3 = new Types\Leaf();
        
        $leaf = new Types\Leaf(null, array($leaf1, $leaf2, $leaf3));
        
        $this->assertEquals(array($leaf1, $leaf2, $leaf3), array_values($leaf->getLeaves()));
        
    }
    
    public function testBasicConstructionWithData()
    {
        $leaf1 = new Types\Leaf('data1');
        $leaf2 = new Types\Leaf(5);
        $leaf3 = new Types\Leaf(true);
        
        $leaf = new Types\Leaf(array(), array($leaf1, $leaf2, $leaf3));
        
        $this->assertEquals(array($leaf1, $leaf2, $leaf3), array_values($leaf->getLeaves()));
        
        $data = $leaf->getLeavesData();
        
        $this->assertEquals(array('data1', 5, true), $data);
    }
    
    public function testConstructionWithPrefix()
    {
        $leaf1 = new Types\Leaf();
        $leaf2 = new Types\Leaf();
        $leaf3 = new Types\Leaf();
        
        $leaf = new Types\Leaf(null, array($leaf1, $leaf2, $leaf3), array('prefix' => 'testprefix'));
        
        foreach ($leaf->getLeaves() as $key => $value) {
            $this->assertEquals(0, strpos($key, 'testprefix'));
        }
    }
    
    public function testProperConstructionWithStrict()
    {
        $leaf1 = new Types\Leaf();
        $leaf2 = new Types\Leaf();
        $leaf3 = new Types\Leaf();
        
        $leaf = new Types\Leaf(null, array($leaf1, $leaf2, $leaf3), array('strict' => true));
        
        $this->assertEquals(array($leaf1, $leaf2, $leaf3), array_values($leaf->getLeaves()));
        
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperConstructionWithStrict()
    {
        $leaf1 = new Types\Leaf();
        $leaf2 = new Types\Leaf();
        
        $leaf = new Types\Leaf(null, array($leaf1, $leaf2, 5), array('strict' => true));
        
    }
    
    public function testAddLeaf() {
        $leaf1 = new Types\Leaf();
        $leaf2 = new Types\Leaf();
        $leaf3 = new Types\Leaf();
        
        $leaf = new Types\Leaf(null, array($leaf1, $leaf2));
        
        $leaf->addLeaf($leaf3);
        
        $this->assertEquals(array($leaf1, $leaf2, $leaf3), array_values($leaf->getLeaves()));
    }
    
    /**
     * @expectedException Phabstractic\Data\Types\Exception\InvalidArgumentException
     * 
     */
    public function testImproperAddLeaf() {
        $leaf1 = new Types\Leaf();
        $leaf2 = new Types\Leaf();
        
        $leaf = new Types\Leaf(null, array($leaf1, $leaf2), array('strict' => true));
        
        $leaf->addLeaf($leaf);
        
    }
    
    public function testRemoveLeaf() {
        $leaf1 = new Types\Leaf();
        $leaf2 = new Types\Leaf();
        $leaf3 = new Types\Leaf();
        
        $leaf = new Types\Leaf(null, array($leaf1, $leaf2, $leaf3));
        
        $leaf->removeLeaf($leaf2);
        
        $this->assertEquals(array($leaf1, $leaf3), array_values($leaf->getLeaves()));
    }
    
    public function testIsLeaf() {
        $leaf1 = new Types\Leaf();
        $leaf2 = new Types\Leaf();
        $leaf3 = new Types\Leaf();
        $leaf4 = new Types\Leaf();
        
        $leaf = new Types\Leaf(null, array($leaf1, $leaf2, $leaf3));
        
        $this->assertTrue((bool) $leaf->isLeaf($leaf1));
        $this->assertFalse((bool) $leaf->isLeaf($leaf4));
        
    }
    
    public function testGetLeafIdentifier() {
        $leaf1 = new Types\Leaf();
        $leaf2 = new Types\Leaf();
        $leaf3 = new Types\Leaf();
        
        $leaf = new Types\Leaf(null, array($leaf1, $leaf2, $leaf3), array('prefix' => 'testprefix'));
        
        $this->assertEquals(0, strpos($leaf->getLeafIdentifier(), 'testprefix'));
        
    }
    
    public function testGetLeafIdentityPaths() {
        $leaf1 = new Types\Leaf(null, array(), array('prefix' => 'Leaf1_'));
        $leaf2 = new Types\Leaf(null, array($leaf1), array('prefix' => 'Leaf2_'));
        $leaf3 = new Types\Leaf(null, array($leaf2), array('prefix' => 'Leaf3_'));
        $leaf4 = new Types\Leaf(null, array(), array('prefix' => 'Leaf4_'));
        $leaf5 = new Types\Leaf(null, array(), array('prefix' => 'Leaf5_'));
        $leaf6 = new Types\Leaf(null, array($leaf4, $leaf5), array('prefix' => 'Leaf6_'));
        $leaf7 = new Types\Leaf(null, array($leaf6), array('prefix' => 'Leaf7_'));
        $leaf8 = new Types\Leaf(null, array($leaf3, $leaf7), array('prefix' => 'Leaf8_'));
        
        $paths = Types\Leaf::getLeafIdentityPaths($leaf8);
        
        $this->assertEquals(3, count($paths));
        
    }
    
    public function testGetFromLeafIdentityPaths() {
        $leaf1 = new Types\Leaf(null, array(), array('prefix' => 'Leaf1_'));
        $leaf2 = new Types\Leaf(null, array($leaf1), array('prefix' => 'Leaf2_'));
        $leaf3 = new Types\Leaf(null, array($leaf2), array('prefix' => 'Leaf3_'));
        $leaf4 = new Types\Leaf(null, array(), array('prefix' => 'Leaf4_'));
        $leaf5 = new Types\Leaf(null, array(), array('prefix' => 'Leaf5_'));
        $leaf6 = new Types\Leaf(null, array($leaf4, $leaf5), array('prefix' => 'Leaf6_'));
        $leaf7 = new Types\Leaf(null, array($leaf6), array('prefix' => 'Leaf7_'));
        $leaf8 = new Types\Leaf(null, array($leaf3, $leaf7), array('prefix' => 'Leaf8_'));
        
        $paths = Types\Leaf::getLeafIdentityPaths($leaf8);
        
        $this->assertEquals(0, strpos(Types\Leaf::getFromLeafIdentityPath($leaf8, $paths[0])->getLeafIdentifier(), 'Leaf1'));
        $this->assertEquals(0, strpos(Types\Leaf::getFromLeafIdentityPath($leaf8, $paths[1])->getLeafIdentifier(), 'Leaf4'));
        $this->assertEquals(0, strpos(Types\Leaf::getFromLeafIdentityPath($leaf8, $paths[2])->getLeafIdentifier(), 'Leaf5'));
        
    }
    
    public function testGetAsArray() {
        $leaf1 = new Types\Leaf(null, array(), array('prefix' => 'Leaf1_'));
        $leaf2 = new Types\Leaf(null, array($leaf1), array('prefix' => 'Leaf2_'));
        $leaf3 = new Types\Leaf(null, array($leaf2), array('prefix' => 'Leaf3_'));
        $leaf4 = new Types\Leaf(null, array(), array('prefix' => 'Leaf4_'));
        $leaf5 = new Types\Leaf(null, array(), array('prefix' => 'Leaf5_'));
        $leaf6 = new Types\Leaf(null, array($leaf4, $leaf5), array('prefix' => 'Leaf6_'));
        $leaf7 = new Types\Leaf(null, array($leaf6), array('prefix' => 'Leaf7_'));
        $leaf8 = new Types\Leaf(null, array($leaf3, $leaf7), array('prefix' => 'Leaf8_'));
        
        $array = Types\Leaf::getAsArray($leaf8);
        
        $this->assertEquals(0, strpos(array_keys($array['leaves'])[0], 'Leaf3'));
        $this->assertEquals(0, strpos(array_keys($array['leaves'][array_keys($array['leaves'])[0]]['leaves'])[0], 'Leaf2'));
        $this->assertEquals(0, strpos(array_keys($array['leaves'])[1], 'Leaf7'));
        $this->assertEquals(0, strpos(array_keys($array['leaves'][array_keys($array['leaves'])[1]]['leaves'])[0], 'Leaf6'));
        
    }
    
    public function testBuildFromArray() {
        $leaf1 = new Types\Leaf(null, array(), array('prefix' => 'Leaf1_'));
        $leaf2 = new Types\Leaf(null, array($leaf1), array('prefix' => 'Leaf2_'));
        $leaf3 = new Types\Leaf(null, array($leaf2), array('prefix' => 'Leaf3_'));
        $leaf4 = new Types\Leaf(null, array(), array('prefix' => 'Leaf4_'));
        $leaf5 = new Types\Leaf(null, array(), array('prefix' => 'Leaf5_'));
        $leaf6 = new Types\Leaf(null, array($leaf4, $leaf5), array('prefix' => 'Leaf6_'));
        $leaf7 = new Types\Leaf(null, array($leaf6), array('prefix' => 'Leaf7_'));
        $leaf8 = new Types\Leaf(null, array($leaf3, $leaf7), array('prefix' => 'Leaf8_'));
        
        $array = Types\Leaf::getAsArray($leaf8);

        $leaf8 = Types\Leaf::buildFromArray($array);
        
        $paths = Types\Leaf::getLeafIdentityPaths($leaf8);
        
        $this->assertEquals(3, count($paths));
        
    }
    
    public function testDataBelongsTo() {
        $leaf1 = new Types\Leaf(1, array(), array('prefix' => 'Leaf1_'));
        $leaf2 = new Types\Leaf(2, array($leaf1), array('prefix' => 'Leaf2_'));
        $leaf3 = new Types\Leaf(3, array($leaf2), array('prefix' => 'Leaf3_'));
        $leaf4 = new Types\Leaf(4, array(), array('prefix' => 'Leaf4_'));
        $leaf5 = new Types\Leaf(5, array(), array('prefix' => 'Leaf5_'));
        $leaf6 = new Types\Leaf(6, array($leaf4, $leaf5), array('prefix' => 'Leaf6_'));
        $leaf7 = new Types\Leaf(7, array($leaf6), array('prefix' => 'Leaf7_'));
        $leaf8 = new Types\Leaf(8, array($leaf3, $leaf7), array('prefix' => 'Leaf8_'));
        
        $path = Types\Leaf::dataBelongsTo(2, $leaf8);
        $this->assertRegExp("/Leaf8.*Leaf3.*Leaf2/", $path);
        
        $path = Types\Leaf::dataBelongsTo(1, $leaf8);
        $this->assertRegExp("/Leaf8.*Leaf3.*Leaf2.*Leaf1/", $path);
        
        $path = Types\Leaf::dataBelongsTo(5, $leaf8);
        $this->assertRegExp("/Leaf8.*Leaf7.*Leaf6.*Leaf5/", $path);
    }
    
    public function testDebugInfo() {
        $leaf1 = new Types\Leaf(1, array(), array('prefix' => 'Leaf1_'));
        $leaf2 = new Types\Leaf(2, array($leaf1), array('prefix' => 'Leaf2_'));
        $leaf3 = new Types\Leaf(3, array($leaf2), array('prefix' => 'Leaf3_'));
        $leaf4 = new Types\Leaf(4, array(), array('prefix' => 'Leaf4_'));
        $leaf5 = new Types\Leaf(5, array(), array('prefix' => 'Leaf5_'));
        $leaf6 = new Types\Leaf(6, array($leaf4, $leaf5), array('prefix' => 'Leaf6_'));
        $leaf7 = new Types\Leaf(7, array($leaf6), array('prefix' => 'Leaf7_'));
        $leaf8 = new Types\Leaf(8, array($leaf3, $leaf7), array('prefix' => 'Leaf8_'));
        
        ob_start();
        
        var_dump($leaf8);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?options\"?\]?.*=\\>\n.*array\\(2\\)/", $output);
        $this->assertRegExp("/\\[?\"?identifier\"?\]?.*=\\>\n.*string/", $output);
        $this->assertRegExp("/\\[?\"?leaves\"?\]?.*=\\>\n.*array\\(2\\)/", $output);
    }
    
}
