<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * File Path Data Structure
 * 
 * This implements a basic path data structure.  Each path has an identifier 
 * that is unique to that path, a path, and a set of file extensions that are 
 * searched through for that path.  The first matching extension returns path 
 * of the file
 * 
 * The identifier for each path is either supplied by the user or generated on 
 * the fly, much like the tree/leaf identifier system
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Components
 * @subpackage Standard
 * 
 */

/**
 * Falcraft Libraries Data Components Namespace
 * 
 */
namespace Phabstractic\Data\Components
{
    require_once(realpath( __DIR__ . '/../../') . '/falcraftLoad.php');
    
    $includes = array(// we are configurable
                      '/Features/ConfigurationTrait.php',
                      '/Features/Resource/ConfigurationInterface.php',
                      // we have identities
                      '/Features/IdentityTrait.php',
                      // we type check against string for extensions
                      '/Data/Types/Type.php',
                      '/Data/Types/Restrictions.php',
                      // we throw domain exception on invalid path
                      '/Data/Components/Exception/InvalidArgumentException.php',
                      '/Data/Components/Exception/DomainException.php',);
        
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Features\Resource as FeaturesResource;
    use Phabstractic\Features;
    use Phabstractic\Data\Components\Exception as ComponentsException;
    use Phabstractic\Data\Types;
    use Phabstractic\Data\Types\Type;
    
    /**
     * Path Class - Defines A Path Structure
     * 
     * A path consists of a directory, a file, and a file extension. This class 
     * keeps track of the directory path, and the assigned or acceptable 
     * extensions allowed by the path. It offers a function to test if a file 
     * is found in the path given the allowed extensions
     * 
     * CHANGELOG
     * 
     * 1.0: Created Path - January 30th, 2014
     * 2.0: Integrated Path for Primus 2 - October 20th, 2015
     * 3.0: removed restrictedset for extensions
     *      removed identity methods
     *      removed automatic php extension addition
     *      reformatted for inclusion in phabstractic - August 1st, 2016
     * 
     * @version 3.0
     * 
     */
    class Path implements FeaturesResource\ConfigurationInterface
    {
        use Features\ConfigurationTrait;
        use Features\IdentityTrait;
        
        /**
         * The path (not file path)
         * 
         * The specification of a directory (not a file)
         * 
         * Note: Trailing slashes will be removed
         * 
         * @var string
         * 
         */
        private $path = '';
        
        /**
         * Is this path relative?
         * 
         * @var boolean
         * 
         */
        private $relative = false;
        
        /**
         * The path identifier
         * 
         * Specific to the particular path, unless otherwise changed in the 
         * rest of the program
         * 
         * It is possible that two paths have the same identifier
         * 
         * @var string
         * 
         */
        private $identifier = null;
        
        /**
         * Set of allowed extensions
         * 
         * When calculating the existence of a filename it checks through all 
         * the available extensions, returning the first arbitrary extension
         * 
         * @var array
         * 
         */
        private $extensions = array();
        
        /**
         * Get this particular path's identifier
         * 
         * @return string The universally unique vertex identifier
         * 
         */
        public function getIdentifier()
        {
            if ( !$this->identifier )
                $this->identifier = $this->getNewIdentity();
            return $this->identifier;
        }
        
        /**
         * Path Construction
         * 
         * This constructs the path from the required variable. If an identifier
         * is not provided a unique identifier is assigned to the path.
         * 
         * Paths have trailing slashes '/' removed, extensions have the FIRST 
         * period removed from them, so '.some.extension' becomes
         * 'some.extension'.
         * 
         * Options: strict - should we throw errors?
         *          check - Check to make sure a path (directory) exists
         *          suppress_warnings - Suppress error warnings on file_exists
         *          identity_prefix - the prefix to use when creating this link
         * 
         * @param string The path relative or absolute
         * @param string The identifier for the path
         * @param array The appropriate extensions for the path
         *              (php added by default)
         * @param array The options as supported by features\configuration
         * 
         */
        public function __construct(
            $path = '',
            array $extensions = array(),
            array $options = array()
        ) {
            $options = array_change_key_case($options);
            
            if (!isset($options['check'])) {
                $options['check'] = false;
            }
            
            if (!isset($options['suppress_warnings'])) {
                $options['suppress_warnings'] = true;
            }
            
            $this->configure($options);
            
            if (!$this->conf->identity_prefix) {
                $this->identityPrefix = 'Path';
            }
            
            $this->setPath($path);
            
            if (Types\Restrictions::checkRestrictedValues(
                $extensions,
                new Types\Restrictions(array(Type::BASIC_STRING)),
                $this->conf->strict
            )) {
                $this->extensions = $extensions;
            } else {
                if ($this->conf->strict) {
                    throw new ComponentsException\InvalidArgumentException(
                        'Phabstractic\\Data\\Components\\Path->__construct: ' .
                        'Extension improper data type');
                }
            }
            
            for ($a = 0; $a < count($extensions); $a++) {
                if ($extensions[$a][0] == '.') {
                    $extensions[$a] = substr($extension[$a], 1);
                }
            }
            
        }

        /**
         * Is this path relative?
         * 
         * @return boolean
         * 
         */
        public function isRelative()
        {
            return $this->relative;
        }

        /**
         * Retrieve the current path
         * 
         * Path can be relative or absolute
         * 
         * @return string The path in the instance
         * 
         */
        public function getPath()
        {
            return $this->path;
        }
        
        /**
         * Assign the path
         * 
         * NOTE: This should be a directory, NOT a file
         * 
         * If option Check has been specified we see if the directory actually
         * exists.
         * 
         * @param string The path to be assigned
         * 
         * @return Phabstractic\Data\Components\Path $this for chaining
         * 
         * @throws Phabstractic\Data\Components\Exception\DomainException 
         *         Path does not exist (only thrown if Check option is set)
         * 
         */
        public function setPath($path)
        {
            if (!$path) {
                $this->path = '';
                $this->relative = true;
            } else {
                $this->relative = ($path[0] != DIRECTORY_SEPARATOR) ?
                    true : false;
            }
            
            if (substr($path, -1) != DIRECTORY_SEPARATOR) {
                $path = rtrim($path, DIRECTORY_SEPARATOR);
            }
            
            if ($this->conf->check && !$this->relative) {
                if (!is_dir($path)) {
                    throw new ComponentsException\DomainException(
                        'Phabstractic\\Data\\Components\\Path->setPath(): ' .
                        'Path does not exist in file system');
                }
            }
            
            $this->path = $path;
            
            return $this;
        }
        
        
        /**
         * Add an allowed extension
         * 
         * This puts a extension or array of extensions into the list of
         * allowed and looked for extensions when a filename is supplied to
         * the path.
         * 
         * NOTE:  Extensions preceding periods (the first only) are removed.
         *        '.some.extension' becomes 'some.extension'
         * 
         * @param string|array The extensions to add
         * 
         * @return Phabstractic\Data\Components\Path $this for chaining
         * 
         */
        public function addExtension($extensions)
        {
            if (!is_array($extensions)) {
                $extensions = array($extensions);
            }
            
            if (!Types\Restrictions::checkRestrictedValues(
                $extensions,
                new Types\Restrictions(array(Type::BASIC_STRING)),
                $this->conf->strict)
            ) {
                if ($this->conf->strict) {
                    throw new ComponentsException\InvalidArgumentException(
                        'Phabstractic\\Data\\Components\\Path->addExtension: ' .
                        'Extension improper data type');
                } else {
                    return;
                }
            }
            
            foreach ($extensions as $extension) {
                if ($extension[0] == '.') {
                      $extension = substr($extension, 1);
                }
                
                $this->extensions[] = $extension;
            }
            
            $this->extensions = array_unique($this->extensions);
            
            return $this;
        }
        
        /**
         * Retrieve the list of allowed extensions
         * 
         * @return array A simple array of allowed extensions
         * 
         */
        public function getExtensions()
        {
            return $this->extensions;
        }
        
        /**
         * Remove a particular extension from the set of allowed extensions
         * 
         * @return mixed Whatever the remove method in Types\Set returns
         */
        public function removeExtension($extension)
        {
            if ($key = array_search($extension, $this->extensions)) {
                array_splice($this->extensions, $key, 1);
            }
        }
        
        /**
         * Is an extension allowed?
         * 
         * @return boolean
         * 
         */
        public function isExtension($extension)
        {
            return in_array($extension, $this->extensions);
        }
        
        /**
         * Find a particular filename in the path
         * 
         * This method returns a filename (or '' on failure) when a given
         * filename is found in the path with an acceptable extension.
         * 
         * NOTE: Do not pass the required extension in $filename
         *       Use $reqExtension instead.
         * 
         * @param string The filename (without path or extension) to check
         * @param string Base path for relative path
         * @param string Any particular extension we're looking for?
         * 
         * @return string|null The full path with extension, or null on failure
         * 
         * @throws Phabstractic\Data\Components\Exception\DomainException
         * 
         */
        public function isFilename(
            $filename,
            $basePath = '',
            $reqExtension = ''
        ) {
            $extensions = $this->extensions;

            if ($filename[0] == DIRECTORY_SEPARATOR) {
                $filename = ltrim($filename, DIRECTORY_SEPARATOR);
            }
            
            if ( $this->relative && !$basePath ) {
                throw new ComponentsException\DomainException(
                    'Phabstractic\\Data\\Components\\Path->isFilename(): ' .
                    'Relative Path MUST have basePath in filecheck');
            } else if ($this->relative) {
                $path = $basePath . DIRECTORY_SEPARATOR . $this->path;
            } else {
                $path = $this->path;
            }
            
            $path = substr($path, -1) != DIRECTORY_SEPARATOR ?
                $path . DIRECTORY_SEPARATOR : $path;
            
            $path = realpath($path);
            
            if ($reqExtension) {
                if (($this->conf->suppresswarnings &&
                        @file_exists(
                            $path . DIRECTORY_SEPARATOR .
                                $filename . '.' . $reqExtension
                        )) ||
                        (file_exists(
                            $path . DIRECTORY_SEPARATOR .
                                $filename . '.' . $reqExtension)
                        )
                ) {
                    return $path . DIRECTORY_SEPARATOR .
                        $filename . '.' . $reqExtension;
                }
                
                return null;
            }
            
            foreach($extensions as $extension) {
                if (($this->conf->suppresswarnings &&
                        @file_exists(
                            $path . DIRECTORY_SEPARATOR .
                            $filename . '.' . $extension
                        )) ||
                        (file_exists(
                            $path . DIRECTORY_SEPARATOR .
                                $filename . '.' . $extension)
                        )
                ) {
                    return $path . DIRECTORY_SEPARATOR .
                        $filename . '.' . $extension;
                }
                
            }
            
            return null;
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
                                   'check' => $this->conf->check,
                                   'suppress_warnings' => $this->conf->suppress_warnings,
                                   'identity_prefix' => $this->conf->identity_prefix,),
                'path' => $this->path,
                'extensions' => $this->extensions,
                'relative' => $this->relative,
                'identityPrefix' => $this->identityPrefix,
            ];
        }
        
    }
    
}
