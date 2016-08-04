<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Module Abstraction
 * 
 * This implements the GenericInterface and adds options functionality
 * via the configuration trait.  It implements basic path, Module, and prefix
 * tracking functionality (to be likely overwritten)
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
namespace Phabstractic\Loader\Resource
{
    require_once(realpath( __DIR__ . '/../../') . '/falcraftLoad.php');
    
    /* GenericInterface.php - We implement the Generic Autoloader Interface
       
       Path.php - The path object, useful for checking and retrieving files with various extensions
       
       Configuration.php - Standard Object for options
       
       Module.php - libraries is a Module tree
       
       Types.php
       Restrictions.php
       RestrictedList.php - The list of paths */
        
    $includes = array('/Loader/Resource/GenericLoaderInterface.php',
                      '/Loader/Module.php',
                      '/Loader/Resource/ModuleInterface.php',
                      '/Data/Components/File/Path.php',
                      '/Features/Configuration.php',
                      '/Data/Types/Type.php',
                      '/Data/Types/Restrictions.php',
                      '/Data/Types/RestrictedList.php',
                      '/Data/Types/Null.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Falcraft\Data\Components\File;
    use Falcraft\Loader;
    use Falcraft\Features;
    use Falcraft\Data\Types\Type;
    use Falcraft\Data\Types;
    
    /**
     * The Abstract Autoloader
     * 
     * This piece of machinery keeps track of paths, prefixes, libraries and Modules
     * 
     * @see Falcraft\Loader\Resource\GenericInterface
     * 
     * First paths are stored in an array of Path objects
     * (each having an identifier).  Libraries are stored as
     * a root to a tree of Modules.  (See Leaf and Tree data types).
     * 
     * Every Module/library has a path identifier associated with it.
     * 
     * Prefixes are stored in an array with path identifiers as the keys
     * and the prefixes associated with those identifiers as array values.
     * 
     * For more detailed information @see Falcraft\Loader\Resource\GenericInterface.php
     * 
     * CHANGELOG
     * 
     * 1.0 Created AbstractLoader - January 31st, 2014
     * 2.0 Refactored for integration with Primus 2 - October 20th, 2015
     * 
     * @abstract
     * 
     * @version 2.0
     * 
     */
    abstract class AbstractLoader implements GenericLoaderInterface
    {
        use Features\Configuration;
        
        /**
         * The list of path objects
         * 
         * Keys are identifiers, values are path objects
         * 
         * @var Falcrat\Data\Types\RestrictedList (Falcraft\Data\Components\File\Path)
         */
        protected $paths;
        
        /**
         * A multi-dimensional array of prefixes tied to paths
         * 
         * Keys are path identifiers, values are array of prefixes
         * 
         * @var array
         * 
         */
        protected $prefixes;
        
        /**
         * The Default Path (By Identifier)
         * 
         * @var string
         * 
         */
        protected $default = '';
        
        /**
         * The root Module for the libraries
         * 
         * This is the beginning of the tree for Module objects
         * 
         * @var Falcraft\Data\Components\File\Module
         * 
         */
        protected $libraries;
        
        /**
         * Autoload the given class as given by spl
         * 
         * @var string
         * 
         */
        abstract public function autoload( $class );
        
        /**
         * Abstract Loader Constructor
         * 
         * This takes in an array of paths (NOT identifiers, actual paths) and
         * an array of options.  It sets up the paths into appropriate objects
         * and sets up the library root Module.
         * 
         * OPTIONS - AutoRegister: Register the load in the SPL (false default)
         *           FileExtension: What file extension to use as a base for paths
         *           DefaultPath: The default path
         *                           The first path in the path array, or string becomes
         *                               the default path unless otherwise specified here.
         * 
         * @param array $paths The actual PATHS to put in the autoloader
         * @param array $options See description
         * 
         */
        public function __construct($paths = array(), $options = array())
        {
            if (is_array($options)) {
                $options = array_change_key_case($options);
            }
            
            $options = array_merge(array('autoregister' => false,
                                         'fileextension' => 'php',
                                         'defaultpath' => '',), $options);
            
            $this->configure($options);
            
            $pathRestrictions = new Types\Restrictions(
                array(Type::TYPED_OBJECT),
                array('Falcraft\\Data\\Components\\File\\Path'),
                array('strict' => $this->conf->strict)
            );
            
            $this->paths = new Types\RestrictedList(
                array(),
                $pathRestrictions,
                array('strict' => $this->conf->strict)
            );
            
            if ($this->conf->defaultpath) {
                $this->setDefaultPath($this->addPath($this->conf->defaultpath));
            }
            
            if (is_array($paths)) {
                foreach ($paths as $path) {
                    if (is_array($path)) {
                        if (isset($path['path']) &&
                                isset($path['identifier'])) {
                            $identifier = $this->addPath(
                                $path['path'],
                                $path['identifier']
                            );
                        }
                        
                    } else {
                        $identifier = $this->addPath($path);
                    }
                    
                    if (!$this->default) {
                        $this->setDefaultPath($this->$identifier);
                    }
                    
                }
                
            } else {
                $identifier = $this->addPath($paths);
                $this->setDefaultPath($identifier);
            }
            
            if ($this->conf->fileextension != 'php') {
                foreach ($this->paths->getList() as $identifier => $path) {
                    $this->paths[$identifier]->removeExtension('php');
                    $this->paths[$identifier]->addExtension(
                        $this->conf->fileextension
                    );
                }
            }
            
            $this->prefixes = array();
            
            $this->libraries = new Loader\Module(
                '/',
                'libraries',
                array(),
                array('strict' => $this->conf->strict)
            );
            
            $this->addLibrary('Falcraft', 'Falcraft');
            
            if ($this->conf->autoregister ) {
                $this->register();
            }
            
        }
        
        /**
         * Register the autoloader with the SPL
         * 
         */
        public function register()
        {
            spl_autoload_register(array($this, 'autoload'));
        }

        /**
         * De (un) register the autoloader withthe SPL
         * 
         */
        public function deregister()
        {
            spl_autoload_unregister(array($this, 'autoload'));
        }
        
        /**
         * Set the default path
         * 
         * NOTE: Detects paths, but only ones that already have been identified
         *          Does not add a path to the list
         * 
         * @param string|int $identifier The path identifier (preferably)
         * 
         */
        public function setDefaultPath($identifier)
        {
            if ($this->isIdentifier($identifier)) {
                $this->default = $identifier;
            } else if ($this->isPath($dentifier)) {
                $this->default = $this->getIdentifier($identifier);
            }
        }
        
        /**
         * Get the default path
         * 
         * This returns an IDENTIFIER, you still have to retrieve the path
         * using the identifier.
         * 
         * @return string|int
         * 
         */
        public function getDefaultPath()
        {
            return $this->default;
        }
        
        /**
         * Add a path to the path pool
         * 
         * Use Identifier to override the built in identity engine
         * Otherwise to get the identifier you'll have to pass the path
         * back into the class.
         * 
         * @param string $path The actual path to add
         * @param string|int The desired path identifier
         * 
         */
        public function addPath($path, $identifier = '')
        {
            if ($path instanceof File\Path) {
                if (!$identifier) {
                    $identifier = $path->getIdentifier();
                }
                
                if (!array_key_exists($identifier, $this->paths->getList())) {
                    $this->paths[$identifier] = $path;
                }
                
                if ($this->conf->fileextension != 'php') {
                    $this->paths[$identifier]->removeExtension('php');
                    $this->paths[$identifier]->addExtension(
                        $this->conf->fileextension
                    );
                }
                
                return $identifier;
            } else {
                if (!in_array($path, $this->getPaths())) {
                    $addition = new File\Path($path, $identifier);
                    $identifier = $addition->getIdentifier();
                    $this->paths[$identifier] = $addition;
                    
                    return $identifier;
                } else {
                    return $this->getIdentifier( $path );
                }
                
            }
            
            return false;
        }
        
        /**
         * Return all the paths in an array
         * 
         * NOT identifiers, actual paths
         * 
         * @return array
         * 
         */
        public function getPaths()
        {
            $ret = array();
            foreach ($this->paths->getList() as $path) {
                $ret[] = $path->getPath();
            }
            
            return $ret;
        }
        
        /**
         * Retrieve a path by identifier
         * 
         * Uses IDENTIFIERS not paths
         * 
         * @return string
         * 
         */
        public function getPath($identifier)
        {
            foreach($this->paths->getList() as $path) {
                if ($path->getIdentifier() == $identifier) {
                    return $path->getPath();
                }
            
            }
            
        }
        
        /**
         * Gets a path's identifier
         * 
         * IF it's in the path list
         * 
         * @param string $pathToIdentify The given path
         * 
         * @return string|int Identifier
         * 
         */
        public function getIdentifier($pathToIdentify)
        {
            foreach ($this->paths->getList() as $path) {
                if ($path->getPath() == $pathToIdentify) {
                    return $path->getIdentifier();
                }
                
            }
            
        }
        
        /**
         * Get the path object itself from an identifier
         * 
         * @param string|int $identifier The given identifier, must be identifier not path
         * 
         * @return Falcraft\Data\Components\File\Path
         * 
         */
        public function getPathObject($identifier)
        {
            if ($this->isIdentifier($identifier)) {
                foreach ($this->paths->getList() as $path) {
                    if ($path->getIdentifier() == $identifier) {
                        return $path;
                    }
                    
                }
                
            } else {
                return null;
            }
        }
        
        /**
         * Get a reference to the path object itself from an identifier
         * 
         * @param string|int $identifier The given identifier, must be identifier not path
         * 
         * @return Falcraft\Data\Components\File\Path
         * 
         */
        public function &getPathObjectReference($identifier)
        {
            if ($this->isIdentifier($identifier)) {
                foreach ($this->paths->getList() as $path) {
                    if ($path->getIdentifier() == $identifier) {
                        return $path;
                    }
                    
                }
                
            } else {
                $null = new Types\Null();
                return $null;
            }
            
        }
        
        /**
         * Does this path exist in the object's list?
         * 
         * @param string $path
         * 
         * @return boolean
         * 
         */
        public function isPath($path)
        {
            return (in_array($path, $this->getPaths())) ?
                $this->getIdentifier($path) : false;
        }
        
        /**
         * Does this path identifier exist in the object's list?
         * 
         * @param string|int $identifier The given identifier
         * 
         * @return boolean
         * 
         */
        public function isIdentifier($identifier)
        {
            foreach ($this->paths->getList() as $path) {
                if ($path->getIdentifier() == $identifier) {
                    return true;
                }
                
            }
            
            return false;
        }
        
        /**
         * Given a particular existing path or identifier, determines if its tied to a Module
         * 
         * This returns true if a given existing path is not tied to any Module.
         * 
         * @param string|int $pathOrIdentifier
         * 
         * @return boolean
         * 
         */
        public function isIndependent($pathOrIdentifier)
        {
            if ($this->isPath($pathOrIdentifier)) {
                $pathOrIdentifier = $this->getIdentifier($pathOrIdentifier);
            }
            
            if (!$this->isIdentifier($pathOrIdentifier)) {
                return false;
            }
            
            if (Loader\Module::pathBelongsTo($pathOrIdentifier, $this->libraries) ==
                    'libraries/') {
                return true;
            }
            
            return false;
        }
        
        /**
         * This returns all the paths (in path form, not identifiers)
         * that are not in a Module
         * 
         * @return array
         * 
         */
        public function getIndependentPaths()
        {
            $ret = array();
            
            foreach ($this->paths->getList() as $path) {
                if ($this->isIndependent($path->getIdentifier())) {
                    $ret[] = $path->getPath();
                }
                
            }

            return $ret;
        }
        
        /**
         * Gets all the independent paths, except returns the path objects
         * 
         * @return array (Falcraft\Data\Components\File\Path)
         * 
         */
        public function getIndependentPathObjects()
        {
            $ret = array();
            
            foreach ($this->paths->getList() as $path) {
                if ($this->isIndependent($path->getIdentifier())) {
                    $ret[] = $path;
                }
                
            }

            return $ret;
        }
        
        /**
         * Removes a path from the path pool, but only if it's not tied
         * to a Module
         * 
         * Takes care of prefixes as well.
         * 
         * @param string $path (or identifier)
         * 
         * @return boolean On success or failure
         * 
         */
        public function removePath($path)
        {
            $independentPaths = $this->getIndependentPaths();
            
            if ($path instanceof File\Path) {
                $path = $path->getPath();
            } else if ($this->isIdentifier($path)) {
                $path = $this->getPath($path);
            }
            
            if (!in_array($path, $independentPaths)) {
                return false;
            }
            
            $identifier = $this->getIdentifier($path);
            // unset($this->paths[$identifier]); -- ??? (10/20/2015)
            
            if (array_key_exists($identifier, $this->prefixes)) {
                unset($this->prefixes[$identifier]);
            }
            
            return true;
        }
        
        /**
         * Add a 'base' library Module to the root library Module
         * 
         * The library member variable keeps track of base leaf Modules known
         * as libraries
         * 
         * @param Falcraft\Data\Components\File\Resource\DirectoryInterface $module
         * 
         */
        public function addLibraryByModule(
            ModuleInterface $module
        ) {
            $this->libraries->addModule($module);
        }
        
        /**
         * Add a library (Module) with a given path, given identifier
         * 
         * Optionally you can specify the Module path, where it will attach
         * itself to the Module pointed to by the path.
         * 
         * Makes a new Module
         * 
         * @param string $path The given path (not Module path)
         * @param string $libraryName The Module identifier (required)
         * @param string $modulePath The path to the Module this library attaches to
         * 
         */
        public function addLibrary($path, $libraryName, $modulePath = '')
        {
            if ($this->isPath($path)) {
                $path = $this->getIdentifier($path);
            } else if (!$this->isIdentifier($path)) {
                $path = $this->addPath($path);
            }
            
            $newModule = new Loader\Module(
                $path,
                $libraryName,
                array(),
                array('strict' => $this->conf->strict)
            );
            
            if ($modulePath) {
                $module = Module::getFromModuleIdentityPath($this->libraries, $modulePath);
                $module->addModule($newModule);
            } else {
                $this->addLibraryByModule($newModule);
            }
            
        }
        
        /**
         * Is this library identifier registered with the autoloader?
         * 
         * Tricky here.  We can check keys because the leaf structure
         * uses the restricted list as an associative array.  When it
         * returns the list, the array comes with the keys.
         * 
         * @param string|int $library The library identifier to test
         * 
         * @return boolean
         * 
         */
        public function isLibrary( $library )
        {
            return array_key_exists($library, $this->libraries->getModules());
        }
        
        /**
         * Remove library by identifier
         * 
         * Removes the given identifying Module from the root library object
         * 
         * @param string|int $libraryName
         * 
         * @return boolean
         * 
         */
        public function removeLibrary( $libraryName )
        {
            $this->libraries->removeModule( $libraryName );
            return true;
        }
        
        /**
         * Retrieve a Module object from the library Module by identifier
         * 
         * @param string|int $libraryName The given library identifier
         * 
         * @return Falcraft\Data\Components\File\Resource\DirectoryInterface
         * 
         */
        public function getLibrary( $libraryName )
        {
            return $this->libraries->getModule($libraryName);
        }
        
        /**
         * This retrieves the Module objects from the library as an array
         * 
         * @return array (Falcraft\Data\Components\File\Resource\DirectoryInterface)
         * 
         */
        public function getLibrariesAsArray()
        {
            return $this->library->getModules();
        }
        
        /**
         * This retrieves the Modules as a giant array from the library
         * 
         * This recursively traces through the Modules, returning arrays
         * in arrays, path => the path, and Modules => the Modules which themselves
         * are returned as arrays.
         * 
         * @return array
         * 
         */
        public function getLibraryModulesAsArray()
        {
            return Loader\Module::getAsArray($this->libraries);
        }
        
        /**
         * Add a Module to the library tree
         * 
         * Constructs a Module from the identifier and path, and then
         * adds that Module to the library variable
         * 
         * @param string|int $identifier Identifier to use
         * @param string $path The actual path to associate, not a Module path
         * @param Falcraft\Data\Components\File\Resource\DirectoryInterface $library
         * 
         */
        public function addLibraryModule($identifier, $path, $library)
        {
            $module = new Loader\Module($path, $identifier);
            
            if ($library instanceof ModuleInterface) {
                $library->addModule($module);
            } else {
                $this->addLibraryByModule($module);
            }
            
        }
        
        /**
         * Takes a Module path that's allegedly in the library, and adds
         * the given Module there
         * 
         * @param string $modulePath The path to the Module (not the associated path)
         * @param Falcraft\Data\Components\File\Resource\DirectoryInterface $module The Module to add
         * 
         */
        public function addModule(
            $modulePath,
            ModuleInterface $module
        ) {
            $branch = Loader\Module::addToModuleIdentityPath(
                $this->libraries,
                $modulePath,
                $module
            );
        }

        /**
         * Check the library paths to see if a Module name exist
         * 
         * This does not guarantee if the actual Module object as its
         * identified exists, but if it's in a path returned by the 
         * static Module function.
         * 
         * @param string $module The Module name/identifier
         * @param string|Falcraft\Data\Components\File\Resource\DirectoryInterface The library to start at
         * 
         * @return boolean
         * 
         */
        public function isLibraryModule($module, $library = '')
        {
            if ($library instanceof ModuleInterface) {
                $haystack = Loader\Module::getModuleIdentityPaths($library);
            } else if (is_string($library)) {
                $haystack = Loader\Module::getFromModuleIdentityPath($library);
                if (!($haystack instanceof Types\Null)) {
                    $haystack = Loader\Module::getModuleIdentityPaths($haystack);
                } else {
                    return false;
                }
                
            } else {
                $haystack = Loader\Module::getModuleIdentityPaths($this->libraries);
            }
            
            if ($haystack instanceof Types\Null) {
                return false;
            }
            
            foreach ($haystack as $needle) {
                if (strpos($needle, $module) !== false) {
                    return true;
                }
                
            }
                
            return false;
        }
        
        /**
         * Tests if the given Module name/identifier is in the library paths
         * 
         * @param string $moduleMatch The name/identifier to match in the paths
         * 
         * @return boolean
         * 
         */
        public function isModule($moduleMatch)
        {
            $haystack = Loader\Module::getModuleIdentityPaths($this->libraries);
            
            foreach ($haystack as $needle) {
                if (strpos( $needle, $moduleMatch) !== false) {
                    return true;
                }
                
            }
                
            return false;
        }
        
        /**
         * Removes Module identifier ONLY if it's a terminating leaf
         * 
         * This removes a Module identifier (not path) only if the Module
         * is the last Module in a given path from the root leaf
         * 
         * @param string $module The Module identifier
         * @param string|Falcraft\Data\Components\File\Resource\DirectoryInterface
         *              $library The library to start at, defaults to internal library
         * 
         * @return boolean True on succcess
         * 
         */
        public function removeLibraryModule($module, $library = '')
        {
            if ($library instanceof ModuleInterface) {
                $haystack = Loader\Module::getModuleIdentityPaths($library);
            } else if (is_string( $library)) {
                $library = $haystack = Loader\Module::getFromModuleIdentityPath($library);
                if (!($haystack instanceof Types\Null)) {
                    $haystack = Loader\Module::getModuleIdentityPaths($haystack);
                } else {
                    return false;
                }
                
            } else {
                $haystack = Loader\Module::getModuleIdentityPaths($this->libraries);
                $library = $this->libraries;
            }
            
            if ($haystack instanceof Types\Null) {
                return false;
            }
            
            $path = '';
            foreach ($haystack as $needle) {
                $needleParts = explode('/', $needle);
                if ($needleParts[count($needleParts)-1] == $module) {
                    $path = $needle;
                }
            }
            
            if ($path) {
                $module = Loader\Module::getFromModuleIdentityPath($library, $path);
                
                $path = explode('/', $path);
                $path = array_pop($path);
                
                $module->removeModule($path);            
                return true;
            }
            
            return false;
        }
        
        /**
         * Removes a Module from the internal libraries using a Module path
         * 
         * @param string $modulePath The Module path starting at the base library
         * 
         */
        public function removeModule($modulePath)
        {
            $path = explode('/', $modulePath);
            $moduleName = array_pop($path);
            $path = implode('/', $path);
            
            $module = Loader\Module::getFromModuleIdentityPath($this->libraries, $path);
            $module->removeModule($moduleName);
        }
        
        /**
         * Add a prefix to a particular path
         * 
         * The path can be an identifier or a path, preferably an identifier
         * 
         * The autoloader keeps track of its own prefixes as attached to paths
         * 
         * This adds path is path doesn't exist.
         * 
         * @param string $prefix (include any characters like an underscore: WP_)
         * @param string|int The path or its identifier
         * 
         * @return boolean Pretty much true
         * 
         */
        public function addPrefix($prefix, $path)
        {
            if ($this->isPath($path)) {
                $path = $this->getIdentifier($path);
            } else if (!$this->isIdentifier($path)) {
                $path = $this->addPath($path);
            }
            
            if (!array_key_exists($path, $this->prefixes)) {
                $this->prefixes[$path] = array();
            }
            
            if (!in_array($prefix, $this->prefixes[$path])) {
                $this->prefixes[$path][] = $prefix;
            }
            
            return true;
        }
        
        /**
         * Does this path have prefixes?
         * 
         * (Any prefix)
         * 
         * Does not add path to list
         * 
         * @param string|int The given path, or identifier
         * 
         * @return boolean true on success, false on failure
         */
        public function hasPrefix($path)
        {
            if ($this->isPath($path)) {
                $path = $this->getIdentifier($path);
            } else if (!$this->isIdentifier($path)) {
                return false;
            }
            
            if (array_key_exists($path, $this->prefixes)) {
                return true;
            }
            
            return false;
        }
        
        /**
         * This tests if there is a prefix associated with a path
         * 
         * If there is no path given, it tests if a prefix exists
         * with any path in the autoloader
         * 
         * @param string $prefix The prefix we're looking for
         * @param string|ind $path The identifier or path we are looking for
         * 
         * @return boolean true on success, false on failure
         * 
         */
        public function isPrefix($prefix, $path = '')
        {    
            if ($path) {
                if ($this->isPath($path)) {
                    $path = $this->getIdentifier($path);
                } else if (!$this->isIdentifier($path)) {
                    return false;
                }
                
                return (array_key_exists($path, $this->prefixes) &&
                            in_array($prefix, $this->prefixes[$path])) ?
                        true : false;
            }
            
            for ($a = 0; $a < count($this->prefixes); $a++) {
                if (in_array($prefix, $this->prefixes[$a])) {
                    return true;
                }
                
            }

            return false;
        }
        
        /**
         * Retrieve all prefixes associated with a path
         * 
         * The path can be an existing path, or an identifier
         * 
         * Preferably an identifier
         * 
         * @param string|id $path The path or identifier
         * 
         * @return array
         * 
         */
        public function getPrefixes($path)
        {
            if ($this->isPath($path)) {
                $path = $this->getIdentifier($path);
            } else if (!$this->isIdentifier($path)) {
                return array();
            }
            
            if (array_key_exists($path, $this->prefixes)) {
                return $this->prefixes[$path];
            }
            
            return array();
        }
        
        /**
         * Remove a prefix from a path
         * 
         * @param string|int $path The path to remove the prefix from
         * @param string $prefix The given prefix
         * 
         * @return boolean true on success, or false on failure
         * 
         */
        public function removePrefix($path, $prefix)
        {
            if ( $this->isPath($path)) {
                $path = $this->getIdentifier($path);
            }
            
            $key = null;
            if (array_key_exists($path, $this->prefixes)) {
                foreach ($this->prefixes[$path] as $k => $v) {
                    if ($v == $prefix) {
                        $key = $k;
                    }
                    
                }
                
            }
            
            if (!is_null($key)) {
                unset($this->prefixes[$path][$key]);
                return true;
            }
            
            return false;
        }
    }
}