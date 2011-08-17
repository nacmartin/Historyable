<?php

namespace Nacmartin\Mapping\Event;

use Doctrine\Common\EventArgs;

/**
 * Doctrine event adapter interface is used
 * to retrieve common functionality for Doctrine
 * events
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Nacmartin.Mapping.Event
 * @subpackage AdapterInterface
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
interface AdapterInterface
{
    /**
     * Set the eventargs
     *
     * @param EventArgs $args
     */
    function setEventArgs(EventArgs $args);

    /**
     * Get the name of domain object
     *
     * @return string
     */
    function getDomainObjectName();

    /**
     * Get the name of used manager for this
     * event adapter
     */
    function getManagerName();
}
