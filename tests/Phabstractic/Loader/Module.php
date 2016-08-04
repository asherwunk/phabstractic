<?php
require_once('src/Phabstractic/Loader/Module.php');
require_once('src/Phabstractic/Loader/Resource/ModuleInterface.php');
require_once('src/Phabstractic/Data/Types/Leaf.php');
require_once('src/Phabstractic/Data/Types/Resource/AbstractLeaf.php');
require_once('src/Phabstractic/Data/Types/Resource/LeafInterface.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');
require_once('src/Phabstractic/Data/Components/Path.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Types;
use Phabstractic\Data\Types\Resource as TypesResource;
use Phabstractic\Features\Resource as FeaturesResource;
use Phabstractic\Loader;
use Phabstractic\Loader\Resource as LoaderResource;
use Phabstractic\Data\Components;

class ModuleTest extends TestCase
{
    
    public function testEmptyInstantiation()
    {
        $module = new Loader\Module();
        
        $this->assertInstanceOf(Loader\Module::class, $module);
        $this->assertInstanceOf(LoaderResource\ModuleInterface::class, $module);
        $this->assertInstanceOf(Types\Leaf::class, $module);
        $this->assertInstanceOf(TypesResource\AbstractLeaf::class, $module);
        $this->assertInstanceOf(TypesResource\LeafInterface::class, $module);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $module);
    }
    
    public function testBasicConstruction()
    {
        $module1 = new Loader\Module(null, 'Module1');
        $module2 = new Loader\Module(null, 'Module2');
        $module3 = new Loader\Module(null, 'Module3');
        
        $path = new Components\Path();
        
        $module = new Loader\Module($path, 'TestModule', array($module1, $module2, $module3));
        
        $this->assertInstanceOf(Loader\Module::class, $module);
        $this->assertInstanceOf(LoaderResource\ModuleInterface::class, $module);
        $this->assertInstanceOf(Types\Leaf::class, $module);
        $this->assertInstanceOf(TypesResource\AbstractLeaf::class, $module);
        $this->assertInstanceOf(TypesResource\LeafInterface::class, $module);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $module);
        
        $this->assertEquals('TestModule', $module->getModuleIdentifier());
        $this->assertEquals(array($module1, $module2, $module3), array_values($module->getModules()));
        
    }
    
    /**
     * @expectedException Phabstractic\Loader\Exception\InvalidArgumentException
     * 
     */
    public function testImproperConstructionWithStrict()
    {
        $module1 = new Loader\Module(null, 'Module1');
        $module2 = new Loader\Module(null, 'Module2');
        
        $path = new Components\Path();
        
        $module = new Loader\Module($path, 'TestModule', array($module1, $module2, 5), array('strict' => true));
        
    }
    
    public function testSetModuleIdentifier() {
        $module = new Loader\Module();
        
        $module->setModuleIdentifier('TestModule');
        
        $this->assertEquals('TestModule', $module->getModuleIdentifier());
        
    }
    
    public function testSetModulePath() {
        $path = new Components\Path();
        $module = new Loader\Module();
        
        $this->assertEquals(null, $module->getPath());
        
        $module->setPath($path);
        
        $this->assertEquals($path, $module->getPath());
        
    }
    
    public function testAddModuleToModule()
    {
        $module1 = new Loader\Module(null, 'Module1');
        $module2 = new Loader\Module(null, 'Module2');
        
        $module = new Loader\Module(null, 'MasterModule');
        $module->addModule($module1);
        $module->addModule($module2);
        
        $this->assertEquals(array($module1, $module2), $module->getModules());
        
    }
    
    /**
     * @depends testAddModuleToModule
     * 
     */
    public function testRemoveModuleFromModule()
    {
        $module1 = new Loader\Module(null, 'Module1');
        $module2 = new Loader\Module(null, 'Module2');
        
        $module = new Loader\Module(null, 'MasterModule');
        $module->addModule($module1);
        $module->addModule($module2);
        
        $module->removeModule($module1);
        
        $this->assertEquals(array($module2), $module->getModules());
        
    }
    
    /**
     * @depends testAddModuleToModule
     * 
     */
    public function testRemoveModuleByIdentifier()
    {
        $module1 = new Loader\Module(null, 'Module1');
        $module2 = new Loader\Module(null, 'Module2');
        
        $module = new Loader\Module(null, 'MasterModule');
        $module->addModule($module1);
        $module->addModule($module2);
        
        $module->removeModuleByIdentifier('Module1');
        
        $this->assertEquals(array($module2), $module->getModules());
        
    }
    
    /**
     * @depends testAddModuleToModule
     * 
     */
    public function testIsSubModule()
    {
        $module1 = new Loader\Module(null, 'Module1');
        $module2 = new Loader\Module(null, 'Module2');
        $module3 = new Loader\Module(null, 'Module3');
        
        $module = new Loader\Module(null, 'MasterModule');
        $module->addModule($module1);
        $module->addModule($module2);
        
        $this->assertTrue($module->isSubModule($module1));
        $this->assertFalse($module->isSubModule($module3));
        
    }
    
    /**
     * @depends testAddModuleToModule
     * 
     */
    public function testIsSubModuleByIdentifier()
    {
        $module1 = new Loader\Module(null, 'Module1');
        $module2 = new Loader\Module(null, 'Module2');
        $module3 = new Loader\Module(null, 'Module3');
        
        $module = new Loader\Module(null, 'MasterModule');
        $module->addModule($module1);
        $module->addModule($module2);
        
        $this->assertTrue($module->isSubModuleByIdentifier('Module1'));
        $this->assertFalse($module->isSubModuleByIdentifier('AnotherModule'));
        
    }
    
    public function testGetModuleIdentityPaths()
    {
        $module1 = new Loader\Module(null, 'Module1');
        $module2 = new Loader\Module(null, 'Module2', array($module1));
        $module3 = new Loader\Module(null, 'Module3', array($module2));
        $module4 = new Loader\Module(null, 'Module4');
        $module5 = new Loader\Module(null, 'Module5');
        $module6 = new Loader\Module(null, 'Module6', array($module4, $module5));
        $module7 = new Loader\Module(null, 'Module7', array($module3, $module6));
        
        $paths = $module7->getModuleIdentityPaths();
        
        $this->assertEquals(array('Module7\\Module3\\Module2\\Module1',
                                  'Module7\\Module6\\Module4',
                                  'Module7\\Module6\\Module5',), $paths);
                                  
        $paths = $module3->getModuleIdentityPaths();
        
        $this->assertEquals(array('Module3\\Module2\\Module1'), $paths);
        
    }
    
    /**
     * @depends testGetModuleIdentityPaths
     * 
     */
    public function testGetFromModuleIdentityPath()
    {
        $module1 = new Loader\Module(null, 'Module1');
        $module2 = new Loader\Module(null, 'Module2', array($module1));
        $module3 = new Loader\Module(null, 'Module3', array($module2));
        $module4 = new Loader\Module(null, 'Module4');
        $module5 = new Loader\Module(null, 'Module5');
        $module6 = new Loader\Module(null, 'Module6', array($module4, $module5));
        $module7 = new Loader\Module(null, 'Module7', array($module3, $module6));
        
        $paths = $module7->getModuleIdentityPaths();
        
        $this->assertEquals($module1, $module7->getFromModuleIdentityPath($paths[0]));
        
        $paths = $module6->getModuleIdentityPaths();
        
        $this->assertEquals($module4, $module6->getFromModuleIdentityPath($paths[0]));
        
    }
    
    /**
     * @depends testGetModuleIdentityPaths
     * 
     */
    public function testAddToModuleIdentityPath()
    {
        $module1 = new Loader\Module(null, 'Module1');
        $module2 = new Loader\Module(null, 'Module2', array($module1));
        $module3 = new Loader\Module(null, 'Module3', array($module2));
        $module4 = new Loader\Module(null, 'Module4');
        $module5 = new Loader\Module(null, 'Module5');
        $module6 = new Loader\Module(null, 'Module6', array($module4, $module5));
        $module7 = new Loader\Module(null, 'Module7', array($module3, $module6));
        
        $paths = $module7->getModuleIdentityPaths();
        
        $module8 = new Loader\Module(null, 'Module8');
        
        $module7->addToModuleIdentityPath($paths[0], $module8);
        
        $paths = $module7->getModuleIdentityPaths();
        
        $this->assertEquals(array('Module7\\Module3\\Module2\\Module1\\Module8',
                                  'Module7\\Module6\\Module4',
                                  'Module7\\Module6\\Module5',), $paths);
                                  
    }
    
    public function testGetModulesAsArray()
    {
        $module1 = new Loader\Module(null, 'Module1');
        $module2 = new Loader\Module(null, 'Module2', array($module1));
        $module3 = new Loader\Module(null, 'Module3', array($module2));
        $module4 = new Loader\Module(null, 'Module4');
        $module5 = new Loader\Module(null, 'Module5');
        $module6 = new Loader\Module(null, 'Module6', array($module4, $module5));
        $module7 = new Loader\Module(null, 'Module7', array($module3, $module6));
        
        $array = $module7->getModulesAsArray();
        
        $desiredResult = array('path' => null, 'modules' => array(
                                'Module3' => array('path' => null, 'modules' => array(
                                        'Module2' => array('path' => null, 'modules' => array(
                                            'Module1' => array('path' => null, 'modules' => array()))))),
                                'Module6' => array('path' => null, 'modules' => array(
                                    'Module4' => array('path' => null, 'modules' => array()),
                                    'Module5' => array('path' => null, 'modules' => array())))));
        
        $this->assertEquals($desiredResult, $array);
        
    }
    
    public function testPathBelongsTo() {
        
        $path1 = new Components\Path();
        $path2 = new Components\Path();
        
        $module1 = new Loader\Module(null, 'Module1');
        $module2 = new Loader\Module($path1, 'Module2', array($module1));
        $module3 = new Loader\Module(null, 'Module3', array($module2));
        $module4 = new Loader\Module(null, 'Module4');
        $module5 = new Loader\Module($path2, 'Module5');
        $module6 = new Loader\Module(null, 'Module6', array($module4, $module5));
        $module7 = new Loader\Module(null, 'Module7', array($module3, $module6));
        
        $this->assertEquals('Module7\\Module3\\Module2\\', $module7->pathBelongsTo($path1));
        $this->assertEquals('Module7\\Module6\\Module5\\', $module7->pathBelongsTo($path2));
        
    }
    
}
