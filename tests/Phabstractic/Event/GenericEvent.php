<?php
require_once('src/Phabstractic/Event/GenericEvent.php');
require_once('src/Phabstractic/Event/Resource/AbstractEvent.php');
require_once('src/Phabstractic/Event/Resource/EventInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Event;
use Phabstractic\Event\Resource as EventResource;

class GenericEventTest extends TestCase
{
    public function testBasicInstantiation() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__
        );
        
        $this->assertInstanceOf(Event\GenericEvent::class, $event);
        $this->assertInstanceOf(EventResource\AbstractEvent::class, $event);
        $this->assertInstanceOf(EventResource\EventInterface::class, $event);
        
    }
    
    public function testInstantiationWithTags() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            null,
            array('tag1', 'tag2')
        );
        
        $this->assertInstanceOf(Event\GenericEvent::class, $event);
        $this->assertInstanceOf(EventResource\AbstractEvent::class, $event);
        $this->assertInstanceOf(EventResource\EventInterface::class, $event);
        
        $this->assertEquals(array('tag1', 'tag2'), $event->getTags());
    }
    
    public function testInstantiationWithCategories() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            null,
            array(),
            array('category1', 'category2')
        );
        
        $this->assertInstanceOf(Event\GenericEvent::class, $event);
        $this->assertInstanceOf(EventResource\AbstractEvent::class, $event);
        $this->assertInstanceOf(EventResource\EventInterface::class, $event);
        
        $this->assertEquals(array('category1', 'category2'), $event->getCategories());
    }
    
    public function testGetState() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $state = $event->getState();
        
        unset($state['fields']['identifier']);
        
        $this->assertEquals(array('tags' => array('tag1', 'tag2'),
                                  'categories' => array('category1', 'category2'),
                                  'fields' => array('target' => null,
                                                    'data' => 'testdata',
                                                    'class' => 'GenericEventTest',
                                                    'function' => 'testGetState',
                                                    'namespace' => '',
                                                    'stop' => false,
                                                    'force' => false)), $state);
    }
    
    public function testSetStateWithArrayNoMorph() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $state = array('tags' => array('tag3', 'tag4'),
                       'categories' => array('category3', 'category4'),
                       'fields' => array('target' => null,
                                         'data' => 'testdataagain',
                                         'class' => 'GenericEventTestAgain',
                                         'function' => 'testSetStateWithArrayNoMorph',
                                         'namespace' => 'testnamespace',
                                         'stop' => false,
                                         'force' => false));
        
        $event->setStateWithArray($state, false);
        
        $state = $event->getState();
        unset($state['fields']['identifier']);
        
        $this->assertEquals(array('tags' => array('tag3', 'tag4'),
                       'categories' => array('category3', 'category4'),
                       'fields' => array('target' => null,
                                         'data' => 'testdataagain',
                                         'class' => 'GenericEventTestAgain',
                                         'function' => 'testSetStateWithArrayNoMorph',
                                         'namespace' => 'testnamespace',
                                         'stop' => false,
                                         'force' => false)), $state);
    }
    
    public function testSetStateWithArrayWithMorph() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $state = array('tags' => array('tag3', 'tag4'),
                       'categories' => array('category3', 'category4'),
                       'fields' => array('target' => null,
                                         'namespace' => 'testnamespace',
                                         'stop' => false,
                                         'force' => false));
        
        $event->setState($state);
        
        $state = $event->getState();
        unset($state['fields']['identifier']);
        
        $this->assertEquals(array('tags' => array('tag1', 'tag2', 'tag3', 'tag4'),
                       'categories' => array('category1', 'category2', 'category3', 'category4'),
                       'fields' => array('target' => null,
                                         'data' => 'testdata',
                                         'class' => 'GenericEventTest',
                                         'function' => 'testSetStateWithArrayWithMorph',
                                         'namespace' => 'testnamespace',
                                         'stop' => false,
                                         'force' => false)), $state);
    }
    
    public function testSetStateWithArrayOptionalFormatNoMorph() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $state = array('tags' => array('tag3', 'tag4'),
                       'categories' => array('category3', 'category4'),
                       'target' => null,
                       'data' => 'testdataagain',
                       'class' => 'GenericEventTestAgain',
                       'function' => 'testSetStateWithArrayOptionalFormatNoMorph',
                       'namespace' => 'testnamespace',
                       'stop' => false,
                       'force' => false);
        
        $event->setStateWithArray($state, false);
        
        $state = $event->getState();
        unset($state['fields']['identifier']);
        
        $this->assertEquals(array('tags' => array('tag3', 'tag4'),
                       'categories' => array('category3', 'category4'),
                       'fields' => array('target' => null,
                                         'data' => 'testdataagain',
                                         'class' => 'GenericEventTestAgain',
                                         'function' => 'testSetStateWithArrayOptionalFormatNoMorph',
                                         'namespace' => 'testnamespace',
                                         'stop' => false,
                                         'force' => false)), $state);
    }
    
    public function testSetStateWithArrayOptionalFormatWithMorph() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $state = array('tags' => array('tag3', 'tag4'),
                       'categories' => array('category3', 'category4'),
                       'target' => null,
                       'namespace' => 'testnamespace',
                       'stop' => false,
                       'force' => false);
        
        $event->setState($state);
        
        $state = $event->getState();
        unset($state['fields']['identifier']);
        
        $this->assertEquals(array('tags' => array('tag1', 'tag2', 'tag3', 'tag4'),
                       'categories' => array('category1', 'category2', 'category3', 'category4'),
                       'fields' => array('target' => null,
                                         'data' => 'testdata',
                                         'class' => 'GenericEventTest',
                                         'function' => 'testSetStateWithArrayOptionalFormatWithMorph',
                                         'namespace' => 'testnamespace',
                                         'stop' => false,
                                         'force' => false)), $state);
    }
    
    public function testSetStateWithEventNoMorph() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $new = new Event\GenericEvent(
            null,
            __FUNCTION__ . 'new',
            __CLASS__ . 'new',
            __NAMESPACE__ . 'new',
            'newtestdata',
            array('tag3', 'tag4'),
            array('category3', 'category4')
        );
        
        $event->setStateWithEvent($new, false);
        
        $state = $event->getState();
        unset($state['fields']['identifier']);
        
        $this->assertEquals(array('tags' => array('tag3', 'tag4'),
                       'categories' => array('category3', 'category4'),
                       'fields' => array('target' => null,
                                         'data' => 'newtestdata',
                                         'class' => 'GenericEventTestnew',
                                         'function' => 'testSetStateWithEventNoMorphnew',
                                         'namespace' => 'new',
                                         'stop' => false,
                                         'force' => false)), $state);
    }
    
    public function testSetStateWithEventWithMorph() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $new = new Event\GenericEvent(
            null,
            __FUNCTION__ . 'new',
            null,
            'testnamespace',
            null,
            array('tag3', 'tag4'),
            array('category3', 'category4')
        );
        
        $event->setState($new);
        
        $state = $event->getState();
        unset($state['fields']['identifier']);
        
        $this->assertEquals(array('tags' => array('tag1', 'tag2', 'tag3', 'tag4'),
                       'categories' => array('category1', 'category2', 'category3', 'category4'),
                       'fields' => array('target' => null,
                                         'data' => 'testdata',
                                         'class' => 'GenericEventTest',
                                         'function' => 'testSetStateWithEventWithMorphnew',
                                         'namespace' => 'testnamespace',
                                         'stop' => false,
                                         'force' => false)), $state);
    }
    
    public function testGetIdentifier() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertRegExp("/GenericEvent/", $event->getIdentifier());
    }
    
    public function testGetCategories() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertEquals(array('category1', 'category2'), $event->getCategories());
    }
    
    public function testAddCategory() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $event->addCategory('category3');
        
        $this->assertEquals(array('category1', 'category2', 'category3'), $event->getCategories());
    }
    
    public function testSetCategories() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $event->setCategories(array('category3', 'category4'));
        
        $this->assertEquals(array('category3', 'category4'), $event->getCategories());
    }
    
    public function testRemoveCategory() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $event->removeCategory('category1');
        
        $this->assertEquals(array('category2'), $event->getCategories());
    }
    
    public function testIsCategory() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertTrue($event->isCategory('category1'));
        $this->assertFalse($event->isCategory('nonexistingcategory'));
    }
    
    public function testGetTags() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertEquals(array('tag1', 'tag2'), $event->getTags());
    }
    
    public function testAddTag() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $event->addTag('tag3');
        
        $this->assertEquals(array('tag1', 'tag2', 'tag3'), $event->getTags());
    }
    
    public function testSetTags() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $event->setTags(array('tag3', 'tag4'));
        
        $this->assertEquals(array('tag3', 'tag4'), $event->getTags());
    }
    
    public function testRemoveTag() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $event->removeTag('tag1');
        
        $this->assertEquals(array('tag2'), $event->getTags());
    }
    
    public function testIsTag() {
        $event = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertTrue($event->isTag('tag1'));
        $this->assertFalse($event->isTag('nonexistingtag'));
    }
    
    public function testGetTarget() {
        $event = new Event\GenericEvent(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertEquals('testtarget', $event->getTarget());
    }
    
    public function testGetTargetReference() {
        $event = new Event\GenericEvent(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $testref = &$event->getTargetReference();
        
        $testref = 'modified';
        
        $this->assertEquals('modified', $event->getTarget());
    }
    
    public function testSetTarget() {
        $event = new Event\GenericEvent(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $event->setTarget('modified');
        
        $this->assertEquals('modified', $event->getTarget());
    }
    
    public function testSetTargetReference() {
        $event = new Event\GenericEvent(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $testref = 'testtarget';
        $event->setTargetReference($testref);
        $testref = 'modified';
        
        $this->assertEquals('modified', $event->getTarget());
    }
    
    public function testGetData() {
        $event = new Event\GenericEvent(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertEquals('testdata', $event->getData());
    }
    
    public function testGetDataReference() {
        $event = new Event\GenericEvent(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $testref = &$event->getDataReference();
        
        $testref = 'modified';
        
        $this->assertEquals('modified', $event->getData());
    }
    
    public function testSetData() {
        $event = new Event\GenericEvent(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $event->setData('modified');
        
        $this->assertEquals('modified', $event->getData());
    }
    
    public function testSetDataReference() {
        $event = new Event\GenericEvent(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $testref = 'testtarget';
        $event->setDataReference($testref);
        $testref = 'modified';
        
        $this->assertEquals('modified', $event->getData());
    }
    
    public function testGetFunction() {
        $event = new Event\GenericEvent(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertEquals(__FUNCTION__, $event->getFunction());
    }
    
    public function testGetClass() {
        $event = new Event\GenericEvent(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertEquals(__CLASS__, $event->getClass());
    }
    
    public function testGetNamespace() {
        $event = new Event\GenericEvent(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertEquals(__NAMESPACE__, $event->getNamespace());
    }
    
    function testStop() {
        $event = new Event\GenericEvent(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertFalse($event->isStopped());
        $event->stop();
        $this->assertTrue($event->isStopped());
        $event->proceed();
        $this->assertFalse($event->isStopped());
        $event->force();
        $event->stop();
        $this->assertFalse($event->isStopped());
        
    }
    
    function testForce() {
        $event = new Event\GenericEvent(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertFalse($event->isUnstoppable());
        $event->force();
        $this->assertTrue($event->isUnstoppable());
        $event->subdue();
        $this->assertFalse($event->isUnstoppable());
        $event->force();
        $event->stop();
        $this->assertFalse($event->isStopped());
        
    }
    
    public function testDebugInfo() {
        $event = new Event\GenericEvent(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        ob_start();
        
        var_dump($event);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?class\"?\]?.*=\\>\n.*string.*\"?GenericEventTest\"?/", $output);
        $this->assertRegExp("/\\[?\"?tags\"?\]?.*=\\>\n.*array\\(2\\)/", $output);
    }
    
    
    
}
