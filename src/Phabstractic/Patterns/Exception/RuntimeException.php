<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * A local runtime error for patterns
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Patterns
 * @subpackage Exception
 * 
 */

/**
 * Falcraft Libraries Patterns Namespace
 * 
 */
namespace Phabstractic\Patterns\Exception
{
    // loading function /falcraftLoad.php depends on registry
    $includes = array('/ExceptionInterface.php');

    foreach ( $includes as $include )
    {
        if ( realpath( __DIR__ . str_replace( '/', DIRECTORY_SEPARATOR, $include ) ) === false )
            throw new \RuntimeException( "Patterns\Exceptions\RuntimeException: include $include not found" );
        require_once( realpath( __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $include ) ) );
    }
    
    /**
     * A local run-time exception for patterns
     * 
     * CHANGELOG
     * 
     * 1.0: created RuntimeException - April 11th, 2015
     * 2.0: formatted for inclusion in phabstractic - July 7th, 2016
     * 
     * @version 2.0
     * 
     */
    class RuntimeException extends \RunTimeException implements ExceptionInterface
    {
        
    }
}
