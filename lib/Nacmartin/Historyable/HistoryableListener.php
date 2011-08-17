<?php

namespace Nacmartin\Historyable;

use Nacmartin\Mapping\MappedEventSubscriber,
    Nacmartin\Historyable\Mapping\Event\HistoryableAdapter,
    Doctrine\Common\EventArgs;

/**
 * Historyable listener
 *
 * @author Boussekeyt Jules <jules.boussekeyt@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author Nacho Martin <nitram.ohcan@gmail.com>
 * @package Nacmartin.Loggable
 * @subpackage LoggableListener
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class HistoryableListener extends MappedEventSubscriber
{

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'onFlush',
            'loadClassMetadata',
        );
    }

    /**
     * Mapps additional metadata
     *
     * @param EventArgs $eventArgs
     * @return void
     */
    public function loadClassMetadata(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $this->loadMetadataForObjectClass($ea->getObjectManager(), $eventArgs->getClassMetadata());
    }


    /**
     * Looks for historyable objects being inserted or updated
     *
     * @param EventArgs $args
     * @return void
     */
    public function onFlush(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $om = $ea->getObjectManager();
        $uow = $om->getUnitOfWork();

        foreach ($ea->getScheduledObjectInsertions($uow) as $object) {
            $meta = $om->getClassMetadata(get_class($object));
            if($config = $this->getConfiguration($om, $meta->name)){
                $status = $ea->getNewStatus($config, $meta, $object);
                $object->setStatus($status);
                $ea->recomputeSingleObjectChangeSet($uow, $meta, $object);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}

