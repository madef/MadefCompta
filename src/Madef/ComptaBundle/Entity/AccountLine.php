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

namespace Madef\ComptaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Madef\ComptaBundle\Entity\Repository\AccountLineRepository")
 * @ORM\Table(name="account_line")
 */
class AccountLine
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @var float
     */
    private $valueTaxInclude;

    /**
     * @ORM\Column(type="float")
     * @var float
     */
    private $valueTaxExclude;

    /**
     * @ORM\Column(type="float")
     * @var float
     */
    private $taxValue;

    /**
     * @ORM\Column(type="float")
     * @var float
     */
    private $taxRate;

    /**
     * @ORM\Column(type="date")
     * @var string
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="Company")
     * @ORM\JoinColumn(name="transmitter_id", referencedColumnName="id")
     * @var string
     */
    private $transmitter;

    /**
     * @ORM\ManyToOne(targetEntity="Company")
     * @ORM\JoinColumn(name="receiver_id", referencedColumnName="id")
     * @var string
     */
    private $receiver;

    /**
     * @ORM\ManyToOne(targetEntity="Invoice", inversedBy="accountLines")
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="id")
     * @var Invoice
     */
    private $invoice;

    /**
     * @ORM\ManyToOne(targetEntity="Type")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     * @var string
     */
    private $type;

    public function __construct()
    {

    }

    public function __toString()
    {
        return strval($this->id);
    }

    /**
     * Get date
     *
     * @param string format
     * @return string|datetime
     */
    public function getDate($format = null)
    {
        if (is_null($format)) {
            return $this->date;
        }
        if (!empty($this->date)) {
            return $this->date->format($format);
        }

        return null;
    }

    /**
     * Set date
     *
     * @param DateTime incomeDate
     * @return Reservation
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get id
     *
     * @return
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param  id
     * @return AccountLine
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get valueTaxInclude
     *
     * @return float
     */
    public function getValueTaxInclude()
    {
        return $this->valueTaxInclude;
    }

    /**
     * Set valueTaxInclude
     *
     * @param float valueTaxInclude
     * @return AccountLine
     */
    public function setValueTaxInclude($valueTaxInclude)
    {
        $this->valueTaxInclude = $valueTaxInclude;

        return $this;
    }

    /**
     * Get valueTaxExclude
     *
     * @return float
     */
    public function getValueTaxExclude()
    {
        return $this->valueTaxExclude;
    }

    /**
     * Set valueTaxExclude
     *
     * @param float valueTaxExclude
     * @return AccountLine
     */
    public function setValueTaxExclude($valueTaxExclude)
    {
        $this->valueTaxExclude = $valueTaxExclude;

        return $this;
    }

    /**
     * Get taxValue
     *
     * @return float
     */
    public function getTaxValue()
    {
        return $this->taxValue;
    }

    /**
     * Set taxValue
     *
     * @param float taxValue
     * @return AccountLine
     */
    public function setTaxValue($taxValue)
    {
        $this->taxValue = $taxValue;

        return $this;
    }

    /**
     * Get taxRate
     *
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * Set taxRate
     *
     * @param float taxRate
     * @return AccountLine
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string description
     * @return AccountLine
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get transmitter
     *
     * @return string
     */
    public function getTransmitter()
    {
        return $this->transmitter;
    }

    /**
     * Set transmitter
     *
     * @param string transmitter
     * @return Invoice
     */
    public function setTransmitter($transmitter)
    {
        $this->transmitter = $transmitter;

        return $this;
    }

    /**
     * Get receiver
     *
     * @return string
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set receiver
     *
     * @param  string      $receiver
     * @return AccountLine
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string type
     * @return AccountLine
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set invoice
     *
     * @param  \Madef\ComptaBundle\Entity\Invoice $invoice
     * @return AccountLine
     */
    public function setInvoice(\Madef\ComptaBundle\Entity\Invoice $invoice = null)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get invoice
     *
     * @return \Madef\ComptaBundle\Entity\Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }
}
