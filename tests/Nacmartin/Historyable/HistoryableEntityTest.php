<?php

namespace Nacmartin\Historyable;

use Tool\BaseTestCaseORM;
use Doctrine\Common\EventManager;
use Doctrine\Common\Util\Debug,
    Historyable\Fixture\Entity\History;

/**
 * Tests for historyable behavior
 * @author Nacho Martin <nitram.ohcan@gmail.com>
 * @link http://nacho-martin.com
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class HistoryableEntityTest extends BaseTestCaseORM
{
    const HISTORY = 'Historyable\Fixture\Entity\History';
    protected function getUsedEntityFixtures()
    {
      return array(
        self::HISTORY
      );
    }

    protected function setUp()
    {
        parent::setUp();

        $evm = new EventManager;
        $this->HistoryableListener = new HistoryableListener();
        $evm->addEventSubscriber($this->HistoryableListener);
        $this->em = $this->getMockSqliteEntityManager($evm);
    }

    public function testHistoryable()
    {
        $statusRepo = $this->em->getRepository(self::HISTORY);
        $this->assertEquals(0, count($statusRepo->findAll()));

        $status0 = new History();
        $status0->setResourceId(1);
        $status0->setAction("added stuff");
        $this->em->persist($status0);
        $this->em->flush();

        $statusLast = $statusRepo->getLast(1);
        $this->assertNotEquals(null, $statusLast);
        $this->assertEquals(0, $statusLast->getStatus(), "first status is 0");

        $status1 = new History();
        $status1->setResourceId(1);
        $status1->setAction("added more stuff");
        $this->em->persist($status1);
        $this->em->flush();

        $statusLast = $statusRepo->getLast(1);
        $this->assertEquals(1, $statusLast->getStatus(), "after saving two status of the same resource, last status is 1.");

        $status2 = new History();
        $status2->setResourceId(2);
        $status2->setAction("stuff done to a different resource");
        $this->em->persist($status2);
        $this->em->flush();

        $statusLast = $statusRepo->getLast(1);
        $this->assertEquals(1, $statusLast->getStatus(), "after saving 1 status to another resource, last status is 1");
    }
}

