<?php

$includes = array('/Data/Types/None.php',
                  '/Patterns/Registry.php',);

foreach ( $includes as $include )
{
    if ( realpath( __DIR__ . str_replace( '/', DIRECTORY_SEPARATOR, $include ) ) === false )
        throw new \RuntimeException( "loadPHP - $context: include $include not found" );
    require_once( realpath( __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $include ) ) );
}

use Phabstractic\Patterns;
use Phabstractic\Data\Types;

if (!defined('UNITTEST_LOADREGISTRY')) {
    define('UNITTEST_LOADREGISTRY', true);
}

/* The following uses the Singletons namespace to instantiate a registry object
   and then check that registry object for the standard Falcraft AutoLoader
   singleton, defined by standard in the Loader/Bootstrap.php.  This file
   works with and without the standard Falcraft AutoLoader as well, much as
   the registry singleton works without it, but obviously cannot work without the
   registry singleton. */

/**
 * Load PHP Files When Autoloader Doesn't Exist
 * 
 * The following uses the patterns namespace to instantiate a registry object
 * and then check that registry object for the standard Falcraft AutoLoader
 * singleton, defined standard in the Loader/Bootstrap.php.  These results are
 * cached, but can be refreshed later.  If there is no loader we include
 * the files passed to the function.
 * 
 * @param array $libraries files to load relative to this file
 * @param string $context the context of the file including
 * @param mixed $refresh refresh the loader contents with another value
 * 
 */
function falcraftLoad(array $libraries, $context = 'global', $refresh = false)
{
    static $falcraftRegistry, $falcraftLoader;
    
    if (!$falcraftRegistry && UNITTEST_LOADREGISTRY) {
        // Access global registry
        $falcraftRegistry = Patterns\Registry::instantiate();
        $falcraftLoader = $falcraftRegistry->get( 'Falcraft\Singletons\Autoloader' );
    } elseif (!UNITTEST_LOADREGISTRY) {
        $falcraftLoader = new Types\None();
    }
    
    if ($refresh) {
        $falcrafLoader = $refresh;
    }
    
    if ( $falcraftLoader instanceof Types\None ) {
        foreach ( $libraries as $include ) {
            if ( realpath( __DIR__ . str_replace( '/', DIRECTORY_SEPARATOR, $include ) ) === false )
                throw new \RuntimeException( "falcraftLoad - $context: include $include not found" );
            require_once( realpath( __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $include ) ) );
        }
        
    }
    
}
