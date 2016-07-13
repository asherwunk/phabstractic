<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * A local range error for data types
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * ckage Data
 * @subpackage Types
 * 
 */

/**
 * Falcraft Libraries Data Types Namespace
 * 
 */
namespace Phabstractic\Data\Types\Exception
{
    require_once(realpath( __DIR__ . '/../../../') . '/falcraftLoad.php');
    
    $includes = array('/Data/Types/Exception/ExceptionInterface.php',);
    
    falcraftLoad($includes, __FILE__);
    
    /**
     * A local run-time exception for data types
     * 
     * CHANGELOG
     * 
     * 1.0: created RangeException - April 7th, 2013
     * 2.0: reformatted for inclusion in phabstractic - July 10th, 2016
     * 
     * @version 2.0
     * 
     */
    class RangeException extends \RangeException implements ExceptionInterface
    {
        
    }
}
