<?php

/*
 * Copyright (c) 2014, de Flotte Maxence <maxence@deflotte.fr>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Madef\ComptaBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class AccountLineRepository extends EntityRepository
{

    /**
     * Get account lines between two dates
     * @param  \DateTime $startDate
     * @param  \DateTime $endDate
     * @return type
     */
    public function findByDate(\DateTime $startDate, \DateTime $endDate, $type = "", $transmitter = "", $receiver = "")
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
            $repository = $this->_em->getRepository('MadefComptaBundle:Type');
            $typeObject = $repository->findOneByName($type);

            $query
                    ->andWhere('r.type = :type')
                    ->setParameter('type', $typeObject);
        }

        if (!empty($transmitter)) {
            $repository = $this->_em->getRepository('MadefComptaBundle:Company');
            $transmitterObject = $repository->findOneByName($transmitter);

            $query
                    ->andWhere('r.transmitter = :transmitter')
                    ->setParameter('transmitter', $transmitterObject);
        }

        if (!empty($receiver)) {
            $repository = $this->_em->getRepository('MadefComptaBundle:Company');
            $receiverObject = $repository->findOneByName($receiver);

            $query
                    ->andWhere('r.receiver = :receiver')
                    ->setParameter('receiver', $receiverObject);
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
