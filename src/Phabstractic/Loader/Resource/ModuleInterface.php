<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */
   
/**
 * This implements a Module (a leaf containing a path or path identifier)
 * 
 * Modules connect to each other like leaves, as they do in the namespace system
 * 
 * Each Module is a path itself and a folder for other leaves.
 * 
 * The idea is that we can map a namespace item (\Item1\Item2\Item3) to a path
 * so that separate namespace elements have different paths, useful in PSR-4
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Loader
 * @subpackage Modules
 * 
 */

/**
 * Falcraft Libraries Data Components Namespace
 * 
 */
namespace Phabstractic\Loader\Resource
{
    require_once(realpath( __DIR__ . '/../../') . '/falcraftLoad.php');
    
    // Inherits from the Folder Interface
    
    $includes = array( '/Data/Components/Resource/PathInterface.php', );
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Data\Components\Resource as ComponentsResource;
    
    /**
     * The Module Interface
     * 
     * Expands on the leaf interface to implement common Module
     * functions having to do with paths and path identifiers
     * 
     * CHANGELOG
     * 
     * 1.0: Created ModuleInterface - February 21st, 2014
     * 1.1: Refactored ModuleInterface to DirectoryInterface - February 27th, 2014
     * 1.2: Wrapped ModuleInterface to DirectoryInterface - February 27th, 2014
     * 2.0: Refactored for Primus 2 Integration - October 20th, 2015
     * 3.0: removed default arguments to path methods
     *      reformatted for inclusion in phabstractic - August 2nd, 2016
     * 
     * @version 2.0
     * 
     */
    interface ModuleInterface
    {
        /**
         * Retrieve the module identifier
         * 
         * @return string
         * 
         */
        public function getModuleIdentifier();
        
        /**
         * Set the module identifier
         * 
         * @return bool
         * 
         */
        public function setModuleIdentifier($newIdentifier);
        
        /**
         * Retrieve the corresponding path
         * 
         * Like getData
         * 
         * @return string
         * 
         */
        public function getPath();

        /**
         * Set the corresponding path
         * 
         * Like setData
         * 
         * @param string $path
         * 
         */
        public function setPath(ComponentsResource\PathInterface $path);
        
        /**
         * Retrieve all the connected Modules
         * 
         * Like getLeaves
         * 
         * @return array
         * 
         */
        public function getModules();
        
        /**
         * Add a Module to this Module's 'directory'.
         * 
         * Remember that the passed ModuleName will become the LOCAL identifier
         * 
         * Like addLeaf
         * 
         * @param Phabstractic\Loader\Resource\ModuleInterface $module
         * @param string|int $ModuleName
         * 
         */
        public function addModule(ModuleInterface $module);
        
        /**
         * Remove a Module from this Module's 'directory'
         * 
         * Like removeLeaf
         * 
         * @param Phabstractic\Loader\Resource\ModuleInterface $ModuleName
         *              The local Module
         * 
         */
        public function removeModule(ModuleInterface $module);
        
        /**
         * Remove a Module from this Module's 'directory'
         * 
         * Like removeLeaf but using identifier
         * 
         * @param string $ModuleName The local Module identifier
         * 
         */
        public function removeModuleByIdentifier($moduleName);
        
        /**
         * Does this Module exist in the local array?
         * 
         * Like isLeaf
         * 
         * @param Phabstractic\Loader\Resource\ModuleInterface $Module
         *              The local Module
         * 
         */
        public function isSubModule($module);
        
        /**
         * Does this Module identifier exist in the local array?
         * 
         * Like isLeaf
         * 
         * @param string $moduleName The local Module identifier
         * 
         */
        public function isSubModuleByIdentifier($moduleName);
        
        /**
         * Parse the path from this module and add new module
         * 
         * @param string $path
         * @param Phabstractic\Loader\Resource\ModuleInterface $newModule
         * 
         */
        public function addToModuleIdentityPath(
            $path,
            ModuleInterface $newModule
        );
        
        /**
         * Return module structure from this module as array
         * 
         * See above, does the reverse.  This is a good way
         * to see how the above is formatted.
         * 
         * @return array
         * 
         */
        public function getModulesAsArray();
        
        /**
         * Retrieve a Module using a path and a root
         * 
         * This starts at a given root Module, and then
         * parses the path (delimited by \'s) until it reaches
         * the intended module.  The path refers to identifiers
         * as stored in the identifier of the actual module
         * 
         * @param string $path The path to the desired Folder (this\is\a\path)
         * 
         * @return Phabstractic\Loader\Resource\LeafInterface|Phabstractic\Data\Types\None
         *              A reference, none on failure
         * 
         */
        public function &getFromModuleIdentityPath($path);
        
        /**
         * Get Module Identifier Paths
         * 
         * This returns a list of all terminating paths
         * 
         * Ex:
         * 
         * [] Module1\Module2\Module3
         * [] Module1\Module4
         * [] Module1\Module5\Module6\Module7
         * 
         * @param $path A recursive function argument, don't use
         * 
         */
        public function getModuleIdentityPaths();
        
        /**
         * Returns a module path that contains the data
         * 
         * NOTE: Only returns the first instance it finds
         * 
         * Okay, this returns a modue path to the module that contains the
         * associated path
         * 
         * @param $path The path to find
         * 
         */
        public function pathBelongsTo($path);
        
    }
    
}
