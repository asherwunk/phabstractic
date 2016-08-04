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
        
    $includes = array(// we are configurable
                      '/Features/ConfigurationTrait.php',
                      '/Features/Resource/ConfigurationInterface.php',
                      // we implement loaderinterface
                      '/Loader/Resource/LoaderInterface.php',
                      // we use paths
                      '/Data/Components/Path.php',
                      '/Data/Components/Resource/PathInterface.php',
                      // we normalize paths
                      '/Resource/FileUtilities.php',
                      // we use modules
                      '/Loader/Module.php',
                      // get leaf identity path returns none
                      '/Data/Types/None.php',
                      // we throw these exceptions
                      '/Loader/Exception/DomainException.php',
                      '/Loader/Exception/InvalidArgumentException.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Features\Resource as FeaturesResource;
    use Phabstractic\Features;
    use Phabstractic\Data\Components\Resource as ComponentsResource;
    use Phabstractic\Data\Components;
    use Phabstractic\Loader\Exception as LoaderException;
    use Phabstractic\Loader\Resource as LoaderResource;
    use Phabstractic\Loader;
    use Phabstractic\Resource as PhabstracticResource;
    use Phabstractic\Data\Types;
    
    /**
     * The Abstract Autoloader
     * 
     * This piece of machinery keeps track of paths, prefixes, libraries and Modules
     * 
     * @see Phabstractic\Loader\Resource\LoaderInterface
     * 
     * First paths are stored in an array of Path objects
     * 
     * Libraries/Modules are stored as a root to a tree of Modules.
     * 
     * Every Module/Library has a path associated with it.
     * 
     * Prefixes are stored in an array with path as the keys
     * and the prefixes associated with those identifiers as array values.
     * 
     * CHANGELOG
     * 
     * 1.0 Created AbstractLoader - January 31st, 2014
     * 2.0 Refactored for integration with Primus 2 - October 20th, 2015
     * 3.0: eliminated restrictedlist for paths
     *      eliminated path identifiers
     *      eliminated default path
     *      reformatted for inclusion in phabstractic - August 3rd, 2016
     * 
     * @abstract
     * 
     * @version 3.0
     * 
     */
    abstract class AbstractLoader implements
        LoaderResource\LoaderInterface,
        FeaturesResource\ConfigurationInterface
    {
        use Features\ConfigurationTrait;
        
        /**
         * The list of path objects
         * 
         * Keys are identifiers, values are path objects
         * 
         * @var array
         * 
         */
        protected $paths = array();
        
        /**
         * A multi-dimensional array of prefixes tied to paths
         * 
         * Keys are path identifiers, values are array of prefixes
         * 
         * @var array
         * 
         */
        protected $prefixes = array();
        
        
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
        abstract public function autoload($class);
        
        /**
         * Abstract Loader Constructor
         * 
         * This takes in an array of paths (NOT identifiers, actual paths) and
         * an array of options.  It sets up the paths into appropriate objects
         * and sets up the library root Module.
         * 
         * OPTIONS - strict: do we throw errors?
         *           auto_register: Register the load in the SPL (false default)
         *           file_extension: What file extension to use as a base for paths
         * 
         * @param array $paths The actual PATHS to put in the autoloader
         * @param array $options See description
         * 
         */
        public function __construct($paths = array(), $options = array())
        {
            $options = array_change_key_case($options);
            
            if (!isset($options['auto_register'])) {
                $options['auto_register'] = false;
            }
            
            if (!isset($options['file_extension'])) {
                $options['file_extension'] = 'php';
            }
            
            $this->configure($options);
            
            foreach ($paths as $key => $path) {
                if (is_string($path)) {
                    $paths[$key] = new Components\Path(
                        $path,
                        array(),
                        array('strict' => $this->conf->strict)
                    );
                } elseif (!($path instanceof ComponentsResource\PathInterface)) {
                    if ($this->conf->strict) {
                        throw new LoaderException\InvalidArgumentException(
                            'Phabstractic\\Loader\\Resource\\AbstractLoader->__construct: ' .
                            'Path is illegal value');
                    }
                    
                }
            }
            
            foreach ($paths as $path) {
                if ($path instanceof Components\Path) {
                    $path->addExtension($this->conf->file_extension);
                    $this->addPath($path);
                }
            }
            
            $this->libraries = new Loader\Module(
                '/',
                'namespaces',
                array(),
                array('strict' => $this->conf->strict)
            );
            
            if ($this->conf->auto_register ) {
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
         * Return all the paths in an array
         * 
         * actual paths, string of arrays
         * 
         * @return array
         * 
         */
        public function getPaths()
        {
            $ret = array();
            foreach ($this->paths as $path) {
                $ret[] = $path->getPath();
            }
            
            return $ret;
        }
        
        /**
         * Add a path to the path pool
         * 
         * @param string|Phabstractic\Data\Components\Resource\PathInterface
         *              $path The actual path to add
         * @param bool false on failure
         * 
         */
        public function addPath($newPath)
        {
            if ($newPath instanceof ComponentsResource\PathInterface) {
                foreach ($this->paths as $path) {
                    if ($path->getPath() == $newPath->getPath()) {
                        if ($this->conf->strict) {
                            throw new LoaderException\DomainException(
                                'Phabstractic\\Loader\\Resource\\AbstractLoader->addPath: ' .
                                'Path already exists');
                        }
                        
                        return false;
                    }
                }
                
                $newPath->addExtension($this->conf->file_extension);
                $this->paths[] = $newPath;
            } else {
                foreach ($this->paths as $path) {
                    if ($path->getPath() == $newPath) {
                        if ($this->conf->strict) {
                            throw new LoaderException\DomainException(
                                'Phabstractic\\Loader\\Resource\\AbstractLoader->addPath: ' .
                                'Path already exists');
                        }
                        
                        return false;
                    }
                }
                
                $this->paths[] = new Components\Path(
                    $newPath,
                    array($this->conf->file_extension),
                    array('strict' => $this->conf->strict)
                );
            }
            
            return true;
        }
        
        /**
         * Does this path exist in the object's list?
         * 
         * @param string $path
         * 
         * @return bool
         * 
         */
        public function isPath($path)
        {
            if ($path instanceof ComponentsResource\PathInterface) {
                return in_array($path, $this->paths);
            } else {
                return in_array(
                    PhabstracticResource\FileUtilities::getAbsolutePath($path),
                    $this->getPaths()
                );
            }
        }
        
        /**
         * Removes a path from the path pool, but only if it's not tied
         * to a Module
         * 
         * @param string $path
         * 
         * @return bool On success or failure
         * 
         */
        public function removePath($path)
        {
            if ($path instanceof ComponentsResource\PathInterface) {
                if ($index = array_search($path, $this->paths)) {
                    array_splice($this->paths, $index, 1);
                    return true;
                }
            } else {
                $path = PhabstracticResource\FileUtilities::getAbsolutePath($path);
                
                if (in_array($path, array_keys($this->prefixes))) {
                    unset($this->prefixes[$path]);
                }
                
                $found = null;
                foreach ($this->paths as $key => $value) {
                    if ($value->getPath() == $path) {
                        array_splice($this->paths, $key, 1);
                        break;
                    }
                }
            }
        }
        
        /**
         * Retrieve a path object by path
         * 
         * @param string $searchPath The path we're looking for
         * 
         * @return Phabstractic\Data\Components\Path
         * 
         */
        public function getPathObject($searchPath)
        {
            foreach ($this->paths as $path) {
                if ($path->getPath() == $searchPath) {
                    return $path;
                }
            }
            
            return null;
        }
        
        /**
         * Retrieve a reference to path object by path
         * 
         * @param string $searchPath The path we're looking for
         * 
         * @return Phabstractic\Data\Components\Path
         * 
         */
        public function &getPathObjectReference($searchPath)
        {
            foreach ($this->paths as $key => $path) {
                if ($path->getPath() == $searchPath) {
                    return $this->paths[$key];
                }
            }
            
            return null;
        }
        
        /**
         * Add a prefix to a particular path
         * 
         * The autoloader keeps track of its own prefixes as attached to paths
         * 
         * @param string $path The path
         * @param string $prefix (include any characters like an underscore: WP_)
         * 
         * @return boolean Pretty much true
         * 
         */
        public function addPrefix($path, $prefix)
        {
            if ($path instanceof ComponentsResource\PathInterface) {
                $path = $path->getPath();
            } else {
                $path = PhabstracticResource\FileUtilities::getAbsolutePath($path);
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
         * @param string The given path
         * 
         * @return boolean true on success, false on failure
         */
        public function hasPrefix($path)
        {
            if ($path instanceof ComponentsResource\PathInterface) {
                $path = $path->getPath();
            } else {
                $path = PhabstracticResource\FileUtilities::getAbsolutePath($path);
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
        public function isPrefix($prefix)
        {
            $paths = array();
            
            foreach ($this->prefixes as $key => $value) {
                if (in_array($prefix, $value)) {
                    $paths[] = $key;
                }
            }
            
            return $paths;
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
            if ($path instanceof ComponentsResource\PathInterface) {
                $path = $path->getPath();
            } else {
                $path = PhabstracticResource\FileUtilities::getAbsolutePath($path);
            }
            
            if (isset($this->prefixes[$path])) {
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
            if ($path instanceof ComponentsResource\PathInterface) {
                $path = $path->getPath();
            } else {
                $path = PhabstracticResource\FileUtilities::getAbsolutePath($path);
            }
            
            if (array_key_exists($path, $this->prefixes)) {
                foreach ($this->prefixes[$path] as $k => $v) {
                    if ($v == $prefix) {
                        array_splice($this->prefixes[$path], $k, 1);
                        break;
                    }
                    
                }
                
            }
            
            return false;
        }
        
        /**
         * Retrieve the endpoints of all namespaces
         * 
         * @return array
         * 
         */
        public function getNamespaces()
        {
            $namespaces = $this->libraries->getModuleIdentityPaths();
            $ret = array();
            foreach ($namespaces as $namespace) {
                $parts = explode('\\', $namespace);
                array_shift($parts);
                $ret[] = implode('\\', $parts);
            }
            
            return $ret;
        }
        
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
        public function addNamespace($namespace, $path)
        {
            if ($path instanceof ComponentsResource\PathInterface) {
                $path->addExtension($this->conf->file_extension);
            } else {
                $path = new Components\Path(
                    $path,
                    array($this->conf->file_extension),
                    array('strict' => $this->conf->strict)
                );
            }
            
            $namespace = 'namespaces\\' . $namespace;
            $parts = explode('\\', $namespace);
            
            $currentModule = 
                    &$this->libraries->getFromModuleIdentityPath($namespace);
            
            if (!($currentModule instanceof Types\None)) {
                $currentModule->setPath($path);
                return;
            }
            
            $currentModule = &$this->libraries;
            
            array_shift($parts);
            
            foreach ($parts as $part) {
                if ($currentModule->isSubModuleByIdentifier($part)) {
                    $currentModule = 
                        &$currentModule->getModuleByIdentifierReference($part);
                } else {
                    $module = new Loader\Module(
                        '',
                        $part,
                        array(),
                        array('strict' => $this->conf->strict)
                    );
                    
                    $currentModule->addModule($module);
                    $currentModule = 
                        &$currentModule->getModuleByIdentifierReference($part);
                }
            }
            
            $currentModule->setPath($path);
            
            return true;
        }
        
        /**
         * Is this namespace registered with this autoloader?
         * 
         * @param string $namespace The namespace of the library
         * 
         * @return boolean True if present
         * 
         */
        public function isNamespace($namespace)
        {
            $namespace = 'namespaces\\' . $namespace;
            
            $currentModule = 
                    &$this->libraries->getFromModuleIdentityPath($namespace);
            
            if (!($currentModule instanceof Types\None)) {
                return true;
            }
            
            return false;
        }
        
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
        public function removeNamespace($namespace)
        {
            if (!$namespace) {
                return false;
            }
            
            $namespace = 'namespaces\\' . $namespace;
            $parts = explode('\\', $namespace);
            $moduleIdentifier = array_pop($parts);
            $parentNamespace = implode('\\', $parts);
            
            $parentModule =
                    &$this->libraries->getFromModuleIdentityPath($parentNamespace);
            
            if (!($parentModule instanceof Types\None)) {
                return $parentModule->removeModuleByIdentifier($moduleIdentifier);
            }
            
            return false;
        }
        
        /**
         * Retrieve path associated with namespace
         * 
         * @param string $namespace The library namespace
         * 
         * @return string
         * 
         */
        public function getNamespacePath($namespace)
        {
            $namespace = 'namespaces\\' . $namespace;
            
            $currentModule = 
                    &$this->libraries->getFromModuleIdentityPath($namespace);
            
            if (!($currentModule instanceof Types\None)) {
                return $currentModule->getPath()->getPath();
            }
            
            return null;
        }
        
        /**
         * Retrieve the module reference associated with namespace
         * 
         * Pre-pend the namespace with a '\\'!
         * 
         * @param string $namespcae The library namespace
         * 
         * @return &Phabstractic\Loader\Resource\ModuleInterface
         * 
         */
        public function &getNamespaceModule($namespace)
        {
            $namespace = 'namespaces' . $namespace;
            
            $currentModule = 
                    &$this->libraries->getFromModuleIdentityPath($namespace);
            
            if (!($currentModule instanceof Types\None)) {
                return $currentModule;
            }
            
            return null;
        }
        
        /**
         * Retrieve the modules as an array
         * 
         * @return array
         *
         */
        public function getNamespaceModulesAsArray()
        {
            return $this->libraries->getModulesAsArray();
        }
    }
    
}