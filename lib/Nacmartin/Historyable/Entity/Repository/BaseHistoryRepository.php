<?php
namespace Nacmartin\Historyable\Entity\Repository;

use Doctrine\ORM\Query,
    Doctrine\ORM\EntityRepository,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata;

class BaseHistoryRepository extends EntityRepository
{
    /**
     * Historyable listener on event manager
     *
     * @var AbstractTreeListener
     */
    protected $listener = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $histListener = null;
        foreach ($em->getEventManager()->getListeners() as $event => $listeners) {
            foreach ($listeners as $hash => $listener) {
                if ($listener instanceof \Nacmartin\Historyable\HistoryableListener) {
                    $histListener = $listener;
                    break;
                }
            }
            if ($histListener) {
                break;
            }
        }

        if (is_null($histListener)) {
            throw new \Nacmartin\Exception\InvalidMappingException('This repository can be attached only to ORM historyable listener');
        }

        $this->listener = $histListener;
    }

    public function getLast($id){
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);
        $qb = $this->_em->createQueryBuilder();
        $qb->select('st')
            ->from($meta->name, 'st')
            ->orderBy('st.'.$config['status_field'], 'DESC')
            ->setMaxResults(1);
        $q = $qb->getQuery();
        return $q->getSingleResult();
    }
}
