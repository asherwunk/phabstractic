<?php

/* This document is meant to follow PSR-1 and PSR-2 php-fig standards
   http://www.php-fig.org/psr/psr-1/
   http://www.php-fig.org/psr/psr-2/ */

/**
 * Configuration Feature Implemented Through Traits
 * 
 * This is a 'feature', logic that can be added to a class, for managing
 * object configurations.  This uses Zend/Config to make it be able to
 * read and write to various configuration formats, as well as use
 * other implementations of Zend/Config/Reader/ReaderInterface or
 * Zend/Config/Writer/WriterInterface
 * 
 * @copyright Copyright 2016 Asher Wolfstein
 * @author Asher Wolfstein <asherwunk@gmail.com>
 * @link http://wunk.me/ The author's URL
 * @link http://wunk.me/programming-projects/phabstractic/ Framework URL
 * @license http://opensource.org/licenses/MIT
 * @package Features
 * @subpackage Configuration
 * 
 */

/**
 * Falcraft Libraries Features Namespace
 * 
 */
namespace Phabstractic\Features
{
    require __DIR__ . '/../../../vendor/autoload.php';
    require_once(realpath( __DIR__ . '/../') . '/falcraftLoad.php');
    
    $includes = array('/Features/Exception/ClassDependencyException.php',
                      '/Features/Exception/InvalidArgumentException.php',
                      '/Features/Resource/ConfigurationInterface.php',);
    
    falcraftLoad($includes, __FILE__);
    
    use Phabstractic\Features\Exception;
    use Phabstractic\Features\Resource as FeaturesResource;
    use Zend\Config;
    
    /**
     * The Configuration trait
     * 
     * Encapsulates all the logic necessary for a Zend/Config object to act
     * as another objects configurations.  Based around arrays and object
     * properties, see documentation for Zend/Config (version 2)
     * 
     * CHANGELOG
     * 
     * 1.0:   Documented Configuration - April 7th, 2015
     * 1.0.1: Moved reader->config if clause - April 7th, 2015
     * 1.0.2: Improved context handling in cases where format is not
     *        YAML - April 10th, 2015
     * 2.0:   reformatted for inclusion in phabstractic - July 7th, 2016
     * 2.1:   added ability to pass text into ->configure using
     *        #confformat and #confcontext - July 8th, 2016
     * 
     * @version 2.1
     * 
     */
    trait ConfigurationTrait
    {
        /**
         * The objects configuration object
         * 
         * Should be an instance of Zend/Config
         * 
         * @var Zend\Config\Config The object configuration info
         * 
         */
        protected $conf;
        
        /**
         * Configure An Object
         * 
         * Expects an array for configuration however, you can also pass it
         * a filepath where it will read the information from a file automatically
         * detecting the format using the file extension.  You can also pass a
         * Zend/Config object already made.
         * 
         * You can also pass a format specifier (forced format) for use in a
         * if $configuration is a string formatted with such information.  E.G.
         * to load from a string in the format ini:
         * 
         * $this->configure($configString, 'ini');
         * 
         * The $context argument is used for any additional reader constructor
         * information, such as the constructor for the 'yaml' format.
         * 
         * NOTE:  You can override/extend the classes used for reading formats
         *        by identifying an additional array in the property
         *        $this->configReaders.  This will merge with the standard
         *        formats array.
         * 
         * @param array|string|Zend\Config\Config $configuration The objects
         *            configuration information.
         * @param string $format The forced format, or format for configuration
         *            string
         * @param mixed $context Any additional information for a reader
         *            constructor, such as needed for the YAML format.
         * @return bool True if instantiated
         * 
         */
        public function configure($configuration, $format = null, $context = null)
        {
            /* If configuration is an instance of a Zend\Config\Config
               then we just use it and return */
            if ($configuration instanceof Config\Config) {
                $this->conf = $configuration;
                return;
            }
            
            /* If you pass in an array with two elements, one of them with
               the key '#confformat' you can specify configuration with a
               formatted string in the key 'configuration'
               
               For YAML you must also pass in processing information as
               the key '#confcontext' as per the Zend Config documentation */
            if (is_array($configuration)) {
                if (array_key_exists('#confformat', $configuration) &&
                        array_key_exists('configuration', $configuration)) {
                    $format = $configuration['#confformat'];
                    if ($format == 'yaml') {
                        if (array_key_exists('#confcontext', $configuration)) {
                            $context = $configuration['#confcontext'];
                        } else {
                            throw new Exception\ClassDependencyException(
                                'Phabstractic\\Features\\ConfigurationTrait->configure: ' .
                                'Reader Context Not Defined');
                        }
                    }
                    $configuration = $configuration['configuration'];
                } else {
                    $configuration = array_change_key_case($configuration);
                }
            }
            
            /* $configuration information as a string could be a filepath, or
               configuration information given as a string ($format contains
               the format information in this case) */
            if (is_string($configuration)) {
                
                // The standard format readers provided by Zend Framework
                $readers = array('ini' => '\\Zend\\Config\\Reader\\Ini',
                                 'xml' => '\\Zend\\Config\\Reader\\Xml',
                                 'json' => '\\Zend\\Config\\Reader\\Json',
                                 'yaml' => '\\Zend\\Config\\Reader\\Yaml',);
                /* This is IMPORTANT: use $configReaders property to override
                   and extend the standard readers.  For example, a MySQL reader
                   would require a property definition:
                   
                   private $configReaders = array();  <-- be sure to initialize
                                                          to array in definition
                   ...
                   $this->configReaders = array( 'mysql', 
                                                 '\\Qualified\\Name\\Reader'); */
                if (isset($this->configReaders)) {
                    $readers = array_merge($readers, $this->configReaders);
                }
                
                // Automatically set $extension to filename extension
                $extension = $format ?
                    $format :
                    strtolower(pathinfo($configuration, PATHINFO_EXTENSION));
                // Instantiate the proper reader class
                if (class_exists($readers[$extension])) {
                    if ( $context ) {
                        $reader = new $readers[$extension]($context);
                    } else {
                        $reader = new $readers[$extension]();
                    }
                } else {
                    throw new Exception\ClassDependencyException(
                        'Phabstractic\\Features\\ConfigurationTrait->configure: ' .
                        'Reader Class Not Defined');
                }
                
                /* Convert $configuration to array, as expected below, from
                   the reader object (must implement ReaderInterface) */
                if ($reader instanceof Config\Reader\ReaderInterface) {
                    $from = $format ? 'fromString' : 'fromFile';
                    $configuration = $reader->$from($configuration);
                } else {
                    throw new Exception\ClassDependencyException(
                        'Phabstractic\\Features\\ConfigurationTrait->configure: ' .
                        '$reader Does Not Implement Config\\Reader\\ReaderInterface');
                }
                
            }
            
            // Now that we're guaranteed an array, instantiate Config object
            if (is_array($configuration)) {
                $this->conf = new Config\Config($configuration, true);
            } else {
                // This error should be unreachable, thus it is not in the unit test
                throw new Exception\InvalidArgumentException(
                    'Phabstractic\\Features\\ConfiguraitonTrait->configure: ' .
                    '$configuration Not Array');
            }
            
            return;
        }
        
        /**
         * Save an Object's Configuration to a File
         * 
         * Takes an objects $conf property and writes the information contained
         * therein to a file with a format automatically specified by the
         * filename.
         * 
         * It is possible to retrieve a string of a particular format from this
         * method by specifying the filename '#string' with an extension
         * indicating the desired format, such as '#string.json'.
         * 
         * The $context argument is used for any additional reader constructor
         * information, such as the constructor for the 'yaml' format.
         * 
         * NOTE:  You can override/extend the classes used for writing formats
         *        by identifying an additional array in the property
         *        $this->configWriters.  This will merge with the standard
         *        formats array.
         * 
         * @param string $file The file path to write to, or '#string.ext'
         * @param Zend\Config\Writer\WriterInterface $writer The optional writer 
         *            object supplied to use (such as a MySQL writer)
         * @param boolean $exclusive Argument provided to toFile(), file
         *            exclusive lock when writing
         * @param mixed $context Any additionla writer constructor
         *            information (YAML)
         * 
         */
        public function saveSettings(
            $file,
            $writer = null,
            $exclusive = true,
            $context = null
        ) {
            // If $writer is specified in the parameter, use it and return
            if ($writer && $writer instanceof Config\Writer\WriterInterface) {
                $writer->toFile($file, $this->conf, $exclusive);
                return;
            }
            
            // The standard format writers provided by Zend Framework
            $writers = array('ini' => '\\Zend\\Config\\Writer\\Ini',
                             'xml' => '\\Zend\\Config\\Writer\\Xml',
                             'array' => '\\Zend\\Config\\Writer\\PhpArray',
                             'json' => '\\Zend\\Config\\Writer\\Json',
                             'yaml' => '\\Zend\\Config\\Writer\\Yaml',);
            /* This is IMPORTANT: use $configWriters property to override
               and extend the standard readers.  For example, a MySQL reader
               would require a property definition:
               
               private $configWriters = array();  <-- be sure to initialize
                                                      to array in definition
               ...
               $this->configReaders = array( 'mysql', 
                                             '\\Qualified\\Name\\Reader'); */
            if (isset($this->configWriters)) {
                $writers = array_merge($writers, $this->configWriters);
            }
            
            // Assign format from extension in given filename
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (class_exists($writers[$extension])) {
                if ( $context ) {
                    $writer = new $writers[$extension]($context);
                } else {
                    $writer = new $writers[$extension]();
                }
            } else {
                throw new Exception\ClassDependencyException(
                    'Phabstractic\\Features\\ConfigurationTrait->saveSettings: ' .
                    'Writer Class Not Defined');
            }
            
            // Now that we should be guaranteed a Writer object, write it
            if ($writer instanceof Config\Writer\WriterInterface) {
                if (strpos($file, '#string') !== 0) {
                    $writer->toFile($file, $this->conf, $exclusive);
                } else {
                    return $writer->toString($this->conf);
                }
            } else {
                throw new Exception\ClassDependencyException(
                    'Phabstractic\\Features\\ConfigurationTrait->saveSettings: ' .
                    '$writer Doesn\'t Implement Config\\Writer\\WriterInterface');
            }
            
            return true;
        }
        
        /**
         * Retrieve an Object's Configuration Information As String
         * 
         * This is a shortcut to ::saveSettings() which specifies a format
         * and forces the return of a string, using the #string.ext filename
         * -see documentation for ::saveSettings()-
         * 
         * @param string $format The format to return, must be supported by
         *            ::saveSettings(), use $this->configWriters to support
         *            additional formats.
         * 
         * @return string|boolean The formatted string, or false otherwise
         * 
         */
        public function getSettings($format, $context = null)
        {
            if ($format == 'yaml') {
                $settings = $this->saveSettings(
                    '#string.' . $format,
                    null,
                    true,
                    $context
                );
            } else {
                $settings = $this->saveSettings('#string.' . $format);
            }
            
            if ($settings === true) {
                return false;
            } else {
                return $settings;
            }
            
        }
        
        /**
         * Process an Object's Configuration
         * 
         * This uses a Zend\Config\Processor implementation to process the
         * configuration information, such as constants.  The processor
         * must be supplied and implement ProcessorInterface
         * 
         * NOTE: Edits the $conf object in place.
         * 
         * @param Zend\Config\Processor\ProcessorInterface $processor The given
         *            processor object
         * 
         */
        public function processSettings($processor)
        {
            if ($processor instanceof Config\Processor\ProcessorInterface) {
                $processor->process($this->conf);
            } else {
                throw new Exception\ClassDependencyException(
                    'Phabstractic\\Features\\ConfiguraitonTrait->processSettings: ' .
                    '$processor Doesn\'t Implement Config\\Processor\\ProcessorInterface');
            }
            
        }
        
    }
    
}
