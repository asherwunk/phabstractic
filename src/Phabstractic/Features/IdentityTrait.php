<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * An Identity Feature
 * 
 * With the identity feature an object can retrieve unique identifiers for their
 * datums regardless of instantiation (using a static class property).
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Features
 * @subpackage Identity
 * 
 */
 
/**
 * Falcraft Libraries Features Namespace
 * 
 */
namespace Phabstractic\Features
{
    
    /**
     * The Identity trait
     * 
     * Encapsulates all the logic necessary for delivering unique identifiers
     * specific to a class.  Use this to get unique identifiers for datums that
     * are created in a particular class globally.
     * 
     * CHANGELOG
     * 
     * 1.0: Created and documented - April 10th, 2015
     * 2.0: Adapted for Primus2, change counter access - September 5th, 2015
     * 3.0: reformatted for inclusion in phabstractic - July 9th, 2016
     * 
     * @version 3.0
     * 
     */
    trait IdentityTrait
    {
        /**
         * The Global Identity Generator Marker
         * 
         * When the identity is polled, this marker is 'advanced'.  It's static
         * to the class, in that way it's unique for each one.
         * 
         * @static
         * 
         * @var mixed $identityCounter The current identity
         * 
         */
        protected static $identityCounter = 0;
        
        /**
         * The Identity Marker Prefix
         * 
         * Specific to a given class, this is placed with/in front of the
         * identity (context) This is a bit strange to understand why it's not
         * static, but imagine a finer grain of control
         * 
         * @var mixed $identityPrefix The class's context/marker prefix
         * 
         */
        protected $identityPrefix = '';
        
        /**
         * Poll for New Identity
         * 
         * Overridable in a using class so that anything could be used as
         * an identity, but by default it's an integer attached to a prefix.
         * As new identities are polled, the integer increases.
         * 
         * @return mixed The new identity
         * 
         */
        protected function getNewIdentity()
        {
            static::$identityCounter++;
            return $this->identityPrefix . static::$identityCounter;
        }
        
    }
    
}
