<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * The Singleton Interface
 * 
 * This file contains the singleton interface.  All singletons that follow
 * the included library pattern will define these functions.
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Patterns
 * @subpackage Interfaces
 * 
 */

/**
 * Falcraft Libraries Pattern Implementations Resource Namespace
 * 
 */
namespace Phabstractic\Patterns\Resource
{
    
    /**
     * Singleton Interface
     * 
     * This provides more a way to identify a singleton
     * than to offer any increased functionality.
     * 
     * CHANGELOG
     * 
     * 1.0 created Singleton Interface - May 27th, 2013
     * 2.0 refactored for Primus library - April 3rd, 2015
     * 3.0 formatted for inclusion in the final phabstractic library - July 7th, 2016
     * 
     * @version 3.0
     * 
     */
    interface SingletonInterface
    {
        /**
         * Tests If Singleton Is Instantiated
         * 
         * @return bool Instantiated?
         * 
         */
        public static function hardened();
        
        /**
         * (Creates) Returns The Instance Of The Singleton
         * 
         * @return mixed The singleton itself
         * 
         */
        public static function instantiate();
        
    }
}
