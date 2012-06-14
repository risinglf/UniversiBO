<?php

namespace Universibo\Bundle\LegacyBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
 * InformativaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class InformativaRepository extends EntityRepository
{
    public function findByTime()
    {
        $time = time();

        $result = $this
                ->createQueryBuilder('i')
                ->where('i.dataPubblicazione <= :time')
                ->andWhere('i.dataFine > :time OR i.dataFine IS NULL')
                ->orderBy('i.dataPubblicazione', 'DESC')
                ->getQuery()
                ->setMaxResults(1)
                ->execute(array('time' => $time));
        
        return count($result) === 0 ? null : $result[0];
    }
}