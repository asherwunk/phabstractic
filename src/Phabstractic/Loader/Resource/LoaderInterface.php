<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Generic Autoloader Interface
 * 
 * This file contains the generic autoloader interface.  USe this when
 * defining a specific autoloader (strategy) such as standard, psr-0, classmap, etc.
 * 
 * A path is simply a directory to look in after Modules and prefixes, like
 * an include path.  The path looked for will be PSR-0 compliant, converting
 * appropriate characters and all-such.  A path set to default is used as the
 * 'project's base directory
 * 
 * A Module is a specific part of a library supplied by a vendor, this may
 * or may not live in the same path, it could exist in a different path then
 * the default vendor path.  What happens then is when the vendor\Module namespace
 * is encountered the new path is substituted in before adding any additional
 * path elements.  Think PSR-4
 * 
 * vvv
 * 
 * A prefix defines class prefixes that don't fit into PSR-0 or otherwise
 * compliant architectures.  For example, lets say in the Logging Module there is a class
 * titled, WP_BaseLog, WP_ is the prefix but is not necessarily provided in
 * the directory structure as might be expected of PSR-0 etc.  When the autoloader
 * encounters a prefix in the final class name, it first checks to see if that file name
 * exists, then takes out the prefix and attempts to load the class file again.
 * 
 * ^^^ Not PSR-0 standard
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Loader
 * @subpackage Resource
 * 
 */

/**
 * Falcraft Libraries Loader Resource Namespace
 * 
 */
namespace Phabstractic\Loader\Resource;
{
    /**
     * The Generic Autoloader Interface
     * 
     * This interface establishes a common lexicon for different
     * autoloader objects/strategies.
     * 
     * It creates functions all autoloading strategies have:
     * registering, adding and removing paths, and autoloading
     * 
     * It also gives the developer an opportunity to employ
     * prefixes and Modules through given functions.
     * 
     * CHANGELOG
     * 
     * 1.0 Created Generic Interface - December 20th, 2013
     * 1.1 Revamped Generic Interface for better compatibility
     *     with current and past practices - January 29th, 2014
     * 1.2 Finalized - February 21st, 2014
     * 2.0 Refactored and Integrated with Primus 2 - October 20th, 2015
     * 3.0: eliminated default path
     *      reformatted for inclusion in phabstractic - August 3rd, 2016
     * 
     * @version 3.0
     * 
     */
    interface LoaderInterface
    {
        /**
         * Autoload a particular class from a given string
         *
         * This is the string provided by the PHP autoloading mechanisms
         * 
         * @param string $class The string referencing the class (namespaced)
         * 
         * @return boolean True on success
         * 
         */
        public function autoload($class);
    
        /**
         * Register the autoloader with spl_autoload registry
         * 
         */
        public function register();
        
        /**
         * Un-register the autoloader with the spl_autoload registry
         * 
         */
        public function deregister();
        
        /**
         * Return all the paths in an array
         * 
         * actual paths, string of arrays
         * 
         * @return array
         * 
         */
        public function getPaths();
        
        /**
         * Register a path with the autoloader
         * 
         * This function can be used in multiple ways depending on the
         * autoloader.  It is meant to be used as sort of an additional
         * include path.
         * 
         * @param string $newPath The include path
         * 
         * @return bool false on failure
         * 
         */
        public function addPath($newPath);
          
        /**
         * Is a given path present in the autoloader?
         * 
         * @param string $path The given path to test, or identifier
         * 
         * @return bool false on failure
         * 
         */
        public function isPath($path);
        
        /**
         * Remove a path with the autoloader
         * 
         * This function servers the opposite functionality
         * of the addPath function.  If a path doesn't exist,
         * it does nothing
         * 
         * @param string $path The path to exclude/remove
         * 
         * @return boolean Successfully removed (was present?)
         * 
         */
        public function removePath($path);
        
        /**
         * Register a prefix with the autoloader
         * 
         * This function can be used in multiple ways depending on the
         * autoloader.  It is mean to be used as a modifier to file
         * loading associated with a particular path
         * 
         * @param string $path The path to associate the prefix to
         * @param string $prefix The prefix identifier/content
         * 
         */
        public function addPrefix($path, $prefix);
         
        /**
         * Search for the existence of a specific prefix
         * 
         * @param string $prefix The prefix identifier/content
         * 
         * @return array Paths the prefix is associated with, empty on failure
         * 
         */
        public function isPrefix($prefix);
        
        /**
         * Retrieve all prefixes associated with a path
         * 
         * The path can be an existing path
         * 
         * @param string $path The path
         * 
         * @return array
         * 
         */
        public function getPrefixes($path);
          
        /**
         * Remove a registered prefix from the autoloader
         *
         * Automatically de-couples the prefix from all assigned paths
         * 
         * @param string $path The path associated
         * @param string $prefix The prefix Identifier/content
         * 
         * @return boolean True if found and removed
         * 
         */
        public function removePrefix($path, $prefix);
        
        /**
         * Retrieve list of namespaces
         * 
         * @return array
         * 
         */
        public function getNamespaces();
        
        /**
         * Register namespace with the autoloader
         * 
         * This function can be used in multiple ways depending on the
         * autoloader.  It is meant to be use as a sort of an additional
         * include path linked to a given library/vendor name
         * 
         * @param string $namespace The base namespace
         * @param string $path The include path
         * 
         * @return bool True on success
         * 
         */
        public function addNamespace($namespace, $path);
        
        /**
         * Is this namespace registered with this autoloader?
         * 
         * @param string $namespace The namespace of the library
         * 
         * @return boolean True if present
         * 
         */
        public function isNamespace($namespace);
        
        /**
         * Unregister a namespace with the autoloader
         * 
         * Performs the opposite functionality of the addLibrary method
         * 
         * Returns false if Module is not present in autoloader
         * 
         * @param string $namespace The library path/identifier
         * 
         * @return boolean True on successful removal
         * 
         */
        public function removeNamespace($namespace);
        
    }
    
}
