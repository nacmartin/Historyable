<?php

namespace Nacmartin\Historyable\Mapping\Event;

use Nacmartin\Mapping\Event\AdapterInterface;

/**
 * Doctrine event adapter interface
 * for Historyable behavior
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author Nacho Martin <nitram.ohcan@gmail.com>
 * @package Nacmartin\Historyable\Mapping\Event
 * @subpackage HistoryableAdapter
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
interface HistoryableAdapter extends AdapterInterface
{
    /**
     * Get new status number
     *
     * @param ClassMetadata $meta
     * @param object $object
     * @return integer
     */
    function getNewStatus($config, $meta, $object);
}

