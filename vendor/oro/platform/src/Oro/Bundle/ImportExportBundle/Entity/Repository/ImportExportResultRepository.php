<?php

namespace Oro\Bundle\ImportExportBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Doctrine repository for ImportExportResult entity
 */
class ImportExportResultRepository extends EntityRepository
{
    /**
     * @param \DateTime $from
     * @param \DateTime $to
     */
    public function updateExpiredRecords(\DateTime $from, \DateTime $to): void
    {
        $qb = $this->createQueryBuilder('importExportResult');
        $qb->update()
            ->set('importExportResult.expired', ':expired')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->gte('importExportResult.createdAt', ':from'),
                    $qb->expr()->lte('importExportResult.createdAt', ':to')
                )
            )
            ->setParameters([
                'expired' => true,
                'from' => $from,
                'to' => $to
            ]);

        $qb->getQuery()->execute();
    }
}
