<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Global Datatypes Enumeration and Functions
 * 
 * This file contains the definition of the global data types enumerator
 * (BOOL, INT, STRING, ARRAY, etc.) A data type here is a value that refers to 
 * a specific data type, including stdClass objects and other specifically
 * classed object.  This file uses two namespaces, so that the enumerator
 * generation doesn't happen in the global space, and so that two namespaced
 * functions can be defined that support the 'conversion' of values to types
 * and strings to types, proven useful in the TaggedUnion class for
 * example.
 * 
 * This file is should be autoloadable, though not executed in the traditional
 * sense (one class per file)
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Data
 * @subpackage Types
 * 
 */

/**
 * Falcraft Libraries Data Types Namespace
 * 
 */
namespace Phabstractic\Data\Types
{
    
    require_once(realpath( __DIR__ . '/../../') . '/falcraftLoad.php');
    
    $includes = array('/Data/Types/Enumeration.php',);
    
    falcraftLoad($includes, __FILE__);
    
    /* Generate some code for the namespace Falcraft\Data\Types
       This will create an enumerator class (Falcraft\Data\Types\Type) that any
       class can use to classify different data types */
    
    /**
     * Type Class - A generated enumerator class containing data type values
     * 
     * This class is generated and then loaded using the Enumeration.php method.
     * It is used to define a global (with fully qualified name) 'registry' or
     * enumeration of all possible PHP datatypes: bool, int, string, array,
     * stdClass object, "typed" object (of a particular class or inheritance),
     * resource, null, closure, and function.
     * 
     * CHANGELOG
     * 
     * 1.0: Documented - May 3rd, 2013
     * 2.0: Refactored/relicensed into Primus - April 7th, 2015
     * 3.0: escaped namespace string properly
     *      made compatible with enumeration.php v3
     *      reformatted for inclusion into phabstractic - July 8th, 2016
     * 
     * @uses Falcraft\Data\Types\Enumeration for enumerator class generation
     * 
     * @version 2.0
     * 
     */
    Enumeration::createEnumerator('Type', array( 'BASIC_BOOL',
                                          'BASIC_INT',
                                          'BASIC_FLOAT',
                                          'BASIC_STRING',
                                          'BASIC_ARRAY',
                                          'BASIC_OBJECT',
                                          'TYPED_OBJECT',
                                          'BASIC_RESOURCE',
                                          'BASIC_NULL',
                                          'BASIC_CLOSURE',
                                          'BASIC_FUNCTION',
                                          'BASIC_CALLABLE'),
                                   array('namespace' => 'Phabstractic\\Data\\Types' ) );
}

/**
 * Falcraft Libraries Data Types "Type" Namespace
 * 
 * Contains the two global utility functions under the Type namespace (same as 
 * the enum above, and actually the same as the fully qualified generated class 
 * name).  These functions support "type" conversion and generation from values 
 * and strings.  Useful in the Restrictions.php
 * 
 */
namespace Phabstractic\Data\Types\Type
{
    // Use the above enumerator generator namespace
    use Phabstractic\Data\Types;
    
    /**
     * Generates an enumerated Type representative value from a given variable
     * 
     * This steps through the various type tests PHP is able to do to determine
     * the type of a variable (bool, int, etc.) and then returns that type as a
     * representative value from the Type Class above (an actual instance).
     * 
     * Note:  With a Typed Object, the enumeration instance includes the
     *        immediate class value available from get_class of the value
     *        regardless of inheritance, implementation, or trait
     * 
     * Note:  This function only checks user-defined functions for 'function' type.
     *        Not internal functions.  The 'function' type is based on the string
     *        type, so it supersedes the string type if evaluated as true.
     * 
     * Important:  This returns a enumeration object with an enumeration value.
     *             Unless a typed object, then returns an array with an
     *             enumeration object with an enumerated value, AND the object.
     * 
     * VERSION 3 INFORMATION: 
     * 
     * @uses Falcraft\Data\Types\Type to generate type enumeration objects
     * 
     * @param mixed $value  The variable we wish to determine
     * 
     * @return \Phabstractic\Data\Types\Type|bool The enumeration instance,
     *              or false on 'failure'
     * 
     */
    function getValueType( $value )
    {
        // Get all user defined functions
        $functions = get_defined_functions();
        $functions = $functions['user'];
        
        if ($value === null) {
            return new Types\Type(Types\Type::BASIC_NULL);
        } else if (is_bool($value)) {
            return new Types\Type(Types\Type::BASIC_BOOL);
        } else if (is_float($value)) {
            return new Types\Type(Types\Type::BASIC_FLOAT);
        } else if (is_int($value)) {
            return new Types\Type(Types\Type::BASIC_INT);
        /* If string is the name of an existing user defined function, supersede
           string comparison and return as function type */
        } else if (is_string($value) && in_array(strtolower($value), $functions)) {
            return new Types\Type(Types\Type::BASIC_FUNCTION);
        } else if (is_array($value) && is_callable($value)) {
            return new Types\Type(Types\Type::BASIC_CALLABLE);
        } else if (is_string($value)) {
            return new Types\Type(Types\Type::BASIC_STRING);
        } else if (is_array($value)) {
            return new Types\Type(Types\Type::BASIC_ARRAY);
        } else if (is_resource($value)) {
            return new Types\Type(Types\Type::BASIC_RESOURCE);
        /* Closures are of the special class Closure, check this AFTER function type
           but before Object type, lest it be mistaken for a typed object. */
        } else if (is_object($value) && is_a($value, 'Closure')) {
            return new Types\Type(Types\Type::BASIC_CLOSURE);
        // Is this a generic object?
        } else if (is_object($value) && (get_class($value) == 'stdClass')) {
            return new Types\Type(Types\Type::BASIC_OBJECT);
        // Or a typed object?
        } else if (is_object($value) && (get_class($value) != 'stdClass')) {
            return array(new Types\Type(Types\Type::TYPED_OBJECT), $value);
        }
        
        /* This function returns a new instance of the enumerator value (so it 
           can also include the class name if it is a typed object), if for 
           some reason a variable isn't ANY type, we return false, rather than 
           throw an error, since it's possible that a variable might not fit any
           type (in the future). */
        return false;
    }
    
    /**
     * Generates a type enumeration instance based on a given string
     * 
     * This function takes a string input and steps through various possible
     * values to generate a new instance of a type enumeration.
     * 
     * Note:  This function cannot return 'typed' objects, only standard
     *        generic objects inheriting from stdClass.
     * 
     * Important:  This returns a enumeration object with an enumeration value.
     * 
     * @uses Falcraft\Data\Types\Type to generate type enumeration objects
     * 
     * @param string $string The string to evaluate
     * 
     * @return Falcraft\Data\Types\Type|null The type object set to the 
     *              appropriate type, or null otherwise
     * 
     */
    function stringToType($string)
    {
        switch (strtoupper($string)) {
            case 'BASIC_BOOL':
            case 'BOOL':
            case 'BOOLEAN':
                return new Types\Type(Types\Type::BASIC_BOOL);
                break;
            
            case 'BASIC_INT':
            case 'INT':
            case 'INTEGER':
                return new Types\Type(Types\Type::BASIC_INT);
                break;
            
            case 'BASIC_STRING':
            case 'STRING':
            case 'STR':
                return new Types\Type(Types\Type::BASIC_STRING);
                break;
            
            case 'BASIC_ARRAY':
            case 'ARRAY':
            case 'ARR':
                return new Types\Type(Types\Type::BASIC_ARRAY);
                break;
            
            case 'BASIC_OBJECT':
            case 'OBJECT':
            case 'OBJ':
                return new Types\Type(Types\Type::BASIC_OBJECT);
                break;
            
            case 'BASIC_RESOURCE':
            case 'RESOURCE':
            case 'RSRC':
                return new Types\Type(Types\Type::BASIC_RESOURCE);
                break;
            
            case 'BASIC_NULL':
            case 'NULL':
                return new Types\Type(Types\Type::BASIC_NULL);
                break;
            
            case 'BASIC_CLOSURE':
            case 'CLOSURE':
                return new Types\Type(Types\Type::BASIC_CLOSURE);
                break;
            
            case 'BASIC_FUNCTION':
            case 'FUNCTION':
            case 'FUNC':
                return new Types\Type(Types\Type::BASIC_FUNCTION);
                break;
                
            default:
                return null;
                break;
        }
        
    }
    
}
