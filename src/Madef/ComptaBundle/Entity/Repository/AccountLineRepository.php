<?php

namespace Madef\ComptaBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class AccountLineRepository extends EntityRepository
{

    /**
     * Get  account lines between two dates
     * @param  \DateTime $startDate
     * @param  \DateTime $endDate
     * @return type
     */
    public function findByDate(\DateTime $startDate, \DateTime $endDate, $type = "", $flowDirection = "")
    {
        // First get the EM handle
        // and call the query builder on it
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('r')
                ->from('Madef\ComptaBundle\Entity\AccountLine', 'r')
                ->where('r.date >= :startDate')
                ->andWhere('r.date <= :endDate')
                ->orderBy('r.date')
                ->setParameter('startDate', $startDate->format('Y-m-d'))
                ->setParameter('endDate', $endDate->format('Y-m-d'));

        if (!empty($type)) {
            $query
                    ->andWhere('r.type = :type')
                    ->setParameter('type', $type);
        }

        if (!empty($flowDirection)) {
            if ($flowDirection == 'in') {
                $query->andWhere('r.valueTaxInclude > 0');
            } else {
                $query->andWhere('r.valueTaxInclude < 0');
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get total from the begening to a specific date
     * @param  \DateTime $date
     * @param  bool      $includeLimit
     * @return type
     */
    public function getTotal(\DateTime $date, $includeLimit, $type = "", $flowDirection = "")
    {
        // First get the EM handle
        // and call the query builder on it
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('sum(r.valueTaxInclude) as totalTaxInclude, sum(r.valueTaxExclude) as totalTaxExclude, sum(r.taxValue) as taxTotal')
                ->from('Madef\ComptaBundle\Entity\AccountLine', 'r')
                ->where('r.date ' . ($includeLimit ? '<=' : '<' ) . ' :date')
                ->orderBy('r.date')
                ->setParameter('date', $date->format('Y-m-d'));

        if (!empty($type)) {
            $query
                    ->andWhere('r.type = :type')
                    ->setParameter('type', $type);
        }

        if (!empty($flowDirection)) {
            if ($flowDirection == 'in') {
                $query->andWhere('r.valueTaxInclude > 0');
            } else {
                $query->andWhere('r.valueTaxInclude < 0');
            }
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Get total between two dates
     * @param  \DateTime $startDate
     * @param  \DateTime $endDate
     * @return type
     */
    public function getRangeTotal(\DateTime $startDate, \DateTime $endDate, $type = "", $flowDirection = "")
    {
        // First get the EM handle
        // and call the query builder on it
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('sum(r.valueTaxInclude) as totalTaxInclude, sum(r.valueTaxExclude) as totalTaxExclude, sum(r.taxValue) as taxTotal')
                ->from('Madef\ComptaBundle\Entity\AccountLine', 'r')
                ->where('r.date >= :startDate')
                ->andWhere('r.date <= :endDate')
                ->orderBy('r.date')
                ->setParameter('startDate', $startDate->format('Y-m-d'))
                ->setParameter('endDate', $endDate->format('Y-m-d'));

        if (!empty($type)) {
            $query
                    ->andWhere('r.type = :type')
                    ->setParameter('type', $type);
        }

        if (!empty($flowDirection)) {
            if ($flowDirection == 'in') {
                $query->andWhere('r.valueTaxInclude > 0');
            } else {
                $query->andWhere('r.valueTaxInclude < 0');
            }
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Get the list of available types
     * @return type
     */
    public function getTypeList()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('DISTINCT(al.type) as type')
                ->from('Madef\ComptaBundle\Entity\AccountLine', 'al')
                ->where('al.type IS NOT NULL')
                ->andWhere('al.type <> :empty')
                ->setParameter('empty', '') // There is a best way ?
                ->orderBy('al.type');

        $results = array();
        foreach ($qb->getQuery()->getResult() as $row) {
            $results [] = $row['type'];
        }

        return $results;
    }

}
