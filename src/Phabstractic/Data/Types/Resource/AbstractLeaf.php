<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * This file contains an abstract 'leaf'.  A leaf is part of a tree structure
 * 
 * The root of the tree is a leaf, which houses other leaves, that house other
 * leaves etc.  This can sometimes be a more efficient way of storing a tree
 * structure that is easily traversable down, such as a file system tree.
 * 
 * There is no branch object connecting them, the branch is 'in the leaf' itself.
 * 
 * Like a node, each leaf has an identifier associated with it.
 * 
 * NOTE:  It is possible to connect one leaf to multiple leafs.
 *        This keeps track of data going down, not up.
 * 
 * @copyright Copyright 2015 Asher Wolfstein
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
namespace Phabstractic\Data\Types\Resource
{
    require_once(realpath( __DIR__ . '/../../../') . '/falcraftLoad.php');
    
    /*
     * Type.php
     * Restrictions.php
     * RestrictedList.php - Make the leaf only accept other leaves, not just
     *                      any object
     * 
     * LeafInterface.php - The identifying interface of a leaf, that which is
                           checked against
     * 
     */
    
    $includes = array(// we are configurable
                      '/Features/ConfigurationTrait.php',
                      '/Features/Resource/ConfigurationInterface.php',
                      // we construct a global and local identifiers
                      '/Features/IdentityTrait.php',
                      // we type check against the leaf interface
                      '/Data/Types/Resource/LeafInterface.php',
                      // we make sure we're only made up of leaves
                      '/Data/Types/Type.php',
                      '/Data/Types/Restrictions.php',
                      // we throw these exceptions
                      '/Data/Types/Exception/InvalidArgumentException.php',);
        
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types\Exception as TypesException;
    use Phabstractic\Data\Types;
    use Phabstractic\Data\Types\Type;
    use Phabstractic\Features\Resource as FeaturesResource;
    use Phabstractic\Features;
    
    /**
     * Abstract Leaf Class - Defines A Leaf/Branch Structure
     * 
     * This implements LeafInterface, but adds an identity variable
     * 
     * It leaves data handling to the child class.
     * 
     * CHANGELOG
     * 
     * 1.0: Created Module - February 7th, 2014
     * 1.1: Repurposed to Leaf - February 9th, 2014
     *         Documented Leaf - February 21st, 2014
     * 2.0: Incorporated AbstractLeaf into Primus - August 26th, 2015
     * 3.0: removed data methods
     *      removed RestrictedList, implemented using basic array
     *      reformatted for inclusion in phabstractic - August 1st, 2016
     * 
     * @abstract
     * 
     * @version 3.0
     * 
     */
    abstract class AbstractLeaf implements
        LeafInterface,
        FeaturesResource\ConfigurationInterface
    {
        use Features\ConfigurationTrait;
        use Features\IdentityTrait;
        
        /**
         * The collection of leaves this leaf is connected to
         * 
         * This uses the leave's identifiers as keys
         * 
         * @var Falcraft\Data\Types\RestrictedList
         *          (Falcraft\Data\Types\Tree\LeafInterface)
         * 
         */
        protected $leaves = array();
        
        /**
         * Get this particular leaf's identifier
         * 
         * @return string The universally unique leaf identifier
         * 
         */
        abstract public function getLeafIdentifier();
        
        /**
         * The Leaf Constructor
         * 
         * This accepts an array of leaves (LeafInterface)
         * 
         * Options - prefix: set the identifier prefix for local
         *           strict: throw errors
         * 
         * @param array $leaves A plain array of LeafInterface compatible objects
         * @param array $options An array of options, as keys (see above)
         * 
         */
        public function __construct(array $leaves = array(), $options = array())
        {
            // prefix must be string
            if (!isset($options['prefix']) || !is_string($options['prefix'])) {
                $options['prefix'] = 'Leaf';
            }
            
            $this->configure($options);
            
            if ($this->conf->prefix) {
                $this->identityPrefix = $this->conf->prefix;
            }
            
            $leafRestrictions = new Types\Restrictions(
                array(Type::TYPED_OBJECT,),
                array('Phabstractic\\Data\\Types\\Resource\\LeafInterface'),
                array('strict' => $this->conf->strict,));
            
            $allowed = Types\Restrictions::checkElements(
                            $leaves,
                            $leafRestrictions,
                            array('strict' => true,));
            
            if ($allowed) {
                foreach ($leaves as $leaf) {
                    $this->leaves[$this->getNewIdentity()] = $leaf;
                }
            } else if ($this->conf->strict) {
                throw new TypesException\InvalidArgumentException(
                    'Phabstractic\\Data\\Types\\Resource\\AbstractLeaf->' .
                    '__construct(): Illegal Value');
            }
            
        }
        
        /**
         * Get a plain array of the leaves contained by this leaf
         * 
         * @return array The connected leaves
         * 
         */
        public function getLeaves()
        {
            return $this->leaves;
        }
        
        /**
         * Add a leaf to the set of leaves this leaf is connected to
         * 
         * @param Phabstractic\Data\Type\Resource\LeafInterface $leaf
         *              The leaf to connect
         * 
         * @return string The new leaf's local identity
         * 
         */
        public function addLeaf(LeafInterface $leaf)
        {
            if ($this === $leaf) {
                if ($this->conf->strict) {
                    throw new TypesException\InvalidArgumentException(
                        'Phabstractic\\Data\\Types\\Resource\\AbstractLeaf->' .
                        'addLeaf(): Can\'t connect to leaf to self');
                }
            }
            
            $newLocalIdentity = $this->getNewIdentity();
            $this->leaves[$newLocalIdentity] = $leaf;
            
            return $newLocalIdentity;
        }
        
        /**
         * Remove a leaf
         * 
         * @param Phabstractic\Data\Type\Resource\LeafInterface $leaf
         *              The leaf to remove
         * 
         * @return bool
         * 
         */
        public function removeLeaf(LeafInterface $leaf)
        {
            $leafIdentity = array_search($leaf, $this->leaves, true);
            if ($leafIdentity) {
                unset($this->leaves[$leafIdentity]);
                return true;
            } else {
                return false;
            }
            
        }
        
        /**
         * Does this leaf have the identifier for another leaf?
         * 
         * @param Phabstractic\Data\Type\Resource\LeafInterface $leaf
         *              The leaf to find
         * 
         * @return string
         * 
         * 
         */
        public function isLeaf(LeafInterface $leaf)
        {
            return array_search($leaf, $this->leaves, true);
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
                'options' => array('strict' => $this->conf->strict,
                                   'prefix' => $this->conf->prefix,),
                'leaves' => $this->leaves,
            ];
        }
        
    }
    
}
