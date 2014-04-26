<?php

namespace Madef\ComptaBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class InvoiceRepository extends EntityRepository
{

    /**
     * Get invoices between two date
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
                ->from('Madef\ComptaBundle\Entity\Invoice', 'r')
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
            $constDirection = '\Madef\ComptaBundle\Entity\Invoice::FLOW_DIRECTION_' . strtoupper($flowDirection);
            $query
                    ->andWhere('r.flowDirection = :flowDirection')
                    ->setParameter('flowDirection', constant($constDirection));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get total between two date
     * @param  \DateTime $startDate
     * @param  \DateTime $endDate
     * @return type
     */
    public function getTotal(\DateTime $startDate, \DateTime $endDate, $type = "", $flowDirection = "")
    {
        // First get the EM handle
        // and call the query builder on it
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('sum(r.valueTaxInclude) as totalTaxInclude, sum(r.valueTaxExclude) as totalTaxExclude, sum(r.taxValue) as taxTotal')
                ->from('Madef\ComptaBundle\Entity\Invoice', 'r')
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
            $constDirection = '\Madef\ComptaBundle\Entity\Invoice::FLOW_DIRECTION_' . strtoupper($flowDirection);
            $query
                    ->andWhere('r.flowDirection = :flowDirection')
                    ->setParameter('flowDirection', constant($constDirection));
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Get a list of invoice that can be linked to the account line
     * @param  string                                 $query       search query filter
     * @param  \Madef\ComptaBundle\Entity\AccountLine $accountLine To order by price proximity
     * @return type
     */
    public function findByName($query, $amountTaxInclude)
    {
        // First get the EM handle
        // and call the query builder on it
        $qb = $this->_em->createQueryBuilder();
        $qb->select('i as object, ABS(i.valueTaxInclude - :accountLineValueTaxInclude) as diffAmount')
                ->from('Madef\ComptaBundle\Entity\Invoice', 'i')
                //->innerJoin('i.accountLines', 'l')
                ->where('i.description like :query')
                ->setParameter('query', '%' . $query . '%')
                ->setParameter('accountLineValueTaxInclude', $amountTaxInclude)
                ->orderBy('diffAmount, i.description, i.date desc, i.id');

        //->where('i.valueTaxInclude + 0.2 <= :accountLineValueTaxInclude AND i.valueTaxInclude - 0.2 >= :accountLineValueTaxInclude')
        //->orWhere('i.valueTaxInclude - 0.2 <= :accountLineValueTaxInclude AND i.valueTaxInclude + 0.2 >= :accountLineValueTaxInclude') // Negative values
        //->orWhere('i.valueTaxExclude + 0.2 <= :accountLineValueTaxExclude AND i.valueTaxExclude - 0.2 >= :accountLineValueTaxExclude')
        //->orWhere('i.valueTaxExclude - 0.2 <= :accountLineValueTaxExclude AND i.valueTaxExclude + 0.2 >= :accountLineValueTaxExclude') // Negative values
        //->orWhere('r.date >= :startDate')
        //->andWhere('r. <> :invoiceId')
        //->setParameter('accountLineValueTaxExclude', $accountLine->getValueTaxExclude())
        //->setParameter('endDate', $endDate->format('Y-m-d'));
        return $qb->setMaxResults(5)->getQuery()->getResult();
    }

    /**
     * Get the list of available types
     * @return type
     */
    public function getTypeList()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('DISTINCT(i.type) as type')
                ->from('Madef\ComptaBundle\Entity\Invoice', 'i')
                ->where('i.type IS NOT NULL')
                ->andWhere('i.type <> :empty')
                ->setParameter('empty', '') // There is a best way ?
                ->orderBy('i.type');

        $results = array();
        foreach ($qb->getQuery()->getResult() as $row) {
            $results [] = $row['type'];
        }

        return $results;
    }

}
