<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * A concrete implementation of a basic leaf
 * 
 * Associates a piece of data with a leaf in a tree
 * 
 * @see Phabstractic\Data\Types\Resource\AbstractLeaf
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
namespace Phabstractic\Data\Types
{
    require_once(realpath( __DIR__ . '/../../') . '/falcraftLoad.php');
    
    /*
     * LeafInterface.php - The identifying interface of a leaf, that which is
     *                     checked against
     * 
     */
    
    $includes = array(// we implement leaf interface 
                      '/Data/Types/Resource/LeafInterface.php',
                      '/Data/Types/Resource/AbstractLeaf.php',
                      // we carry an identity
                      '/Features/IdentityTrait.php',
                      // stati functions return none
                      '/Data/Types/None.php',);
        
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Types\Resource as TypesResource;
    use Phabstractic\Features;
    
    /**
     * Leaf Class
     * 
     * Inherits from Abstract, identity and configuration, this implements
     * the data storage.
     * 
     * CHANGELOG
     * 
     * 1.0: Created Module - February 7th, 2014
     * 1.1: Repurposed to Leaf - February 9th, 2014
     *         Documented Leaf - February 21st, 2014
     * 2.0: Incorporated AbstractLeaf into Primus - August 26th, 2015
     * 3.0: reformatted for inclusion in phabstractic - August 1st, 2016
     * 
     * @version 3.0
     * 
     */
    class Leaf extends TypesResource\AbstractLeaf implements
        TypesResource\LeafInterface
    {
        use Features\IdentityTrait;
        
        /**
         * The leaf's identifier
         * 
         * @var mixed
         * 
         */
        private $identifier;
        
        /**
         * The Leaf's Data Property
         * 
         * @var mixed
         * 
         */
        private $data;
        
        /**
         * Get the data associated with the leaf
         * 
         * @return mixed The data
         * 
         */
        public function getData()
        {
            return $this->data;
        }
        
        /**
         * Get data as reference
         * 
         * @return &mixed The data reference
         * 
         */
        public function &getDataReference() {
            return $this->data;
        }
        
        /** 
         * Set the data to be associated with the leaf
         * 
         * @param mixed $data The data
         * 
         */
        public function setData($data = null)
        {
            $this->data = $data;
        }
        
        /**
         * Get leaves data
         * 
         * @return array
         * 
         */
        public function getLeavesData() {
            $ret = array();
            foreach ($this->leaves as $leaf) {
                if ($leaf instanceof Leaf) {
                    $ret[] = $leaf->getData();
                }
            }
            
            return $ret;
        }
        
        /**
         * Get this particular leaf's identifier
         * 
         * Warning: You can create multiple leaves with the same
         * identifier by changing the identifier at a later time.
         * 
         * @return string The universally unique vertex identifier
         * 
         */
        public function getLeafIdentifier()
        {
            if (!$this->identifier)
                $this->identifier = $this->getNewIdentity();
            return $this->identifier;
        }
        
        /**
         * Set this particular leaf's identifier
         * 
         * Warning: You can create multiple leaves with the same
         * identifier by changing the identifier at a later time.
         * 
         * @return bool
         *
         */
        public function setLeafIdentifier($newIdentifier)
        {
            return false;
        }
        
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
        public function __construct(
            $data = null,
            array $leaves = array(),
            $options = array()
        ) {
            $options = array_change_key_case($options);
            
            if (!isset($options['prefix'])) {
                $options['prefix'] = 'Leaf';
            }
        
            $this->configure($options);
            
            if ($this->conf->prefix) {
                $this->identityPrefix = $this->conf->prefix;
            }
            
            $this->data = $data;
            
            parent::__construct($leaves, $options);
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
            $ret = parent::__debugInfo();
            
            return [
                'options' => $ret['options'],
                'identifier' => $this->getLeafIdentifier(),
                'leaves' => $ret['leaves'],
            ];
        }
        
        /**
         * Take a root leaf, parse the path, and add the given leaf
         * 
         * Takes a root leaf, calls getFromLeafIdentityPath, and then adds
         * the new leaf to the resulting leaf.
         * 
         * @param Phabstractic\Data\Types\Resource\AbstractLeaf $root
         * @param string $path
         * @param Phabstractic\Data\Types\Resource\LeafInterface
         *              $newLeaf
         * 
         */
        public static function addToLeafIdentityPath(
            TypesResource\AbstractLeaf $root,
            $path,
            TypesResource\LeafInterface $newLeaf,
            $delimiter = '/'
        ) {
            $branch = Leaf::getFromFolderIdentityPath($root, $path, $delimiter);
            $branch->addLeaf($newLeaf);
        }
        
        /**
         * Take an array of Leaves, and create the Tree
         * 
         * Each array will have a data element for the data
         * and then a leaves element whose entries are other
         * data.  An empty array ends the sequence
         * 
         * Very recursive
         * 
         * @param array $build Not an array of leaves, but as above
         * 
         */
        public static function buildFromArray($build = array())
        {
            if (isset($build['data'])) {
                $ret = new Leaf($build['data']);
            } else {
                $ret = new Leaf();
            }
            foreach ($build['leaves'] as $leaf) {
                $ret->addLeaf(Leaf::buildFromArray($leaf));
            }
            
            return $ret;
        }
        
        /**
         * Return Leaf structure from root as array
         * 
         * See above, does the reverse.  This is a good way
         * to see how the above is formatted.
         * 
         * @param Phabstractic\Data\Types\Resource\AbstractLeaf
         *              $root The root to start at
         * 
         * @return array
         * 
         */
        public static function getAsArray(
            TypesResource\AbstractLeaf $root
        ) {
            $leaves = $root->getLeaves();
            
            if (!$leaves) {
                return array('data' => $root->getData(), 'leaves' => array());
            }
            
            $ret = array();
            
            foreach ($leaves as $leaf) {
                $ret['data'] = $leaf->getData();
                $ret['leaves'][$leaf->getLeafIdentifier()] =
                    Leaf::getAsArray($leaf);
            }
            
            return $ret;
        }
        
        /**
         * Retrieve a Leaf using a path and a root
         * 
         * This starts at a given root Leaf, and then
         * parses the path (delimited by '/'s) until it reaches
         * the intended Leaf.  The path refers to identifiers
         * as stored in the identifier array of the actual Leaf
         * 
         * @param Phabstractic\Data\Types\Resource\AbstractLeaf
         *              $root The Leaf to begin at
         * @param string $path The path to the desired Folder (this/is/a/path)
         * 
         * @return Phabstractic\Data\Type\Resource\LeafInterface|Phabstractic\Data\Types\None
         *              A reference, none on failure
         * 
         */
        public static function &getFromLeafIdentityPath(
            TypesResource\AbstractLeaf $root,
            $path,
            $delimiter = '/'
        ) {
            $path = explode($delimiter, $path);
            
            if ($root->getLeafIdentifier() == $path[0]) {
                if (count($path) == 1) {
                    return $root;
                }
                
                array_shift($path);
                
                foreach($root->getLeaves() as $leaf )
                {
                    $test = Leaf::getFromLeafIdentityPath(
                        $leaf,
                        implode($delimiter, $path),
                        $delimiter
                    );
                    
                    if (!($test instanceof None)) {
                        return $test;
                    }
                }
                
                $ret = new None();
                return $ret;
            } else {
                $ret = new None();
                return $ret;
            }
            
        }
        
        /**
         * Get Leaf Identifier Paths
         * 
         * This returns a list of all terminating paths
         * 
         * Ex:
         * 
         * [] Leaf7/Leaf8/TestPrefix9
         * [] Leaf12/Leaf1
         * [] Leaf19/TestPrefix10/AnotherPrefix4/Leaf76
         * 
         * @param Phabstractic\Types\Resource\AbstractLeaf
         *              $root The leaf to start at
         * @param $path A recursive function argument, don't use
         * 
         */
        public static function getLeafIdentityPaths(
            TypesResource\AbstractLeaf $root,
            $path = '',
            $delimiter = '/'
        ) {
            static $paths;
            
            if ($path) {
                $leaves = $root->getLeaves();
                
                if (!$leaves) {
                    $paths[] = $path;
                    return true;
                }
                
                foreach ($leaves as $leaf) {
                    Leaf::getLeafIdentityPaths(
                        $leaf,
                        $path . $delimiter . $leaf->getLeafIdentifier(),
                        $delimiter
                    );
                }

                return false;
            } else {
                $paths = array();
                
                foreach($root->getLeaves() as $leaf) {
                    Leaf::getLeafIdentityPaths(
                        $leaf,
                        $leaf->getLeafIdentifier(),
                        $delimiter
                    );
                }
                
                for ($a = 0; $a < count($paths); $a++) {
                    $paths[$a] = $root->getLeafIdentifier() . $delimiter . $paths[$a];
                }
                    
                return $paths;
            }
        }
        
        /**
         * Returns a leaf path that contains the data
         * 
         * NOTE: Only returns the first instance it finds
         * 
         * Okay, this returns a leaf path to the leaf that contains the
         * associated data
         * 
         * @param $data The data to find
         * @param Phabstractic\Data\Types\Resource\AbstractLeaf
         *              $root The root of the tree
         * @param string $recurseKey Recursive function argument, don't use
         * @param Phabstractic\Data\Types\Resource\AbstractLeaf
         *              $recurseLeaf Recursive function argument, don't use
         * 
         */
        public static function dataBelongsTo(
            $data,
            TypesResource\AbstractLeaf $root,
            $recurseLeaf = null,
            $delimiter = '/'
        ) {
            static $leafNames;
            
            if ($recurseLeaf) {
                if ($recurseLeaf->getData() === $data) {
                    return true;
                }
                
                foreach ($recurseLeaf->getLeaves() as $leafValue ) {
                    if (Leaf::dataBelongsTo(
                        $data,
                        $root,
                        $leafValue,
                        $delimiter)
                    ) {
                        $leafNames = $leafValue->getLeafIdentifier() . $delimiter . $leafNames;
                        return true;
                    }
                    
                }

                return false;
            } else {
                $leafNames = '';
                
                foreach($root->getLeaves() as $leafValue ) {
                    if (Leaf::dataBelongsTo(
                        $data,
                        $root,
                        $leafValue,
                        $delimiter)
                    ) {
                        $leafNames = $leafValue->getLeafIdentifier() . $delimiter . $leafNames;
                    }
                }
                
                $leafNames = $root->getLeafIdentifier() . $delimiter . $leafNames;
                    
                return $leafNames;
            }
        }
        
    }
    
}
