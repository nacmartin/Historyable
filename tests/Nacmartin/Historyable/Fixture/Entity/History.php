<?php

namespace Historyable\Fixture\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nacmartin\Mapping\Annotation as Nacmartin;

/**
 * @ORM\Table(name="history")
 * @ORM\Entity(repositoryClass="Historyable\Fixture\Repository\HistoryRepository")
 * @Nacmartin\Historyable
 */

class History
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
    * @Nacmartin\RefVersion
    * @ORM\Column(name="resource_id", type="integer") 
    */
    private $resourceId;

    /**
     * @Nacmartin\Status
     * @ORM\Column(type="integer")
     */
    private $status;

    /*
     * @ORM\Column(type="string")
     */
    private $action;

    public function getId()
    {
        return $this->id;
    }

    public function getResourceId()
    {
        return $this->resourceId;
    }
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
    }
    public function getStatus()
    {
        return $this->status;
    }
    public function setStatus($status)
    {
        $this->status = $status;
    }
    public function getAction()
    {
        return $this->action;
    }
    public function setAction($action)
    {
        $this->action = $action;
    }
}
