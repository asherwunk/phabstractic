<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Module Data Structure
 * 
 * This implements a Module structure.  A Module structure is a recursive
 * list of relative and absolute paths, with Module names as keys.
 * 
 * This of each module being an element in a namespace, then anchor that namespace
 * to a path.
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programing-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Loader
 * @subpackage Modules
 * 
 */

/**
 * Falcraft Libraries Loader Components Namespace
 * 
 */
namespace Phabstractic\Loader
{
    require_once(realpath( __DIR__ . '/../') . '/falcraftLoad.php');
    
    /* Folder.php - Inherits from Folder
       ModuleInterface.php - type checks against interface */
        
    $includes = array(// we inherit from types\leaf
                      '/Data/Types/Leaf.php',
                      // we type check using restrictions and sets
                      '/Data/Types/Type.php',
                      '/Data/Types/Restrictions.php',
                      // we typecheck against pathinterface
                      '/Data/Components/Resource/PathInterface.php',
                      // we implement moduleinterface
                      '/Loader/Resource/ModuleInterface.php',
                      // we throw this exception
                      '/Loader/Exception/InvalidArgumentException.php',);
    
    falcraftLoad($includes, __FILE__);
   
    use Phabstractic\Data\Types;
    use Phabstractic\Data\Types\Type;
    use Phabstractic\Loader\Resource as LoaderResource;
    use Phabstractic\Loader\Exception as LoaderException;
    use Phabstractic\Data\Components\Resource as ComponentsResource;
    
    /**
     * Module Class
     * 
     * The Module class is a leaf structure that contains a path
     * (or path identifier).  Each path represented might be relative
     * or absolute, or something entirely different.
     * 
     * Technically it's a string strung up as a tree.
     * 
     * Each Module has it's own path, and it's own directory of other Modules.
     * 
     * The idea is that we can map a namespace item (\Item1\Item2\Item3) to a path
     * so that separate namespace elements have different paths, useful in PSR-4
     * 
     * CHANGELOG
     * 
     * 1.0: Created Module - February 21st, 2014
     * 1.1: Refactored Module to CDirectory.php - February 27th, 2014
     * 1.2: Wrapped Module around CDirectory.php - February 27th, 2014
     * 2.0: Refactored and integrated with Primus 2 - October 20th, 2015
     * 3.0: eliminated isPath method
     *      reformatted for inclusion in phabstractic - August 2nd, 2016
     * 
     * @version 2.0
     * 
     */
    class Module extends Types\Leaf implements LoaderResource\ModuleInterface
    {
        /**
         * The Module Constructor
         * 
         * This accepts an array of modules (ModuleInterface)
         * 
         * Options - prefix: set the identifier prefix for local
         *           strict: throw errors
         * 
         * @param Phabstractic\Data\Components\Path The path to store
         * @param string The module identifier
         * @param array $modules A plain array of ModuleInterface compatible objects
         * @param array $options An array of options, as keys (see above)
         * 
         */
        public function __construct(
            $path = null,
            $identifier = null,
            array $modules = array(),
            $options = array()
        ) {
            $options = array_change_key_case($options);
            
            if (!isset($options['prefix'])) {
                $options['prefix'] = 'Module';
            }
        
            $this->configure($options);
            
            if ($this->conf->prefix) {
                $this->identityPrefix = $this->conf->prefix;
            }
            
            $this->setModuleIdentifier($identifier);
            if ($path && ($path instanceof ComponentsResource\PathInterface)) {
                $this->setPath($path);
            }
            
            $moduleRestrictions = new Types\Restrictions(
                array(Type::TYPED_OBJECT,),
                array('Phabstractic\\Loader\\Resource\\ModuleInterface'),
                array('strict' => $this->conf->strict,));
            
            $allowed = Types\Restrictions::checkElements(
                            $modules,
                            $moduleRestrictions,
                            $this->conf->strict);
            
            if ($allowed) {
                parent::__construct($path, $modules, $options);
            } else if ($this->conf->strict) {
                throw new LoaderException\InvalidArgumentException(
                    'Phabstractic\\Loader\\Module->' .
                    '__construct(): Illegal Value');
            }
        }
        
        /**
         * Override default leaf identifier behavior
         * 
         */
        public function getLeafIdentifier()
        {
            return $this->identifier;
        }
        
        /**
         * Get this particular module's identifier
         * 
         * Warning: You can create multiple modules with the same
         * identifier by changing the identifier at a later time.
         * 
         * @return string The module identifier
         * 
         */
        public function getModuleIdentifier()
        {
            return $this->getLeafIdentifier();
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
            $this->identifier = $newIdentifier;
            
            return true;
        }
        
        /**
         * Set this particular module's identifier
         * 
         * Warning: You can create multiple leaves with the same
         * identifier by changing the identifier at a later time.
         * 
         * @return bool
         *
         */
        public function setModuleIdentifier($newIdentifier)
        {
            return $this->setLeafIdentifier($newIdentifier);
        }
        
        /**
         * GetPath - Get the associated path
         * 
         * @return Phabstractic\Data\Components\Path
         * 
         */
        public function getPath()
        {
            return $this->getData();
        }
        
        /**
         * Set the associated path
         * 
         * @param Phabstractic\Data\Components\Path $path
         * 
         */
        public function setPath(ComponentsResource\PathInterface $path)
        {
            return $this->setData($path);
        }
        
        /**
         * GetModules - Wrapper
         * 
         * A wrapper function for underlying abstract leaf functionality
         * 
         * @return array The Modules
         *  
         */
        public function getModules()
        {
            return $this->getLeaves();
        }
        
        /**
         * Add Module - Wrapper
         * 
         * A wrapper function for underlying abstract leaf functionality
         * 
         * @param Falcraft\Loader\Resource\ModuleInterface $module
         *          The actual module to add
         * @param string|int $moduleName The module identifier
         * 
         * @return mixed What addleaf returns
         * 
          */
        public function addModule(LoaderResource\ModuleInterface $module) {
            
            return $this->addLeaf($module);
        }
        
        /**
         * Remove Module - Wrapper
         * 
         * A wrapper function for underlying abstract leaf functionality
         * 
         * @param Phabstractic\Loader\Resource\ModuleInterface $module
         *              The local module
         * 
         * @return mixed What removeleaf returns
         * 
         */
        public function removeModule(LoaderResource\ModuleInterface $module)
        {
            return $this->removeLeaf($module);
        }
        
        /**
         * Remove Module By Identifier - Wrapper
         * 
         * A wrapper function for underlying abstract leaf functionality
         * 
         * @param string|int $moduleName The local module identifier
         * 
         * @return mixed What removeleaf returns
         * 
         */
        public function removeModuleByIdentifier($moduleName)
        {
            $modules = $this->getLeaves();
            $remove = null;
            
            foreach ($modules as $module) {
                if ($module->getLeafIdentifier() == $moduleName) {
                    $remove = $module;
                }
            }
            
            if ($remove) {
                return $this->removeLeaf($remove);
            }
            
            return false;
        }
        
        /**
         * Is Sub Module - Wrapper
         * 
         * A wrapper function for underlying abtract leaf functionality
         * 
         * @param Phabstractic\Loader\Resource\ModuleInterface $module
         *              the module
         * 
         * @return boolean Are we in the module's 'directory'?
         * 
         */
        public function isSubModule($module)
        {
            return $this->isLeaf($module);
        }
        
        /**
         * Is Sub Module - Wrapper
         * 
         * A wrapper function for underlying abtract leaf functionality
         * 
         * @param string $moduleName the module identifier
         * 
         * @return boolean Are we in the module's 'directory'?
         * 
         */
        public function isSubModuleByIdentifier($moduleName)
        {
            $modules = $this->getLeaves();
            
            foreach ($modules as $module) {
                if ($module->getLeafIdentifier() == $moduleName) {
                    return true;
                }
            }
            
            return false;
        }
        
        /**
         * Retrieve a Module using a path from this module
         * 
         * This starts at a given root Module, and then
         * parses the path (delimited by '\'s) until it reaches
         * the intended Module.  The path refers to identifiers
         * as stored in the identifier array of the actual Module
         * 
         * @param string $path The path to the desired Module (this\is\a\path)
         * 
         * @return Falcraft\Loader\Module|Falcraft\Data\Types\None
         *              A reference, none on failure
         * 
         */
        public function &getFromModuleIdentityPath($path) {
            return Types\Leaf::getFromLeafIdentityPath($this, $path, '\\');
        }
        
        /**
         * Take this Module, parse the path, and add the given Module
         * 
         * Takes this Module, calls getFromModulePath, and then adds
         * the newModule to the resulting Module.
         * 
         * @param string $path
         * @param Falcraft\Loader\Resource\ModuleInterface $newModule
         * 
         */
        public function addToModuleIdentityPath(
            $path,
            LoaderResource\ModuleInterface $newModule
        ) {
            return Types\Leaf::addToLeafIdentityPath(
                $this,
                $path,
                $newModule,
                '\\'
            );
        }
        
        /**
         * Return Modules structure from root as array
         * 
         * @return array
         * 
         */
        public function getModulesAsArray() {
            $leaves = $this->getLeaves();
            
            if (!$leaves) {
                return array('path' => $this->getData(), 'modules' => array());
            }
            
            $ret = array();
            
            foreach ($leaves as $leaf) {
                $ret['path'] = $leaf->getData();
                $ret['modules'][$leaf->getLeafIdentifier()] =
                    $leaf->getModulesAsArray();
            }
            
            return $ret;
        }
        
        /**
         * Get Module Paths
         * 
         * This returns a list of all terminating paths
         * 
         * Ex:
         * 
         * [] Module1\Module2\Module3
         * [] Module1\Module4
         * [] Module1\Module5\Module6\Module7
         * 
         * @param Falcraft\Data\Components\File\Resource\DirectoryInterface $rootModule The Module to start at
         * @param $ModulePath A recursive function argument, don't use
         * 
         */
        public function getModuleIdentityPaths() {
            return Types\Leaf::getLeafIdentityPaths($this, '', '\\');
        }
        
        /**
         * Returns a Module path that contains the path
         * 
         * Okay, this returns a Module path to the Module that contains the associated path
         * 
         * @param $path The path to find (NOT Modulepath)
         * 
         */
        public function pathBelongsTo($path) {
            return Types\Leaf::dataBelongsTo(
                $path,
                $this,
                null,
                '\\'
            );
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
                'modules' => $ret['leaves'],
            ];
        }
        
    }
}
