<?php
require_once('src/Phabstractic/Data/Components/Path.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Data\Components;
use Phabstractic\Features\Resource as FeaturesResource;


class PathTest extends TestCase
{
    public function testEmptyInstantiation() {
        $path = new Components\Path();
        
        $this->assertInstanceOf(Components\Path::class, $path);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $path);
    }
    
    public function testBasicInstantiation() {
        $path = new Components\Path(__DIR__, array('php'));
        
        $this->assertInstanceOf(Components\Path::class, $path);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $path);
        
        $this->assertEquals(__DIR__, $path->getPath());
        
    }
    
    public function testRelativeInstantiation() {
        $path = new Components\Path('relative/path');
        
        $this->assertInstanceOf(Components\Path::class, $path);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $path);
        
        $this->assertTrue($path->isRelative());
        
    }
    
    public function testIdentityPrefixInstantiation() {
        $path = new Components\Path('relative/path', array(), array('identity_prefix' => 'Test_'));
        
        $this->assertInstanceOf(Components\Path::class, $path);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $path);
        
        $this->assertEquals(0, strpos($path->getIdentifier(), 'Test_'));
        
    }
    
    public function testCheckConstruction() {
        $path = new Components\Path('relative/path', array(), array('check' => true));
    }
    
    public function testAddExtension() {
        $path = new Components\Path('relative/path');
        
        $path->addExtension('php');
        $path->addExtension(array('xml', 'txt'));
        
        $this->assertEquals(array('php', 'xml', 'txt'), $path->getExtensions());
        
    }
    
    /**
     * @depends testAddExtension
     * 
     */
    public function testRemoveExtension() {
        $path = new Components\Path('relative/path');
        
        $path->addExtension('php');
        $path->addExtension(array('xml', 'txt'));
        
        $this->assertEquals(array('php', 'xml', 'txt'), $path->getExtensions());
        
        $path->removeExtension('xml');
        
        $this->assertEquals(array('php', 'txt'), $path->getExtensions());
        
    }
    
    /**
     * @depends testAddExtension
     * 
     */
    public function testIsExtension() {
        $path = new Components\Path('relative/path', array('php', 'xml'));
        
        $this->assertTrue($path->isExtension('php'));
        $this->assertFalse($path->isExtension('txt'));
        
    }
    
    public function testSetPathWithCheck() {
        $path = new Components\Path('relative/path', array(), array('check' => true));
        
        $path->setPath('another/relative/path');
        
        $this->assertEquals('another/relative/path', $path->getPath());
        
        $pos = strpos(__DIR__, 'tests/');
        $base = substr(__DIR__, 0, $pos);
        
        $path->setPath($base . 'tests/PathTestDirectory/');
        
        $this->assertEquals($base . 'tests/PathTestDirectory/', $path->getPath());
    }
    
    /**
     * @expectedException Phabstractic\Data\Components\Exception\DomainException
     * 
     */
    public function testImproperSetPathWithCheck() {
        $pos = strpos(__DIR__, 'tests/');
        $base = substr(__DIR__, 0, $pos);
        
        $path = new Components\Path($base . 'tests/NonExistDirectory/', array(), array('check' => true));
        
    }
    
    public function testIsFilename() {
        $pos = strpos(__DIR__, 'tests/');
        $base = substr(__DIR__, 0, $pos);
        
        $path = new Components\Path($base . 'tests/PathTestDirectory/', array('php', 'xml'), array('suppress_warnings' => true));
        $this->assertEquals($base . 'tests/PathTestDirectory/TestFile1.php', $path->isFilename('TestFile1'));
        $this->assertEquals($base . 'tests/PathTestDirectory/TestFile2.xml', $path->isFilename('TestFile2'));
        $this->assertEquals(null, $path->isFilename('TestFile3'));
        
        // relative
        $path = new Components\Path('tests/PathTestDirectory/', array('php', 'xml'), array('suppress_warnings' => true));
        $this->assertEquals($base . 'tests/PathTestDirectory/TestFile1.php', $path->isFilename('TestFile1', $base));
        $this->assertEquals($base . 'tests/PathTestDirectory/TestFile2.xml', $path->isFilename('TestFile2', $base));
        $this->assertEquals($base . 'tests/PathTestDirectory/TestFile3.ini', $path->isFilename('TestFile3', $base, 'ini'));
    }
    
    public function testDebugInfo() {
        $pos = strpos(__DIR__, 'tests/');
        $base = substr(__DIR__, 0, $pos);
        
        $path = new Components\Path($base . 'tests/PathTestDirectory/', array('php', 'xml'), array('suppress_warnings' => true));
        
        ob_start();
        
        var_dump($path);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?strict\"?\]?.*=\\>/", $output);
        $this->assertRegExp("/\\[?\"?check\"?\]?.*=\\>\n.*bool\\(false\\)/", $output);
        $this->assertRegExp("/\\[?\"?suppress_warnings\"?\]?.*=\\>\n.*bool\\(true\\)/", $output);
        $this->assertRegExp("/\\[?\"?identity_prefix\"?\]?.*=\\>/", $output);
        $this->assertRegExp("/\\[?\"?path\"?\]?.*=\\>\n.*string/", $output);
        $this->assertRegExp("/\\[?\"?extensions\"?\]?.*=\\>\n.*array\\(2\\)/", $output);
        $this->assertRegExp("/\\[?\"?relative\"?\]?.*=\\>\n.*bool\\(false\\)/", $output);
        $this->assertRegExp("/\\[?\"?identityPrefix\"?\]?.*=\\>\n.*string/", $output);
    }
    
}
