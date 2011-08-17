<?php

namespace Nacmartin\Historyable\Mapping\Driver;

use Nacmartin\Mapping\Driver\AnnotationDriverInterface,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Nacmartin\Exception\InvalidMappingException;

/**
 * This is an annotation mapping driver for Historyable
 * behavioral extension. Used for extraction of extended
 * metadata from Annotations specificaly for Historyable
 * extension.
 *
 * @author Boussekeyt Jules <jules.boussekeyt@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author Nacho Martin <nitram.ohcan@gmail.com>
 * @package Nacmartin.Historyable.Mapping.Driver
 * @subpackage Annotation
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Annotation implements AnnotationDriverInterface
{
    /**
     * Annotation to define that this object is historyable
     */
    const HISTORYABLE = 'Nacmartin\\Mapping\\Annotation\\Historyable';

    /**
     * Annotation to define that this property is a status
     */
    const STATUS = 'Nacmartin\\Mapping\\Annotation\\Status';

    /**
     * Annotation to define that this property is a refVersion
     */
    const REFVERSION = 'Nacmartin\\Mapping\\Annotation\\RefVersion';

    /**
     * List of types which are valid for status and refVersion fields
     *
     * @var array
     */
    private $validTypes = array(
        'integer'
    );

    /**
     * Annotation reader instance
     *
     * @var object
     */
    private $reader;

    /**
     * original driver if it is available
     */
    protected $_originalDriver = null;

    /**
     * {@inheritDoc}
     */
    public function setAnnotationReader($reader)
    {
        $this->reader = $reader;
    }


    /**
     * {@inheritDoc}
     */
    public function validateFullMetadata(ClassMetadata $meta, array $config)
    {
        if ($config && is_array($meta->identifier) && count($meta->identifier) > 1) {
            throw new InvalidMappingException("Historyable does not support composite identifiers in class - {$meta->name}");
        }
        if (isset($config['status']) && !isset($config['historyable'])) {
            throw new InvalidMappingException("Class must be annoted with Historyable annotation - {$meta->name}");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata(ClassMetadata $meta, array &$config)
    {
        $class = $meta->getReflectionClass();
        // class annotations
        if ($annot = $this->reader->getClassAnnotation($class, self::HISTORYABLE)) {
            $config['historyable'] = true;

        }

        // property annotations
        foreach ($class->getProperties() as $property) {
            if ($meta->isMappedSuperclass && !$property->isPrivate() ||
                $meta->isInheritedField($property->name) ||
                isset($meta->associationMappings[$property->name]['inherited'])
            ) {
                continue;
            }
            if ($status = $this->reader->getPropertyAnnotation($property, self::STATUS)) {
                $field = $property->getName();
                if (!$meta->hasField($field)) {
                    throw new InvalidMappingException("Unable to find status [{$field}] as mapped property in entity - {$meta->name}");
                }
                if (!$this->isValidField($meta, $field)) {
                    throw new InvalidMappingException("Cannot make status on field - [{$field}] type is not valid and must be 'integer' in class - {$meta->name}");
                }
                if (isset($config['status_field'])) {
                    throw new InvalidMappingException("There cannot be more than one status field: [{$field}] and [{$config['status_field']}], in class - {$meta->name}.");
                }
                $config['status_field'] = $field;
            }
            if ($refVersion = $this->reader->getPropertyAnnotation($property, self::REFVERSION)) {
                $field = $property->getName();
                if (isset($config['refversion_field'])) {
                    throw new InvalidMappingException("There cannot be more than one refVersion field: [{$field}] and [{$config['status_refVersion']}], in class - {$meta->name}.");
                }
                $config['refVersion_field'] = $field;
            }
        }
    }
    /**
     * Checks if $field type is valid as status or refVersion field
     *
     * @param ClassMetadata $meta
     * @param string $field
     * @return boolean
     */
    protected function isValidField($meta, $field)
    {
        $mapping = $meta->getFieldMapping($field);
        return $mapping && in_array($mapping['type'], $this->validTypes);
    }
    /**
     * Passes in the mapping read by original driver
     *
     * @param $driver
     * @return void
     */
    public function setOriginalDriver($driver)
    {
        $this->_originalDriver = $driver;
    }

}

