<?php
require_once('src/Phabstractic/Event/Filter.php');
require_once('src/Phabstractic/Event/Resource/AbstractEvent.php');
require_once('src/Phabstractic/Event/Resource/EventInterface.php');
require_once('src/Phabstractic/Event/GenericEvent.php');
require_once('src/Phabstractic/Event/Resource/FilterInterface.php');
require_once('src/Phabstractic/Features/Resource/ConfigurationInterface.php');

use PHPUnit\Framework\TestCase;
use Phabstractic\Event;
use Phabstractic\Event\Resource as EventResource;
use Phabstractic\Features\Resource as FeaturesResource;

class FilterTest extends TestCase
{
    public function testBasicInstantiation() {
        $filter = new Event\Filter();
        
        $this->assertInstanceOf(Event\Filter::class, $filter);
        $this->assertInstanceOf(EventResource\AbstractEvent::class, $filter);
        $this->assertInstanceOf(EventResource\EventInterface::class, $filter);
        $this->assertInstanceOf(EventResource\FilterInterface::class, $filter);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $filter);
        
    }
    
    public function testInstantiationWithTags() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            null,
            array('tag1', 'tag2')
        );
        
        $this->assertInstanceOf(Event\Filter::class, $filter);
        $this->assertInstanceOf(EventResource\AbstractEvent::class, $filter);
        $this->assertInstanceOf(EventResource\EventInterface::class, $filter);
        $this->assertInstanceOf(EventResource\FilterInterface::class, $filter);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $filter);
        
        $this->assertEquals(array('tag1', 'tag2'), $filter->getTags());
    }
    
    public function testInstantiationWithCategories() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            null,
            array(),
            array('category1', 'category2')
        );
        
        $this->assertInstanceOf(Event\Filter::class, $filter);
        $this->assertInstanceOf(EventResource\AbstractEvent::class, $filter);
        $this->assertInstanceOf(EventResource\EventInterface::class, $filter);
        $this->assertInstanceOf(EventResource\FilterInterface::class, $filter);
        $this->assertInstanceOf(FeaturesResource\ConfigurationInterface::class, $filter);
        
        $this->assertEquals(array('category1', 'category2'), $filter->getCategories());
    }
    
    public function testGetState() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $state = $filter->getState();
        
        unset($state['fields']['identifier']);
        
        $this->assertEquals(array('tags' => array('tag1', 'tag2'),
                                  'categories' => array('category1', 'category2'),
                                  'fields' => array('target' => null,
                                                    'data' => 'testdata',
                                                    'class' => 'FilterTest',
                                                    'function' => 'testGetState',
                                                    'namespace' => '')), $state);
    }
    
    public function testSetStateWithArrayNoMorph() {
        $filter = new Event\Filter();
        
        $filter->build(
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
                                         'class' => 'FilterTestAgain',
                                         'function' => 'testSetStateWithArrayNoMorph',
                                         'namespace' => 'testnamespace'));
        
        $filter->setStateWithArray($state, false);
        
        $state = $filter->getState();
        unset($state['fields']['identifier']);
        
        $this->assertEquals(array('tags' => array('tag3', 'tag4'),
                       'categories' => array('category3', 'category4'),
                       'fields' => array('target' => null,
                                         'data' => 'testdataagain',
                                         'class' => 'FilterTestAgain',
                                         'function' => 'testSetStateWithArrayNoMorph',
                                         'namespace' => 'testnamespace')), $state);
    }
    
    public function testSetStateWithArrayWithMorph() {
        $filter = new Event\Filter();
        
        $filter->build(
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
        
        $filter->setState($state);
        
        $state = $filter->getState();
        unset($state['fields']['identifier']);
        
        $this->assertEquals(array('tags' => array('tag1', 'tag2', 'tag3', 'tag4'),
                       'categories' => array('category1', 'category2', 'category3', 'category4'),
                       'fields' => array('target' => null,
                                         'data' => 'testdata',
                                         'class' => 'FilterTest',
                                         'function' => 'testSetStateWithArrayWithMorph',
                                         'namespace' => 'testnamespace')), $state);
    }
    
    public function testSetStateWithArrayOptionalFormatNoMorph() {
        $filter = new Event\Filter();
        
        $filter->build(
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
        
        $filter->setStateWithArray($state, false);
        
        $state = $filter->getState();
        unset($state['fields']['identifier']);
        
        $this->assertEquals(array('tags' => array('tag3', 'tag4'),
                       'categories' => array('category3', 'category4'),
                       'fields' => array('target' => null,
                                         'data' => 'testdataagain',
                                         'class' => 'GenericEventTestAgain',
                                         'function' => 'testSetStateWithArrayOptionalFormatNoMorph',
                                         'namespace' => 'testnamespace')), $state);
    }
    
    public function testSetStateWithArrayOptionalFormatWithMorph() {
        $filter = new Event\Filter();
        
        $filter->build(
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
        
        $filter->setState($state);
        
        $state = $filter->getState();
        unset($state['fields']['identifier']);
        
        $this->assertEquals(array('tags' => array('tag1', 'tag2', 'tag3', 'tag4'),
                       'categories' => array('category1', 'category2', 'category3', 'category4'),
                       'fields' => array('target' => null,
                                         'data' => 'testdata',
                                         'class' => 'FilterTest',
                                         'function' => 'testSetStateWithArrayOptionalFormatWithMorph',
                                         'namespace' => 'testnamespace')), $state);
    }
    
    public function testSetStateWithFilterNoMorph() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $new = new Event\Filter();
        
        $new->build(
            null,
            __FUNCTION__ . 'new',
            __CLASS__ . 'new',
            __NAMESPACE__ . 'new',
            'newtestdata',
            array('tag3', 'tag4'),
            array('category3', 'category4')
        );
        
        $filter->setStateWithFilter($new, false);
        
        $state = $filter->getState();
        unset($state['fields']['identifier']);
        
        $this->assertEquals(array('tags' => array('tag3', 'tag4'),
                       'categories' => array('category3', 'category4'),
                       'fields' => array('target' => null,
                                         'data' => 'newtestdata',
                                         'class' => 'FilterTestnew',
                                         'function' => 'testSetStateWithFilterNoMorphnew',
                                         'namespace' => 'new')), $state);
    }
    
    public function testSetStateWithFilterWithMorph() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $new = new Event\Filter();
        
        $new->build(
            null,
            __FUNCTION__ . 'new',
            null,
            'testnamespace',
            null,
            array('tag3', 'tag4'),
            array('category3', 'category4')
        );
        
        $filter->setState($new);
        
        $state = $filter->getState();
        unset($state['fields']['identifier']);
        
        $this->assertEquals(array('tags' => array('tag1', 'tag2', 'tag3', 'tag4'),
                       'categories' => array('category1', 'category2', 'category3', 'category4'),
                       'fields' => array('target' => null,
                                         'data' => 'testdata',
                                         'class' => 'FilterTest',
                                         'function' => 'testSetStateWithFilterWithMorphnew',
                                         'namespace' => 'testnamespace')), $state);
    }
    
    public function testGetIdentifier() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertRegExp("/EventFilter/", $filter->getIdentifier());
    }
    
    public function testGetCategories() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertEquals(array('category1', 'category2'), $filter->getCategories());
    }
    
    public function testAddCategory() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $filter->addCategory('category3');
        
        $this->assertEquals(array('category1', 'category2', 'category3'), $filter->getCategories());
    }
    
    public function testSetCategories() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $filter->setCategories(array('category3', 'category4'));
        
        $this->assertEquals(array('category3', 'category4'), $filter->getCategories());
    }
    
    public function testRemoveCategory() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $filter->removeCategory('category1');
        
        $this->assertEquals(array('category2'), $filter->getCategories());
    }
    
    public function testIsCategory() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertTrue($filter->isCategory('category1'));
        $this->assertFalse($filter->isCategory('nonexistingcategory'));
    }
    
    public function testGetTags() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertEquals(array('tag1', 'tag2'), $filter->getTags());
    }
    
    public function testAddTag() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $filter->addTag('tag3');
        
        $this->assertEquals(array('tag1', 'tag2', 'tag3'), $filter->getTags());
    }
    
    public function testSetTags() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $filter->setTags(array('tag3', 'tag4'));
        
        $this->assertEquals(array('tag3', 'tag4'), $filter->getTags());
    }
    
    public function testRemoveTag() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $filter->removeTag('tag1');
        
        $this->assertEquals(array('tag2'), $filter->getTags());
    }
    
    public function testIsTag() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertTrue($filter->isTag('tag1'));
        $this->assertFalse($filter->isTag('nonexistingtag'));
    }
    
    public function testGetTarget() {
        $filter = new Event\Filter();
        
        $filter->build(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertEquals('testtarget', $filter->getTarget());
    }
    
    public function testGetTargetReference() {
        $filter = new Event\Filter();
        
        $filter->build(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $testref = &$filter->getTargetReference();
        
        $testref = 'modified';
        
        $this->assertEquals('modified', $filter->getTarget());
    }
    
    public function testSetTarget() {
        $filter = new Event\Filter();
        
        $filter->build(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $filter->setTarget('modified');
        
        $this->assertEquals('modified', $filter->getTarget());
    }
    
    public function testSetTargetReference() {
        $filter = new Event\Filter();
        
        $filter->build(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $testref = 'testtarget';
        $filter->setTargetReference($testref);
        $testref = 'modified';
        
        $this->assertEquals('modified', $filter->getTarget());
    }
    
    public function testGetData() {
        $filter = new Event\Filter();
        
        $filter->build(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertEquals('testdata', $filter->getData());
    }
    
    public function testGetDataReference() {
        $filter = new Event\Filter();
        
        $filter->build(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $testref = &$filter->getDataReference();
        
        $testref = 'modified';
        
        $this->assertEquals('modified', $filter->getData());
    }
    
    public function testSetData() {
        $filter = new Event\Filter();
        
        $filter->build(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $filter->setData('modified');
        
        $this->assertEquals('modified', $filter->getData());
    }
    
    public function testSetDataReference() {
        $filter = new Event\Filter();
        
        $filter->build(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $testref = 'testtarget';
        $filter->setDataReference($testref);
        $testref = 'modified';
        
        $this->assertEquals('modified', $filter->getData());
    }
    
    public function testGetFunction() {
        $filter = new Event\Filter();
        
        $filter->build(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertEquals(__FUNCTION__, $filter->getFunction());
    }
    
    public function testGetClass() {
        $filter = new Event\Filter();
        
        $filter->build(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertEquals(__CLASS__, $filter->getClass());
    }
    
    public function testGetNamespace() {
        $filter = new Event\Filter();
        
        $filter->build(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        $this->assertEquals(__NAMESPACE__, $filter->getNamespace());
    }
    
    public function testStrictlyApplicable() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            null,
            array('tag1', 'tag2')
        );
        
        $testevent1 = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            null,
            array('tag1')
        );
        
        $testevent2 = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            null,
            array('tag2')
        );
        
        $testevent3 = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            null,
            array('tag1', 'tag2')
        );
        
        $this->assertFalse($filter->isStrictlyApplicable($testevent1));
        $this->assertFalse($filter->isStrictlyApplicable($testevent2));
        $this->assertTrue($filter->isStrictlyApplicable($testevent3));
        
    }
    
    public function testLooselyApplicable() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            null,
            array('tag1', 'tag2')
        );
        
        $testevent1 = new Event\GenericEvent(
            null,
            null,
            null,
            null,
            null,
            array('tag1')
        );
        
        $testevent2 = new Event\GenericEvent(
            null,
            null,
            null,
            null,
            null,
            array('tag2')
        );
        
        $testevent3 = new Event\GenericEvent(
            null,
            null,
            null,
            null,
            null,
            array('tag1', 'tag3')
        );
        
        $testevent4 = new Event\GenericEvent(
            null,
            null,
            null,
            null,
            null,
            array('tag3', 'tag4')
        );
        
        $this->assertTrue($filter->isLooselyApplicable($testevent1));
        $this->assertTrue($filter->isLooselyApplicable($testevent2));
        $this->assertTrue($filter->isLooselyApplicable($testevent3));
        $this->assertFalse($filter->isLooselyApplicable($testevent4));
        
    }
    
    public function testIsEventApplicable() {
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            null,
            array('tag1', 'tag2')
        );
        
        $testevent1 = new Event\GenericEvent(
            null,
            null,
            null,
            null,
            null,
            array('tag1')
        );
        
        $testevent2 = new Event\GenericEvent(
            null,
            null,
            null,
            null,
            null,
            array('tag2')
        );
        
        $testevent3 = new Event\GenericEvent(
            null,
            null,
            null,
            null,
            null,
            array('tag1', 'tag3')
        );
        
        $testevent4 = new Event\GenericEvent(
            null,
            null,
            null,
            null,
            null,
            array('tag3', 'tag4')
        );
        
        $testevent5 = new Event\GenericEvent(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            null,
            array('tag1', 'tag2')
        );
        
        $filter->makeStrict();
        $this->assertFalse($filter->isEventApplicable($testevent1));
        $this->assertTrue($filter->isEventApplicable($testevent5));
        
        $filter->loosenUp();
        $this->assertTrue($filter->isLooselyApplicable($testevent1));
        $this->assertTrue($filter->isLooselyApplicable($testevent2));
        $this->assertTrue($filter->isLooselyApplicable($testevent3));
        $this->assertFalse($filter->isLooselyApplicable($testevent4));
        
    }
    
    public function testCustomFunction() {
        $closure = function ($event) { return ($event->getTarget() == 'testtarget') ? true : false; };
        
        $filter = new Event\Filter();
        
        $filter->build(
            null,
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            null,
            array('tag1', 'tag2')
        );
        
        $filter->enableFunction($closure);
        
        $testevent1 = new Event\GenericEvent(
            'testtarget',
            null,
            null,
            null,
            null
        );
        
        $testevent2 = new Event\GenericEvent(
            'othertarget',
            null,
            null,
            null,
            null
        );
        
        $this->assertTrue($filter->isEventApplicable($testevent1));
        $this->assertFalse($filter->isEventApplicable($testevent2));
        
        $testevent3 = new Event\GenericEvent(
            null,
            null,
            null,
            null,
            null,
            array('tag1', 'tag3')
        );
        
        $filter->disableFunction();
        
        $this->assertTrue($filter->isEventApplicable($testevent3));
    }
    
    public function testDebugInfo() {
        $filter = new Event\Filter();
        
        $filter->build(
            'testtarget',
            __FUNCTION__,
            __CLASS__,
            __NAMESPACE__,
            'testdata',
            array('tag1', 'tag2'),
            array('category1', 'category2')
        );
        
        ob_start();
        
        var_dump($filter);
        
        $output = ob_get_clean();
        
        $this->assertRegExp("/\\[?\"?class\"?\]?.*=\\>\n.*string.*\"?FilterTest\"?/", $output);
        $this->assertRegExp("/\\[?\"?tags\"?\]?.*=\\>\n.*array\\(2\\)/", $output);
    }
    
    
    
}
