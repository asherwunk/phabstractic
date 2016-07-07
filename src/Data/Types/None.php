<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Object-Oriented Null (To Replace null)
 * 
 * This file contains the null class.  The null class itself doesn't equate
 * to null, but it's field does, it's constant does, and function.  This is
 * useful when you want to return a null value that isn't actually language
 * specific null because null might be a correct return type
 * 
 * The name of this class was in conflict with a reserved keyword in PHP 7
 * so it has been changed to None
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic
 * @license http://opensource.org/licenses/MIT
 * @package Data
 * @subpackage Structures
 * 
 */

/**
 * Falcraft Libraries Data Types Namespace
 * 
 */
namespace Phabstractic\Data\Types
{

    /**
     * Null Class - Defines a NULL Object
     * 
     * Some functions can return null, false, empty, etc.
     * but not indicate a failure.  This object gives developers a class to use
     * that defines a truly null variable.  This way a function could return
     * null, false, empty, etc.  legitimately, and upon failure return new Null;
     * Example usage: if ( $loader instanceof Types\Null ) ...
     * 
     * CHANGELOG
     * 
     * 1.0: Created Null - May 10th, 2013
     * 2.0: Reproduced Null for Primus - April 2nd, 2015
     * 3.0: Formatted Null for inclusion in phabstractic
     *      Changed classname to None - July 7th, 2016
     * 
     * @version 3.0
     * 
     */
    class None
    {
        /**
        * The NULL constant redefined in the object
        * 
        */
        const NULL = null;
        
        /**
        * This is the null variable defined in the null object
        * 
        */
        public $null = null;
        
        /**
        * Everything equals to null in Null
        * 
        * @return null
        * 
        */
        public function null()
        {
            return null;
        }
    }
}
