<?php

namespace Nacmartin\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\Reader;
use Nacmartin\Mapping\ExtensionMetadataFactory;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\EventArgs;

/**
 * This is extension of event subscriber class and is
 * used specifically for handling the extension metadata
 * mapping for extensions.
 *
 * It dries up some reusable code which is common for
 * all extensions who mapps additional metadata through
 * extended drivers
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Nacmartin.Mapping
 * @subpackage MappedEventSubscriber
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
abstract class MappedEventSubscriber implements EventSubscriber
{
    /**
     * List of cached object configurations
     *
     * @var array
     */
    protected $configurations = array();

    /**
     * ExtensionMetadataFactory used to read the extension
     * metadata through the extension drivers
     *
     * @var Nacmartin\Mapping\ExtensionMetadataFactory
     */
    private $extensionMetadataFactory = array();

    /**
     * List of event adapters used for this listener
     *
     * @var array
     */
    private $adapters = array();

    /**
     * Custom annotation reader
     *
     * @var object
     */
    private $annotationReader;

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    private $defaultAnnotationReader;

    /**
     * Get an event adapter to handle event specific
     * methods
     *
     * @param EventArgs $args
     * @throws \Nacmartin\Exception\InvalidArgumentException - if event is not recognized
     * @return \Nacmartin\Mapping\Event\AdapterInterface
     */
    protected function getEventAdapter(EventArgs $args)
    {
        $class = get_class($args);
        if (preg_match('@Doctrine\\\([^\\\]+)@', $class, $m) && in_array($m[1], array('ODM', 'ORM'))) {
            if (!isset($this->adapters[$m[1]])) {
                $adapterClass = $this->getNamespace() . '\\Mapping\\Event\\Adapter\\' . $m[1];
                if (!class_exists($adapterClass)) {
                    $adapterClass = 'Nacmartin\\Mapping\\Event\\Adapter\\'.$m[1];
                }
                $this->adapters[$m[1]] = new $adapterClass;
            }
            $this->adapters[$m[1]]->setEventArgs($args);
            return $this->adapters[$m[1]];
        } else {
            throw new \Nacmartin\Exception\InvalidArgumentException('Event mapper does not support event arg class: '.$class);
        }
    }

    /**
     * Get the configuration for specific object class
     * if cache driver is present it scans it also
     *
     * @param ObjectManager $objectManager
     * @param string $class
     * @return array
     */
    public function getConfiguration(ObjectManager $objectManager, $class) {
        $config = array();
        if (isset($this->configurations[$class])) {
            $config = $this->configurations[$class];
        } else {
            $factory = $objectManager->getMetadataFactory();
            $cacheDriver = $factory->getCacheDriver();
            if ($cacheDriver) {
                $cacheId = ExtensionMetadataFactory::getCacheId($class, $this->getNamespace());
                if (($cached = $cacheDriver->fetch($cacheId)) !== false) {
                    $this->configurations[$class] = $cached;
                    $config = $cached;
                } else {
                    // re-generate metadata on cache miss
                    $this->loadMetadataForObjectClass($objectManager, $factory->getMetadataFor($class));
                    if (isset($this->configurations[$class])) {
                        $config = $this->configurations[$class];
                    }
                }
            }
        }
        return $config;
    }

    /**
     * Get extended metadata mapping reader
     *
     * @param ObjectManager $objectManager
     * @return Nacmartin\Mapping\ExtensionMetadataFactory
     */
    public function getExtensionMetadataFactory(ObjectManager $objectManager)
    {
        $oid = spl_object_hash($objectManager);
        if (!isset($this->extensionMetadataFactory[$oid])) {
            if (is_null($this->annotationReader)) {
                // create default annotation reader for extensions
                $this->annotationReader = $this->getDefaultAnnotationReader();
            }
            $this->extensionMetadataFactory[$oid] = new ExtensionMetadataFactory(
                $objectManager,
                $this->getNamespace(),
                $this->annotationReader
            );
        }
        return $this->extensionMetadataFactory[$oid];
    }

    /**
     * Set annotation reader class
     * since older doctrine versions do not provide an interface
     * it must provide these methods:
     *     getClassAnnotations([reflectionClass])
     *     getClassAnnotation([reflectionClass], [name])
     *     getPropertyAnnotations([reflectionProperty])
     *     getPropertyAnnotation([reflectionProperty], [name])
     *
     * @param object $reader - annotation reader class
     */
    public function setAnnotationReader($reader)
    {
        $this->annotationReader = $reader;
    }

    /**
     * Scans the objects for extended annotations
     * event subscribers must subscribe to loadClassMetadata event
     *
     * @param ObjectManager $objectManager
     * @param ClassMetadata $metadata
     * @return void
     */
    public function loadMetadataForObjectClass(ObjectManager $objectManager, ClassMetadata $metadata)
    {
        $factory = $this->getExtensionMetadataFactory($objectManager);
        $config = $factory->getExtensionMetadata($metadata);
        if ($config) {
            $this->configurations[$metadata->name] = $config;
        }
    }

    /**
     * Get the namespace of extension event subscriber.
     * used for cache id of extensions also to know where
     * to find Mapping drivers and event adapters
     *
     * @return string
     */
    abstract protected function getNamespace();

    /**
     * Create default annotation reader for extensions
     *
     * @return \Doctrine\Common\Annotations\AnnotationReader
     */
    private function getDefaultAnnotationReader()
    {
        if (null === $this->defaultAnnotationReader) {
            if (version_compare(\Doctrine\Common\Version::VERSION, '3.0.0-DEV', '>=')) {
                $reader = new \Doctrine\Common\Annotations\AnnotationReader();
                \Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
                    'Nacmartin\\Mapping\\Annotation',
                    __DIR__ . '/../../'
                );
                $reader = new \Doctrine\Common\Annotations\CachedReader($reader, new ArrayCache());
            } else if (version_compare(\Doctrine\Common\Version::VERSION, '2.1.0RC4-DEV', '>=')) {
                $reader = new \Doctrine\Common\Annotations\AnnotationReader();
                \Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
                    'Nacmartin\\Mapping\\Annotation',
                    __DIR__ . '/../../'
                );
                $reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
                $reader = new \Doctrine\Common\Annotations\CachedReader($reader, new ArrayCache());
            } else if (version_compare(\Doctrine\Common\Version::VERSION, '2.1.0-BETA3-DEV', '>=')) {
                $reader = new \Doctrine\Common\Annotations\AnnotationReader();
                $reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
                $reader->setIgnoreNotImportedAnnotations(true);
                $reader->setAnnotationNamespaceAlias('Nacmartin\\Mapping\\Annotation\\', 'nacmartin');
                $reader->setEnableParsePhpImports(false);
                $reader->setAutoloadAnnotations(true);
                /*\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
                    'Nacmartin\\Mapping\\Annotation',
                    __DIR__ . '/../../'
                );*/
                $reader = new \Doctrine\Common\Annotations\CachedReader(
                    new \Doctrine\Common\Annotations\IndexedReader($reader), new ArrayCache()
                );
            } else {
                $reader = new \Doctrine\Common\Annotations\AnnotationReader();
                $reader->setAutoloadAnnotations(true);
                $reader->setAnnotationNamespaceAlias('Nacmartin\\Mapping\\Annotation\\', 'nacmartin');
                $reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
            }
            $this->defaultAnnotationReader = $reader;
        }
        return $this->defaultAnnotationReader;
    }
}
