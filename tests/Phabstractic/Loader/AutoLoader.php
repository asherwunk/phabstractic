<?php
require_once('src/Phabstractic/Loader/AutoLoader.php');
require_once('src/Phabstractic/Loader/Resource/LoaderInterface.php');
require_once('src/Phabstractic/Loader/Resource/AbstractLoader.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');
require_once('src/Phabstractic/Data/Components/Path.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Loader\Resource as LoaderResource;
use Phabstractic\Loader;
use Phabstractic\Features\Resource as FeaturesResource;
use Phabstractic\Data\Components;

class AutoLoaderTest extends TestCase
{
    
    public function testEmptyInstantiation()
    {
        $loader = new Loader\AutoLoader();
        
        $this->assertInstanceOf(Loader\AutoLoader::class, $loader);
        $this->assertInstanceOf(LoaderResource\AbstractLoader::class, $loader);
        $this->assertInstanceOf(LoaderResource\LoaderInterface::class, $loader);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $loader);
        
    }
    
    public function testInstantiationWithStringPaths()
    {
        $loader = new Loader\AutoLoader(array('path1', 'path2/path3'));
        
        $this->assertEquals(array('path1', 'path2/path3'), $loader->getPaths());
        
    }
    
    public function testInstantiationImproperNoStrict()
    {
        $loader = new Loader\AutoLoader(array('path1', 5));
        
        $this->assertEquals(array('path1'), $loader->getPaths());
        
    }
    
    /**
     * @expectedException Phabstractic\Loader\Exception\InvalidArgumentException
     * 
     */
    public function testInstantiationImproperWithStrict()
    {
        $loader = new Loader\AutoLoader(array('path1', 5), array('strict' => true));
        
    }
    
    public function testAddPath()
    {
        $loader = new Loader\AutoLoader();
        
        $loader->addPath('path1');
        
        $path = new Components\Path('../../path4/path5/../path6/path7/../');
        $loader->addPath($path);
        
        $path = new Components\Path('../../path4/path5/../../../');
        $loader->addPath($path);
        
        $this->assertEquals(array('path1', '../../path4/path6/', '../../../'), $loader->getPaths());
        
        $loader->addPath('path4/path5/path6');
        
        $this->assertEquals(array('path1', '../../path4/path6/', '../../../', 'path4/path5/path6'), $loader->getPaths());
        
    }
    
    /**
     * @depends testAddPath
     * 
     */
    public function testIsPath()
    {
        $loader = new Loader\AutoLoader();
        
        $loader->addPath('path1');
        
        $path = new Components\Path('../../path4/path5/../path6/path7/../');
        $loader->addPath($path);
        
        $path = new Components\Path('../../path4/path5/../../../');
        $loader->addPath($path);
        
        $loader->addPath('path4/path5/path6');
        
        $this->assertTrue($loader->isPath('../../../'));
        $this->assertTrue($loader->isPath($path));
        $this->assertTrue($loader->isPath('../../path4/path6/'));
        $this->assertFalse($loader->isPath('path7/path8/path9'));
        
        $path = new Components\Path('../../path4/path5/../../../path7/');
        
        $this->assertFalse($loader->isPath($path));
        
    }
    
    /**
     * @depends testAddPath
     * 
     */
    public function testRemovePath()
    {
        $loader = new Loader\AutoLoader();
        
        $loader->addPath('path1');
        
        $path = new Components\Path('../../path4/path5/../path6/path7/../');
        $loader->addPath($path);
        
        $path = new Components\Path('../../path4/path5/../../../');
        $loader->addPath($path);
        
        $loader->addPath('path4/path5/path6');
        
        $this->assertEquals(array('path1', '../../path4/path6/', '../../../', 'path4/path5/path6'), $loader->getPaths());
        
        $loader->removePath($path);
        
        $this->assertEquals(array('path1', '../../path4/path6/', 'path4/path5/path6'), $loader->getPaths());
        
        $loader->removePath('../../path4/path6/');
        
        $this->assertEquals(array('path1', 'path4/path5/path6'), $loader->getPaths());
        
    }
    
    /**
     * @depends testAddPath
     * 
     */
    public function testGetPathObject()
    {
        $loader = new Loader\AutoLoader();
        
        $loader->addPath('path1');
        
        $path = new Components\Path('../../path4/path5/../path6/path7/../');
        $loader->addPath($path);
        
        $path = new Components\Path('../../path4/path5/../../../', array('ini'));
        
        $loader->addPath($path);
        
        $loader->addPath('path4/path5/path6');
        
        $this->assertEquals(array('path1', '../../path4/path6/', '../../../', 'path4/path5/path6'), $loader->getPaths());
        
        $path = $loader->getPathObject('../../../');
        
        $this->assertEquals(array('ini', 'php'), $path->getExtensions());
        
    }
    
    /**
     * @depends testAddPath
     * 
     */
    public function testGetPathObjectReference()
    {
        $loader = new Loader\AutoLoader();
        
        $loader->addPath('path1');
        
        $path = new Components\Path('../../path4/path5/../path6/path7/../');
        $loader->addPath($path);
        
        $path = new Components\Path('../../path4/path5/../../../', array('ini'));
        
        $loader->addPath($path);
        
        $loader->addPath('path4/path5/path6');
        
        $this->assertEquals(array('path1', '../../path4/path6/', '../../../', 'path4/path5/path6'), $loader->getPaths());
        
        $path = &$loader->getPathObjectReference('../../../');
        
        $path->setPath('../path8/path9');
        
        $this->assertEquals(array('path1', '../../path4/path6/', '../path8/path9', 'path4/path5/path6'), $loader->getPaths());
        
    }
    
    public function testAddPrefix()
    {
        $loader = new Loader\AutoLoader();
        
        $loader->addPrefix('path4/path7/', 'WP_');
        
        $this->assertEquals(array('WP_'), $loader->getPrefixes('path4/path7/'));
        
        $loader->addPrefix('path4/path7/', 'Class_');
        
        $this->assertEquals(array('WP_', 'Class_'), $loader->getPrefixes('path4/path7/'));
        
    }
    
    /**
     * @depends testAddPrefix
     * 
     */
    public function testHasPrefix()
    {
        $loader = new Loader\AutoLoader();
        
        $loader->addPrefix('path4/path7/', 'WP_');
        $loader->addPrefix('path9/', 'WP_');
        $loader->addPrefix('../path10', 'Class_');
        
        $this->assertTrue($loader->hasPrefix('path4/path7/'));
        $this->assertTrue($loader->hasPrefix('path9/'));
        $this->assertTrue($loader->hasPrefix('../path10'));
        $this->assertFalse($loader->hasPrefix('unknownpath/'));
        
    }
    
    /**
     * @depends testAddPrefix
     * 
     */
    public function testIsPrefix()
    {
        $loader = new Loader\AutoLoader();
        
        $loader->addPrefix('path4/path7/', 'WP_');
        $loader->addPrefix('path9/', 'WP_');
        $loader->addPrefix('../path10', 'Class_');
        
        $this->assertEquals(array('path4/path7/', 'path9/'), $loader->isPrefix('WP_'));
    }
    
    /**
     * @depends testAddPrefix
     * 
     */
    public function testRemovePrefix()
    {
        $loader = new Loader\AutoLoader();
        
        $loader->addPrefix('path4/path7/', 'WP_');
        $loader->addPrefix('path4/path7/', 'Class_');
        $loader->addPrefix('path9/', 'WP_');
        
        $loader->removePrefix('path4/path7/', 'Class_');
        $loader->removePrefix('path9/', 'Class_');
        
        $this->assertEquals(array('WP_'), $loader->getPrefixes('path4/path7/'));
        $this->assertEquals(array('WP_'), $loader->getPrefixes('path9/'));
        
    }
    
    public function testAddNamespace()
    {
        $loader = new Loader\AutoLoader();
        
        $loader->addNamespace('Phabstractic\\Data\\Components', 'test/path');
        $loader->addNamespace('Phabstractic\\Data\\Components', 'test/anotherpath');
        
        $this->assertEquals(array('Phabstractic\\Data\\Components'), $loader->getNamespaces());
        
        $loader->addNamespace('Phabstractic\\Data\\Types\\Resource', 'test/resourcepath');
        
        $this->assertEquals(array('Phabstractic\\Data\\Components',
                                  'Phabstractic\\Data\\Types\\Resource'), $loader->getNamespaces());
        
        $loader->addNamespace('Phabstractic\\Data', 'intermediatepath');
        
        $this->assertEquals(array('Phabstractic\\Data\\Components',
                                  'Phabstractic\\Data\\Types\\Resource'), $loader->getNamespaces());
    }
    
    /**
     * @depends testAddNamespace
     * 
     */
    public function testIsNamespace()
    {
        $loader = new Loader\AutoLoader();
        
        $loader->addNamespace('Phabstractic\\Data\\Components', 'test/path');
        $loader->addNamespace('Phabstractic\\Data\\Types\\Resource', 'test/resourcepath');
        $loader->addNamespace('Phabstractic\\Data', 'intermediatepath');
        
        $this->assertTrue($loader->isNamespace('Phabstractic\\Data\\Components'));
        $this->assertTrue($loader->isNamespace('Phabstractic\\Data\\Types'));
        $this->assertFalse($loader->isNamespace('Some\\Strange\\Namespace'));
        
    }
    
    /**
     * @depends testAddNamespace
     * 
     */
    public function testRemoveNamespace()
    {
        $loader = new Loader\AutoLoader();
        
        $loader->addNamespace('Phabstractic\\Data\\Components', 'test/path');
        $loader->addNamespace('Phabstractic\\Data\\Types\\Resource', 'test/resourcepath');
        $loader->addNamespace('Phabstractic\\Data', 'intermediatepath');
        
        $loader->removeNamespace('Phabstractic\\Data\\Types');
        
        $this->assertTrue($loader->isNamespace('Phabstractic\\Data\\Components'));
        $this->assertFalse($loader->isNamespace('Phabstractic\\Data\\Types'));
        
        $loader->removeNamespace('Phabstractic\\Data');
        
        $this->assertTrue($loader->isNamespace('Phabstractic'));
        $this->assertFalse($loader->isNamespace('Phabstractic\\Data'));
        
        $loader->removeNamespace('Phabstractic');
        
        $this->assertFalse($loader->isNamespace('Phabstractic'));
        
    }
    
    /**
     * @depends testAddNamespace
     * 
     */
    public function testGetNamespacePath()
    {
        $loader = new Loader\AutoLoader();
        
        $loader->addNamespace('Phabstractic\\Data\\Components', 'test/path');
        $loader->addNamespace('Phabstractic\\Data\\Types\\Resource', 'test/resourcepath');
        $loader->addNamespace('Phabstractic\\Data', 'intermediatepath');
        
        $this->assertEquals('test/path', $loader->getNamespacePath('Phabstractic\\Data\\Components'));
        $this->assertEquals('test/resourcepath', $loader->getNamespacePath('Phabstractic\\Data\\Types\\Resource'));
        $this->assertEquals('intermediatepath', $loader->getNamespacePath('Phabstractic\\Data'));
        
    }
    
    /**
     * @depends testAddNamespace
     * 
     */
    public function testGetNamespaceModule()
    {
        $loader = new Loader\AutoLoader();
        
        $loader->addNamespace('Phabstractic\\Data\\Components', 'test/path');
        $loader->addNamespace('Phabstractic\\Data\\Types\\Resource', 'test/resourcepath');
        $loader->addNamespace('Phabstractic\\Data', 'intermediatepath');
        
        $module = &$loader->getNamespaceModule('\\Phabstractic\\Data');
        $module->getPath()->setPath('anotherpath');
        
        $this->assertEquals('test/path', $loader->getNamespacePath('Phabstractic\\Data\\Components'));
        $this->assertEquals('test/resourcepath', $loader->getNamespacePath('Phabstractic\\Data\\Types\\Resource'));
        $this->assertEquals('anotherpath', $loader->getNamespacePath('Phabstractic\\Data'));
        
    }
    
    /**
     * @depends testAddNamespace
     * 
     */
    public function testGetNamespaceModulesAsArray()
    {
        $loader = new Loader\AutoLoader();
        $path1 = new Components\Path('test/path');
        $path2 = new Components\Path('test/resourcepath');
        $path3 = new Components\Path('intermediatepath');
        
        $loader->addNamespace('Phabstractic\\Data\\Components', $path1);
        $loader->addNamespace('Phabstractic\\Data\\Types\\Resource', $path2);
        $loader->addNamespace('Phabstractic\\Data', $path3);
        
        $desired = array('path' => '/', 'modules' => array(
                            'Phabstractic' => array('path' => '', 'modules' => array(
                                'Data' => array('path' => $path3, 'modules' => array(
                                    'Components' => array('path' => $path1, 'modules' => array(),),
                                    'Types' => array('path' => '', 'modules' => array(
                                        'Resource' => array('path' => $path2, 'modules' => array(),)))))))));
        
        $this->assertEquals($desired, $loader->getNamespaceModulesAsArray());
        
    }
    
    public function testAddDelimiter()
    {
        $loader = new Loader\AutoLoader();
        
        $this->assertEquals(array('\\'), $loader->getDelimiters());
        
        $loader->addDelimiter('_');
        
        $this->assertEquals(array('\\', '_'), $loader->getDelimiters());
    }
    
    /**
     * @depends testAddDelimiter
     * 
     */
    public function testRemoveDelimiter()
    {
        $loader = new Loader\AutoLoader();
        
        $this->assertEquals(array('\\'), $loader->getDelimiters());
        
        $loader->addDelimiter('_');
        
        $this->assertEquals(array('\\', '_'), $loader->getDelimiters());
        
        $loader->removeDelimiter('\\');
        
        $this->assertEquals(array('_'), $loader->getDelimiters());
    }
    
    public function testDebugInfo() {
        $loader = new Loader\AutoLoader();
        
        ob_start();
        
        var_dump($loader);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?strict\"?\]?.*=\\>/", $output);
        $this->assertRegExp("/\\[?\"?auto_register\"?\]?.*=\\>\n.*bool/", $output);
        $this->assertRegExp("/\\[?\"?file_extension\"?\]?.*=\\>\n.*string/", $output);
        $this->assertRegExp("/\\[?\"?paths\"?\]?.*=\\>\n.*array\\(0\\)/", $output);
        $this->assertRegExp("/\\[?\"?prefixes\"?\]?.*=\\>\n.*array\\(0\\)/", $output);
        $this->assertRegExp("/\\[?\"?libraries\"?\]?.*=\\>/", $output);
        
    }
    
    public function testAutoload() {
        $loader = new Loader\AutoLoader();
        
        $loader->register();
        
        $loader->addPath('../tests/AutoLoadTestDirectory');
        $loader->addPrefix('../tests/AutoLoadTestDirectory', 'WP_');
        
        $class = new \UnknownNamespace\UnknownSubNamespace\AnotherUnknownSubNamespace\WP_UnknownClass();
        $class = new \UnknownNamespace\UnknownSubNamespace\AnotherUnknownSubNamespace\UnprefixedUnknownClass();
        
        $loader->removePath('../tests/AutoLoadTestDirectory');
        $loader->removePrefix('../tests/AutoLoadTestDirectory', 'WP_');
        
        $loader->addNamespace('UnknownNamespace', '../tests');
        $loader->addNamespace('UnknownNamespace\\UnknownSubNamespace', 'AutoLoadTestDirectory');
        $loader->addPrefix('../tests/AutoLoadTestDirectory', 'WP_');
        
        $class = new \UnknownNamespace\UnknownSubNamespace\WP_UnknownClass();
        $class = new \UnknownNamespace\UnknownSubNamespace\AnotherUnknownNamespace\UnprefixedUnknownClass();
        
        
        
    }
    
}
