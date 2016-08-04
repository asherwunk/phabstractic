<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * File Path Interface
 * 
 * This implements a basic path data structure.  Each path has an identifier 
 * that is unique to that path, a path, and a set of file extensions that are 
 * searched through for that path.  The first matching extension returns path 
 * of the file
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
namespace Phabstractic\Data\Components\Resource
{
    /**
     * Path Interface - Defines A Path Structure
     * 
     * A path consists of a directory, a file, and a file extension. This class 
     * keeps track of the directory path, and the assigned or acceptable 
     * extensions allowed by the path. It offers a function to test if a file 
     * is found in the path given the allowed extensions
     * 
     * CHANGELOG
     * 
     * 1.0: Created PathInterface - August 3rd, 2016
     * 
     * @version 1.0
     * 
     */
    interface PathInterface
    {
        /**
         * Get this particular path's identifier
         * 
         * @return string The universally unique vertex identifier
         * 
         */
        public function getIdentifier();
        
        /**
         * Is this path relative?
         * 
         * @return boolean
         * 
         */
        public function isRelative();

        /**
         * Retrieve the current path
         * 
         * Path can be relative or absolute
         * 
         * @return string The path in the instance
         * 
         */
        public function getPath();
        
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
        public function setPath($path);
        
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
        public function addExtension($extensions);
        
        /**
         * Retrieve the list of allowed extensions
         * 
         * @return array A simple array of allowed extensions
         * 
         */
        public function getExtensions();
        
        /**
         * Remove a particular extension from the set of allowed extensions
         * 
         * @return mixed Whatever the remove method in Types\Set returns
         */
        public function removeExtension($extension);
        
        /**
         * Is an extension allowed?
         * 
         * @return boolean
         * 
         */
        public function isExtension($extension);
        
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
        );
        
    }
    
}
