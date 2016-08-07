<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */
   
/**
 * This file contains the builder interface
 * 
 * A builder gives us a getBuilderResult method that returns the final
 * product.  Every builder has this 'final' method.
 * 
 * @link https://en.wikipedia.org/wiki/Builder_pattern [english]
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Patterns
 * @subpackage Composition
 * 
 */

/**
 * Falcraft Libraries Pattern Implementations Namespace
 * 
 */
namespace Phabstractic\Patterns\Resource
{
    /**
     * Builder Interface - Identifies Many Different Objects As Builders
     * 
     * Any class that implements this interface can be 'tagged' as a builder
     * enabled pattern.  Every builder gives us a getBuilderResult method that
     * returns the final product of the build.
     * 
     * CHANGELOG
     * 
     * 1.0: created builderinterface - August 5th, 2016
     * 
     * @version 1.0
     * 
     */
    interface BuilderInterface
    {
        
        /**
         * Return final result of composition
         * 
         * @return mixed
         * 
         */
        public function getBuiltObject();
        
    }
    
}
