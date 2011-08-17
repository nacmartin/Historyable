<?php

namespace Nacmartin\Historyable\Mapping\Event\Adapter;

use Nacmartin\Mapping\Event\Adapter\ORM as BaseAdapterORM;
use Nacmartin\Historyable\Mapping\Event\HistoryableAdapter;

/**
 * Doctrine event adapter for ORM adapted
 * for Historyable behavior
 *
 * @author Nacho Martin <nitram.ohcan@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Nacmartin\Historyable\Mapping\Event\Adapter
 * @subpackage ORM
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
final class ORM extends BaseAdapterORM implements HistoryableAdapter
{

    /**
     * {@inheritDoc}
     */
    public function getNewStatus($config, $meta, $object)
    {
        $em = $this->getObjectManager();
        $objectMeta = $em->getClassMetadata(get_class($object));
        $identifierField = $this->getSingleIdentifierFieldName($objectMeta);
        $objectId = $objectMeta->getReflectionProperty($identifierField)->getValue($object);

        $dql = "SELECT MAX(status.{$config['status_field']}) FROM {$meta->name} status";
        $dql .= " WHERE status.{$config['refVersion_field']} = :objectId";

        $q = $em->createQuery($dql);
        $q->setParameters(array(
            'objectId' => $objectMeta->getReflectionProperty($config['refVersion_field'])->getValue($object)
        ));
        $currver = $q->getSingleScalarResult();
        return $currver != null ? $currver + 1: 0;
    }
}
