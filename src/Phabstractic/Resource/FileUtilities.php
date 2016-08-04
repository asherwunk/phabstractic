<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Static Utility Functions: File Functions
 * 
 * This houses functions that have to do with paths and the filesystem.
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Utilities
 * @subpackage File
 * 
 */

/**
 * Falcraft Resource/Utilities Namespace
 * 
 */
namespace Phabstractic\Resource
{
    /**
     * The Static File Utility Class
     * 
     * This class contains all the static file/path-based utility functions
     * that are used in particular cases throughout the library.
     *
     * CHANGELOG
     * 
     * 1.0: created FileUtilities - August 3rd, 2016
     * 
     * @version 1.0
     * 
     */
    class FileUtilities
    {
        
        /**
         * Get Absolute File Path
         * 
         * This normalizes a file path to eliminate ..'s
         * 
         * @link http://php.net/manual/en/function.realpath.php#84012
         * 
         * @param string $path the path to normalize
         * 
         * @return string the normalized path
         * 
         */
        public static function getAbsolutePath($path) {
            $before = false;
            $after = false;
            if ($path[0] == DIRECTORY_SEPARATOR) {
                $before = true;
            }
            
            if ($path[strlen($path)-1] == DIRECTORY_SEPARATOR) {
                $after = true;
            }
            
            $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
            $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
            $absolutes = array();
            foreach ($parts as $part) {
                if ('.' == $part) continue;
                if ('..' == $part) {
                    if (($pop = array_pop($absolutes)) == '..' || $pop == null) {
                        array_push($absolutes, '..');
                        if ($pop != null) {
                            array_push($absolutes, '..');
                        }
                    };
                } else {
                    $absolutes[] = $part;
                }
            }
            
            $path = implode(DIRECTORY_SEPARATOR, $absolutes);
            if ($before) {
                $path = DIRECTORY_SEPARATOR . $path;
            }
            
            if ($after) {
                $path = $path . DIRECTORY_SEPARATOR;
            }
            
            return $path;
        }
    }
    
}
