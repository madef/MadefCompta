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
 * @ORM\Entity(repositoryClass="Madef\ComptaBundle\Entity\Repository\InvoiceRepository")
 * @ORM\Table(name="invoice")
 */
class Invoice
{

    const FLOW_DIRECTION_IN = 0;
    const FLOW_DIRECTION_OUT = 1;

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
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $transmitter;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $receiver;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $filename;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $filetype;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $type;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    private $flowDirection;

    /*
     * @ORM\OneToMany(targetEntity="AccountLine", mappedBy="invoice")
     * @var AccountLines
     */
    private $accountLines;

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
     * @return Invoice
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
     * @return Invoice
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
     * @return Invoice
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
     * @return Invoice
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
     * @return Invoice
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
     * @return Invoice
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
     * @param string receiver
     * @return Invoice
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get filename
     *
     * @param bool Full path
     * @return string
     */
    public function getFilename($fullpath = false)
    {
        $directory = '';
        if ($fullpath) {
            $directory = realpath(__DIR__ . '/../Resources/download/invoice') . '/';
        }

        return $directory . $this->filename;
    }

    /**
     * Set filename
     *
     * @param string filename
     * @return Invoice
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Has filename
     *
     * @return bool
     */
    public function hasFilename()
    {
        if (!$this->getFilename()) {
            return false;
        }

        return true;
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
     * @return Invoice
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get flowDirection
     *
     * @return bool
     */
    public function getFlowDirection()
    {
        return $this->flowDirection;
    }

    /**
     * Set flowDirection
     *
     * @param bool flowDirection
     * @return Invoice
     */
    public function setFlowDirection($flowDirection)
    {
        $this->flowDirection = $flowDirection;

        return $this;
    }

    public function isIn()
    {
        if (is_null($this->getFlowDirection()) || $this->getFlowDirection() != self::FLOW_DIRECTION_IN) {
            return false;
        }

        return true;
    }

    public function isOut()
    {
        if (is_null($this->getFlowDirection()) || $this->getFlowDirection() != self::FLOW_DIRECTION_OUT) {
            return false;
        }

        return true;
    }

    /**
     * Get filetype
     *
     * @return string
     */
    public function getFiletype()
    {
        return $this->filetype;
    }

    /**
     * Set filetype
     *
     * @param string filetype
     * @return Invoice
     */
    public function setFiletype($filetype)
    {
        $this->filetype = $filetype;

        return $this;
    }

    /**
     * Get formated filename
     *
     * @return String
     */
    public function getFormatedFilename()
    {
        return $this->getDate('Y-m-d') . '-' . str_replace(' ', '_', $this->getDescription()) . '.' . $this->getFiletype();
    }

}
