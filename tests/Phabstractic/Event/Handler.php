<?php
require_once('src/Phabstractic/Event/Handler.php');
require_once('src/Phabstractic/Patterns/PublisherTrait.php');
require_once('src/Phabstractic/Patterns/Resource/PublisherInterface.php');
require_once('src/Phabstractic/Patterns/Resource/ObserverInterface.php');
require_once('src/Phabstractic/Event/GenericEvent.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Patterns;
use Phabstractic\Patterns\Resource as PatternsResource;
use Phabstractic\Event;

class TestHandlerPublisherClass implements PatternsResource\PublisherInterface {
    use Patterns\PublisherTrait;
    
}

class TestHandlerTestClass {
    public static function staticMethod() {
        return true;
    }
    
    public function testMethod() {
        return true;
    }
    
}

function testHandlerTestFunction() {
    return true;
}

class HandlerTest extends TestCase
{
    public function testEmptyInstantiation() {
        $handler = new Event\Handler();
        
        $this->assertInstanceOf(Event\Handler::class, $handler);
        $this->assertInstanceOf(PatternsResource\ObserverInterface::class, $handler);
    }
    
    public function testClosureInstantiation() {
        $handler = new Event\Handler(
            null,
            function () {return true;}
        );
        
        $publisher = new TestHandlerPublisherClass();
        $state = new Event\GenericEvent(null,
                                        __FUNCTION__,
                                        __CLASS__,
                                        __NAMESPACE__);
        
        $this->assertTrue($handler->notifyObserver($publisher, $state));
    }
    
    public function testStaticMethodInstantiation() {
        $handler = new Event\Handler(
            'TestHandlerTestClass',
            'staticMethod'
        );
        
        $publisher = new TestHandlerPublisherClass();
        $state = new Event\GenericEvent(null,
                                        __FUNCTION__,
                                        __CLASS__,
                                        __NAMESPACE__);
        
        $this->assertTrue($handler->notifyObserver($publisher, $state));
    }
    
    public function testNormalMethodInstantiation() {
        $handler = new Event\Handler(
            new TestHandlerTestClass,
            'testMethod'
        );
        
        $publisher = new TestHandlerPublisherClass();
        $state = new Event\GenericEvent(null,
                                        __FUNCTION__,
                                        __CLASS__,
                                        __NAMESPACE__);
        
        $this->assertTrue($handler->notifyObserver($publisher, $state));
    }
    
    /**
     * @expectedException \Phabstractic\Event\Exception\InvalidArgumentException
     * 
     */
    public function testImproperStaticMethodInstantiation() {
        $handler = new Event\Handler(
            'TestHandlerTestClass',
            'staticMethodNonexist',
            ''
        );
        
    }
    
    /**
     * @expectedException \Phabstractic\Event\Exception\InvalidArgumentException
     * 
     */
    public function testImproperMethodInstantiation() {
        $handler = new Event\Handler(
            new TestHandlerTestClass(),
            'testMethodNonexist',
            ''
        );
        
    }
    
    public function testSetProperObject() {
        $handler = new Event\Handler();
        
        $handler->setFunction('testMethod');
        $handler->setNamespace('');
        $handler->setObject(new TestHandlerTestClass());
        
        $publisher = new TestHandlerPublisherClass();
        $state = new Event\GenericEvent(null,
                                        __FUNCTION__,
                                        __CLASS__,
                                        __NAMESPACE__);
        
        $this->assertTrue($handler->notifyObserver($publisher, $state));
    }
    
    /**
     * @expectedException \Phabstractic\Event\Exception\InvalidArgumentException
     * 
     */
    public function testSetImproperObject() {
        $handler = new Event\Handler();
        
        $handler->setFunction('testMethodNonexist');
        $handler->setNamespace('');
        $handler->setObject(new TestHandlerTestClass());
        
    }
    
    public function testSetProperFunction() {
        $handler = new Event\Handler();
        
        $handler->setNamespace('');
        $handler->setObject('TestHandlerTestClass');
        $handler->setFunction('staticMethod');
        
        $publisher = new TestHandlerPublisherClass();
        $state = new Event\GenericEvent(null,
                                        __FUNCTION__,
                                        __CLASS__,
                                        __NAMESPACE__);
        
        $this->assertTrue($handler->notifyObserver($publisher, $state));
    }
    
    /**
     * @expectedException \Phabstractic\Event\Exception\InvalidArgumentException
     * 
     */
    public function testSetImproperFunction() {
        $handler = new Event\Handler();
        
        $handler->setNamespace('');
        $handler->setObject('TestHandlerTestClass');
        $handler->setFunction('staticMethodNonexist');
        
    }
    
    public function testSetProperNamespace() {
        $handler = new Event\Handler();
        
        $handler->setObject('TestHandlerTestClass');
        $handler->setFunction('staticMethod');
        $handler->setNamespace('');
        
        $publisher = new TestHandlerPublisherClass();
        $state = new Event\GenericEvent(null,
                                        __FUNCTION__,
                                        __CLASS__,
                                        __NAMESPACE__);
        
        $this->assertTrue($handler->notifyObserver($publisher, $state));
    }
    
    /**
     * @expectedException \Phabstractic\Event\Exception\InvalidArgumentException
     * 
     */
    public function testSetImproperNamespace() {
        $handler = new Event\Handler();
        
        $handler->setObject('TestHandlerTestClass');
        $handler->setFunction('staticMethod');
        $handler->setNamespace('nonexist');
        
    }
    
    public function testGetDestination() {
        $test = new TestHandlerTestClass();
        
        $handler = new Event\Handler(
            $test,
            'testMethod'
        );
        
        $this->assertEquals(array($test, 'testMethod'), $handler->getDestination());
        
        $handler = new Event\Handler(
            'TestHandlerTestClass',
            'staticMethod'
        );
        
        $this->assertEquals('\\TestHandlerTestClass::staticMethod', $handler->getDestination());
        
        $closure = function () { return true; };
        
        $handler = new Event\Handler(
            null,
            $closure
        );
        
        $this->assertEquals($closure, $handler->getDestination());
        
    }
    
    public function testBuildFromArray() {
        $arr = array(new TestHandlerTestClass(), 'testMethod');
        
        $handler = Event\Handler::buildFromArray($arr);
        
        $publisher = new TestHandlerPublisherClass();
        $state = new Event\GenericEvent(null,
                                        __FUNCTION__,
                                        __CLASS__,
                                        __NAMESPACE__);
        
        $this->assertTrue($handler->notifyObserver($publisher, $state));
    }
    
    public function testBuildFromClosure() {
        $handler = Event\Handler::buildFromClosure(function(){return true;});
        
        $publisher = new TestHandlerPublisherClass();
        $state = new Event\GenericEvent(null,
                                        __FUNCTION__,
                                        __CLASS__,
                                        __NAMESPACE__);
        
        $this->assertTrue($handler->notifyObserver($publisher, $state));
    }
    
    public function testDebugInfo() {
        $handler = new Event\Handler(
            new TestHandlerTestClass(),
            'testMethod'
        );
        
        ob_start();
        
        var_dump($handler);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?function\"?\]?.*=\\>\n.*string/", $output);
    }
    
}
