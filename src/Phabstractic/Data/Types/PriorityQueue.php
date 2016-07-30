<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Priority Queue (stack priority)
 * 
 * This file contains the PriorityQueue class.  This class uses the
 * AbstractSortedList to sort the list, which in turn uses the
 * AbstractRestrictList to limit the list to objects that implement
 * PriorityInterface.
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Data
 * @subpackage Structures
 * 
 */

/**
 * Falcraft Libraries Data Types Namespace
 * 
 */
namespace Phabstractic\Data\Types
{
    
    require_once(realpath( __DIR__ . '/../../') . '/falcraftLoad.php');
    
    /* This class contains a static function for returning unique values
       for an array that is more object compatible. */
    $includes = array(// we are configurable
                      '/Features/ConfigurationTrait.php',
                      // we utilize array utilities in resource
                      '/Resource/ArrayUtilities.php',
                      // we inherit from sorted list
                      '/Data/Types/Resource/AbstractSortedList.php',
                      // we typecheck against filter in constructor
                      '/Data/Types/Resource/FilterInterface.php',
                      // instantiate default restrictions to priorityinterface
                      '/Data/Types/Restrictions.php',
                      '/Data/Types/Type.php',
                      // we can get priorities between a certain specified range
                      '/Data/Types/Range.php',
                      // use set intersect
                      '/Data/Types/Set.php',
                      // some list methods return None data type
                      '/Data/Types/None.php',
                      // we throw the following exceptions
                      '/Data/Types/Exception/InvalidArgumentException.php',
                      '/Data/Types/Exception/RangeException.php',
                      '/Data/Types/Exception/RuntimeException.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types\Resource as TypesResource;
    use Phabstractic\Data\Types\Exception as TypesException;
    use Phabstractic\Data\Types\Type;
    use Phabstractic\Resource as PhabstracticResource;
    use Phabstractic\Features;
    
    /**
     * Priority Queue Class - Implements a Priority Queue
     * 
     * This is a list that automatically sorts itself
     * according to Priority objects (data attached to priority numbers)
     * 
     * The lower the number, the higher urgency the item has in the list.
     * 
     * This object relies on AbstractSortedList and provides the sort
     * algorithm required
     * 
     * NOTE: Because it checks against a priority interface, it's possible
     *       to have different 'kinds' of priority objects in this list
     *       despite its restrictions.
     * 
     * CHANGELOG
     * 
     * 1.0: Created PriorityQueue - May 27th, 2013
     * 1.1: Added Falcraft\Data\Types\Range support - October 7th, 2013
     * 2.0: Refactored and re-formatted for inclusion
     *          in primus - April 11th, 2015
     * 3.0: reformatted for inclusion in phabstractic - July 24th, 2016
     * 
     * @link http://en.wikipedia.org/wiki/Priority_queue [English]
     * 
     * @version 3.0
     * 
     */
    class PriorityQueue extends TypesResource\AbstractSortedList
    {
        use Features\ConfigurationTrait;
        
        /**
         * Return Data Equal To Given Urgency Index?
         * 
         */
        const EQUAL = 0;
        
        /**
         * Return Data Equal or Higher To Given Urgency Index?
         * 
         */
        const HIGHER = 1;
        
        /**
         * Return Data Equal or Lower to Given Urgency Index?
         * 
         */
        const LOWER = -1;
        
        /**
         * Compare Two Priority Objects
         * 
         * This is used by AbstractSortedList to sort the elements of the list
         * 
         * @param Phabstractic\Data\Type\Resource\PriorityInterface $l
         *            The first value to compare
         * @param Phabstractic\Data\Type\Resource\PriorityInterface $r
         *            The second value to compare
         * 
         * @return int The required comparison results.
         * 
         * @throws TypesException\InvalidArgumentException
         *             if the values to compare are not of PriorityInterface
         * 
         */
        protected function cmp($l, $r)
        {
            // check against restrictions, v3
            if (!$this->restrictions->isAllowed(Type\getValueType($l)) ||
                    !$this->restrictions->isAllowed(Type\getValueType($r))) {
                throw new TypesException\InvalidArgumentException(
                    'Phabstractic\\Data\\Types\\PriorityQueue->cmp: ' .
                    'Comparison types not allowed');
            }
                    
            $lp = $l->getPriority();
            $rp = $r->getPriority();
            
            if ( $lp > $rp ) {
                return 1;
            } else if ( $lp < $rp ) {
                return -1;
            } else {
                return 0;
            }
            
        }
        
        /**
         * The Priority Queue constructor
         * 
         * Accepts data, and the obligatory options parameter
         * 
         * Passes the required restrictions onto the parent class along with
         * the options
         * 
         * This instantiates the class and sets the index
         * 
         * Options: strict - Do we raise appropriate exceptions when values
         *                   are misaligned?
         * 
         * @param mixed $data The data to initialize the queue
         * @param Phabstractic\Data\Types\Resource\FilterInterface $restrictions
         *            The type restrictions
         * @param mixed $options The options to pass into the object
         * 
         */
        public function __construct(
            $data = null,
            TypesResource\FilterInterface $restrictions = null,
            $options = array()
        ) {
            $this->configure($options);
            
            if (!$restrictions) {
               $this->restrictions = new Restrictions(
                    array(Type::TYPED_OBJECT),
                    array('Phabstractic\\Data\\Types\\Resource\\PriorityInterface'),
                    array('autoload' => true));
            } else {
                $this->restrictions = $restrictions;
            }
        
            parent::__construct($data, $this->restrictions, $options);
        }
        
        /**
         * Returns the top value, (that being the most urgent)
         * 
         * Does not pop the value off the list
         * 
         * @return mixed|Phabstractic\Data\Types\None Data from the priority object,
         *              null otherwise
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         * 
         */
        public function top()
        {
            if (!empty($this->list)) {
                $queue = $this->getList();
                return $queue[0];
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\PriorityQueue->top: ' .
                        'called on empty list');
                } else {
                    return new None();
                }
                
            }
            
        }
        
        /**
         * Returns the top value as a reference (that being the most urgent)
         * 
         * Does not pop the value off the list
         * 
         * @return mixed|Phabstractic\Data\Types\None Data from the priority object,
         *              null otherwise
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         * 
         */
        public function &topReference()
        {
            // apply the sorting function
            parent::topReference();
            
            if (!empty($this->list)) { 
                return $this->list[0]; 
            } else {
                if ($this->conf->strict) {
                   throw new Exception\RangeException(
                       'Phabstractic\\Data\\Types\\PriorityQueue: ' .
                       'Top called on empty list');
                } else {
                    return new None();
                }
                
            }
        }
        
        /**
         * Wrapper function for top()
         * 
         * @return mixed|Phabstractic\Data\Types\None  Data from priority object,
         *              null if otherwise
         * 
         */
        public function peek()
        {
            // can return Types\Null();
            return $this->top();
        }
        
        /**
         * Wrapper function for topReference()
         * 
         * @return mixed|Phabstractic\Data\Types\None Data from priority object as
         *              a reference, null if otherwise
         * 
         */
        public function &peekReference()
        {
            // Can return Types\Null();
            return $this->topReference();
        }
        
        /**
         * Returns the least urgent item from the list
         * 
         * @return mixed|Phabstractic\Data\Types\None The data from the priority
         *              object, null if otherwise
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         * 
         */
        public function bottom()
        {
            // sort the list (abstractsortedlist doesn't have this function)
            usort($this->list, array($this, 'cmp'));
            
            if (!empty($this->list)) {
                return $this->list[count($this->list)-1];
            } else {
                if ($this->conf->strict) {
                    throw new TypesException\RangeException(
                        'Phabstractic\\Data\\Types\\PriorityQueue->bottom: ' .
                        'called on empty list');
                } else {
                    return new None();
                }
                
            }
            
        }
        
        /**
         * Returns the least urgent item from the list as a reference
         * 
         * @return mixed|Phabstractic\Data\Types\None The data from the priority
         *                  object as a reference, null if otherwise
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         * 
         */
        public function &bottomReference()
        {
            // sort the list (abstractsortedlist doesn't have this function)
            usort($this->list, array($this, 'cmp'));
            
            if (!empty($this->list)) { 
                return $this->list[count($this->list)-1];
            } else {
                if ($this->conf->strict) {
                   throw new TypesException\RangeException(
                       'Phabstractic\\Data\\Types\\PriorityQueue->' .
                       'bottom called on empty queue.');
                } else {
                    $none = new None();
                    // must return reference, not literal
                    return $none;
                }
                
            }
        }
        
        /**
         * Returns the data objects specified by the order request as an array
         * 
         * Because this doesn't set references, the code had to be duplicated
         * 
         * @param int $i the given urgency level
         * @param string $order the given order, higher, lower, equal
         * 
         * @return array|Phabstractic\Data\Types\None The array of data items that
         *               are equal, higher, or lower than the given urgency
         *               index
         * 
         * @throws Phabstractic\Data\Types\Exception\RangeException
         *              if strict and index doesn't exist
         * 
         */
        public function index($i, $order = self::EQUAL)
        {
            if (empty($this->list)) {
                if ($this->conf->strict) {
                    throw new Exception\RangeException(
                        'Phabstractic\\Data\\Types\\PriorityQueue->index: ' .
                        'call on empty list');
                } else {
                    return array(new None());
                }
            } else {
                $return = array();
                foreach ($this->list as $item) {
                    switch ($order) {
                        /* this stores the results of boolean logic statements
                           allows us to reference the result in one place below */
                        case self::HIGHER:
                            $predicate = $item->getPriority() >= $i;
                            break;
                            
                        case self::LOWER:
                            $predicate = $item->getPriority() <= $i;
                            break;
                        
                        case self::EQUAL:
                            $predicate = $item->getPriority() == $i;
                            break;
                            
                        default:
                            $predicate = true;
                    }
                    
                    // Do we include the item?
                    if ( $predicate ) {
                        $return[] = $item->getData();
                    }
                }
                
                return $return;
            }
            
        }
        
        /**
         * Get A Range Of Indexes
         * 
         * This gets an array of objects that have priorities between ranges
         * 
         * NOTE: The utility function must be used because of incompatibility
         *       of array_unique algorithm
         * 
         * @param Phabstractic\Data\Types\Range $r The range to include
         * 
         * @return array The appropriate array
         * 
         */
        public function indexRange(Range $r)
        {
            return PhabstracticResource\ArrayUtilities::returnUnique(
                Set::intersection($this->index($r->getMinimum(), self::HIGHER),
                                  $this->index($r->getMaximum(), self::LOWER)));
        }
                
        /**
         * Returns the urgency index data items as an array of references
         * 
         * This turns out an array of the data objects that are higher,
         * lower, or equal to the urgency level given.
         * 
         * @param int $i the given urgency level
         * @param string $order the given order, higher, lower, equal
         * 
         * @return array|Phabstractic\Data\Types\None An array of references to the
         *              data objects
         * 
         * @throws Phabstractic\Types\Exception\RangeException
         *              if strict is set and no index
         * 
         */
        public function &indexReference($i, $order = self::EQUAL)
        {
            if (empty($this->list)) {
                if ($this->conf->strict) {
                    throw new Exception\RangeException(
                        'Phabstractic\\Data\\Types\\PriorityQueue->indexReference: ' .
                        'call on empty list');
                } else {
                    $arr = array(new None());
                    // return value, not literal
                    return $arr;
                }
            } else {
                $return = array();
                foreach ($this->list as $item) {
                    switch ($order) {
                        case self::HIGHER:
                            $predicate = $item->getPriority() >= $i;
                            break;
                            
                        case self::LOWER:
                            $predicate = $item->getPriority() <= $i;
                            break;
                        
                        case self::EQUAL:
                            $predicate = $item->getPriority() == $i;
                            break;
                            
                        default:
                            $predicate = true;
                    }
                    
                    // Do we include the item?
                    if ( $predicate ) {
                        $return[] = &$item->getDataReference();
                    }
                }
                
                return $return;
            }
        }
        
        /**
         * Get A Range Of Index References
         * 
         * This gets an array of objects that have priorities between ranges
         * 
         * NOTE: The utility function must be used because of incompatibility
         *       of array_unique algorithm
         * 
         * @param Phabstractic\Data\Types\Range $r The range to include
         * 
         * @return array The appropriate array
         * 
         */
        public function indexRangeReference( Types\Range $r )
        {
            return PhabstracticResource\ArrayUtilities::returnUniqueByReference(
                Set::intersection($this->indexReference($r->getMinimum(), self::HIGHER),
                                  $this->indexReference($r->getMaximum(), self::LOWER)));
        }
        
        /**
         * Deletes an element given by data from the list
         * 
         * This does not need sorting because the list will
         * already be sorted by urgency, eliminating one item
         * does not upset that sort
         * 
         * @param mixed $data The given data item to remove from the list
         * 
         * @return bool Whether the data item was deleted, returns false if
         *              data item not in queue
         * 
         * @throw Phabstractic\Data\TypesException\RangeException
         *              if set to strict and element doesn't exist
         * 
         */
        public function delete($data)
        {
            foreach ($this->list as $key => $priority) {
                /* we're going to use the element comparison of arrayutiltiies
                   as it's more hackable and comprehensive, in my opinion */
                if (PhabstracticResource\ArrayUtilities::elementComparison(
                            $priority->getData(),
                            $data
                        ) === 0) {
                    unset($this->list[$key]);
                    return true;
                }
                
            }
            
            return false;
        }
        
        /**
         * Delete all elements meeting a particular priority
         * 
         * Pretty straight forward
         * 
         * @param integer $priority Priority to delete
         * 
         * @return bool Successful?
         * 
         */
        public function deletePriority($priority)
        {
            $keys = array();
            foreach ($this->list as $key => $val) {
                if ($val->getPriority() == $priority) {
                    $keys[] = $key;
                }
                
            }
            
            if (empty($keys)) {
                return false;
            }
            
            foreach ($keys as $key) {
                unset($this->list[$key]);
            }
            
            return true;
        }
        
        /**
         * Pop the most urgent item off the list
         * 
         * This truncates the list from the top, and returns its value
         * 
         * NOTE: This returns the priority's DATA
         * 
         * @return mixed|Phabstractic\Data\Types\None The data associated with the
         *                    highest urgency, null otherwise
         * 
         */
        public function pop()
        {
            // top ends up sorting the list prior
            
            $datum = $this->top();
            if (!($datum instanceof None)) {
                $priority = array_shift($this->list);
                return $priority->getData();
            }
            
            return $datum;
        }
        
        /**
         * Pop the most urgent item off the list as a reference
         * 
         * This truncates the list from the top, and returns its value as
         * a reference
         * 
         * NOTE: This returns the priority's DATA
         * 
         * @return mixed|Phabstractic\Data\Types\None The data associated with the
         *                    highest urgency, null otherwise
         * 
         */
        public function &popReference()
        {
            $datum = $this->topReference();
            if (!($datum instanceof None)) {
                $priority = array_shift($this->list);
                return $priority->getDataReference();
            }
            
            return $datum;
        }
        
        /**
         * Pull the least urgent item off the list
         * 
         * This truncates the lsit from the bottom, and return it's value
         * 
         * NOTE: This returns the priority's DATA
         * 
         * @return mixed|Phabstractic\Data\Types\None The data associated with the
         *         lowest urgency, null otherwise
         * 
         */
        public function pull()
        {
            $datum = $this->bottom();
            if (!($datum instanceof None)) {
                $priority = array_pop($this->list);
                return $priority->getData();
            }
            
            return $datum;
        }
        
        /**
         * Pull the least urgent item off the list as a reference
         * 
         * This truncates the lsit from the bottom, and return it's value
         * 
         * NOTE: This returns the priority's DATA
         * 
         * @return mixed|Phabstractic\Data\Types\None The data reference associated
         *              with the lowest urgency, null otherwise
         * 
         */
        public function &pullReference()
        {
            $datum = $this->bottomReference();
            if (!($datum instanceof None)) {
                $priority = array_pop($this->list);
                return $priority->getDataReference();
            }
            
            return $datum;
        }
        
        /**
         * Cannot roll a priority queue
         * 
         * @throws Exception\RuntimeException
         * 
         */
        public function roll($i)
        {
            throw new Exception\RuntimeException(
                'Phabstractic\\Data\\Types\\PriorityQueue->roll: ' .
                'Cannot roll values');
        }
        
        /**
         * Push a value on to the list
         * 
         * This automatically sorts the list after all other requirements
         * have been met
         * 
         * Remember AbstractSortedList is the parent of this abstract class
         * 
         * @return int|null Count of new list, null if restrictions not met
         * 
         */
        public function push() {
            $args = func_get_args();
            $exec = 'if ( parent::push( ';
            for ($a = 0; $a < count( $args ); $a++) {
                if ($a) {
                    $exec .= ', ';
                }
                
                $exec .= "\$args[$a] ";
            }
            
            $exec .= " ) ) { \array_push( \$this->list, ";
            for ($a = 0; $a < count( $args ); $a++) {
                if ($a) {
                    $exec .= ", ";
                }
                
                $exec .= "\$args[$a] ";
            }
            
            $exec .= " ); }";
            return eval($exec);
        }
        
        /**
         * Push a reference on to the list (fifo, lifo, etc)
         * 
         * This automatically sorts the list after all other requiresments have been met
         * 
         * @return int|null Count of new list, null if restrictions are not met
         * 
         */
        public function pushReference( &$a ) {
            if (parent::push($a)) {
                $this->list[] =& $a;
                return count($this->list);
            }
            
            $none = new None();
            return $none;
        }
        
        /**
         * Exchange the two top elements of the list
         * 
         * @throws Phabstractic\Data\Types\Exception\RuntimeException
         * 
         */
        public function exchange() { 
            throw new TypesException\RuntimeException(
                'Phabstractic\\Data\\Types\\PriorityQueue->exchange: ' .
                'Cannot exchange values');
        }
        
        /**
         * Duplicate the value at the top of the list
         * 
         * @throws Phabstractic\Data\Types\Exception\RuntimeException
         * 
         */
        public function duplicate() {
            throw new TypesException\RuntimeException(
                'Phabstractic\\Data\\Types\\PriorityQueue->duplicate: ' .
                'Cannot duplicate values');
        }
        
        /**
         * Retrieve reference to priority associated with data
         * 
         * @param mixed $data The data to search for
         * 
         * @returns &Phabstractic\Data\Types\Priority
         * 
         */
        public function &retrievePriority($data) {
            foreach ($this->getList() as $key => $priority) {
                if ($priority->getData() == $data) {
                    return $this->list[$key];
                }
            }
            
            $none = new None();
            return $none;
        }
        
        /**
         * Debug Info (var_dump)
         * 
         * Display debug info
         * 
         * Requires PHP 5.6+
         * 
         */
        public function __debugInfo()
        {
            return [
                'options' => array('strict' => $this->conf->strict,),
                'list' => $this->list,
            ];
        }
        
    }
    
}
