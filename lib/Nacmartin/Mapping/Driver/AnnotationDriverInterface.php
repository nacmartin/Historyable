<?php

namespace Nacmartin\Mapping\Driver;

use Nacmartin\Mapping\Driver;

/**
 * Annotation driver interface, provides method
 * to set custom annotation reader.
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author Nacho Martin <nitram.ohcan@gmail.com>
 * @package Nacmartin.Mapping.Driver
 * @subpackage AnnotationDriverInterface
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
interface AnnotationDriverInterface extends Driver
{
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
    public function setAnnotationReader($reader);
}
