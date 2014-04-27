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

namespace Madef\ComptaBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class AccountLineController extends Controller
{

    /**
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        $session = $request->getSession();
        $validator = $this->get('validator');

        $startDateRow = $request->get('start_date');
        $endDateRow = $request->get('end_date');
        $startDateErrors = $validator->validateValue($startDateRow, new Assert\Date());
        $endDateErrors = $validator->validateValue($endDateRow, new Assert\Date());

        $currentDate = new \DateTime();

        if (!is_null($startDateRow) && !$startDateErrors->count()) { // Si la date est soumise par post et est valide, on l'enregistre en session
            $startDate = \DateTime::createFromFormat('Y-m-d', $startDateRow);
            // @TODO Voir pourquoi on perd la session
            $session->set('startDate', $startDate);
        } elseif (!($startDate = $session->get('startDate'))) { // Si la date n'est pas en session
            $startDate = \DateTime::createFromFormat('Y-m-d', $currentDate->format('Y-m-01'));
        }

        if (!is_null($endDateRow) && !$endDateErrors->count()) { // Si la date est soumise par post et est valide, on l'enregistre en session
            $endDate = \DateTime::createFromFormat('Y-m-d', $endDateRow);
            // @TODO Voir pourquoi on perd la session
            $session->set('endDate', $endDate);
        } elseif (!($endDate = $session->get('endDate'))) { // Si la date n'est pas en session
            $endDate = \DateTime::createFromFormat('Y-m-d', $currentDate->format('Y-m-t')); // t = Nombre de jours dans le mois
        }

        // Get the resa list
        $accountLineCollection = $this->getDoctrine()->getRepository('MadefComptaBundle:AccountLine')
                ->findByDate($startDate, $endDate, $request->get('type'), $request->get('transmitter'), $request->get('receiver'));

        $solde = $this->getDoctrine()->getRepository('MadefComptaBundle:AccountLine')
                ->getTotal($startDate, false, $request->get('type'));
        $total = $this->getDoctrine()->getRepository('MadefComptaBundle:AccountLine')
                ->getTotal($endDate, true, $request->get('type'));
        $range = $this->getDoctrine()->getRepository('MadefComptaBundle:AccountLine')
                ->getRangeTotal($startDate, $endDate, $request->get('type'));

        $format = '.html';
        if ($request->get('format') === 'csv') {
            $format = '.csv';
        }

        $typeList = $this->getDoctrine()->getRepository('MadefComptaBundle:Type')
                ->getList();

        $companyList = $this->getDoctrine()->getRepository('MadefComptaBundle:Company')
                ->getList();

        $return = $this->renderView('MadefComptaBundle:AccountLine:list' . $format . '.twig', array(
                    'startDate' => $startDate->format('j M. Y'),
                    'endDate' => $endDate->format('j M. Y'),
                    'startDateRow' => $startDate->format('Y-m-d'),
                    'endDateRow' => $endDate->format('Y-m-d'),
                    'soldeTaxRate' => ($solde['totalTaxExclude']) ? $solde['taxTotal'] / $solde['totalTaxExclude'] * 100 : 0,
                    'soldeTaxValue' => $solde['taxTotal'],
                    'soldeTaxExclude' => $solde['totalTaxExclude'],
                    'soldeTaxInclude' => $solde['totalTaxInclude'],
                    'totalTaxRate' => ($total['totalTaxExclude']) ? $total['taxTotal'] / $total['totalTaxExclude'] * 100 : 0,
                    'totalTaxValue' => $total['taxTotal'],
                    'totalTaxExclude' => $total['totalTaxExclude'],
                    'totalTaxInclude' => $total['totalTaxInclude'],
                    'rangeTaxRate' => ($range['totalTaxExclude']) ? $range['taxTotal'] / $range['totalTaxExclude'] * 100 : 0,
                    'rangeTaxValue' => $range['taxTotal'],
                    'rangeTaxExclude' => $range['totalTaxExclude'],
                    'rangeTaxInclude' => $range['totalTaxInclude'],
                    'section' => 'list',
                    'accountLineCollection' => $accountLineCollection,
                    'successMessage' => $session->getFlashBag()->get('successMessage'),
                    'typeList' => $typeList,
                    'currentType' => $request->get('type'),
                    'companyList' => $companyList,
                    'currentTransmitter' => $request->get('transmitter'),
                    'currentReceiver' => $request->get('receiver'),
        ));

        $response = new Response($return);

        if ($request->get('format') === 'csv') {
                $response->headers->set('Content-Type', 'text/csv');
                $response->headers->set('Content-length', strlen($return));
                $d = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    'file-download.csv'
                );
                $response->headers->set('Content-Disposition', $d);
        }

        return $response;
    }

    /**
     * @ParamConverter("accountLine", class="MadefComptaBundle:AccountLine")
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Madef\ComptaBundle\Entity\AccountLine     $accountLine
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, \Madef\ComptaBundle\Entity\AccountLine $accountLine)
    {
        $session = $request->getSession();

        if (!$accountLine) {
            $app->abort(404, "Page inconue");
        }

        if ($accountLineFromSession = $session->get('accountLine')) {
            $accountLine = $accountLineFromSession;
            $session->remove('accountLine');
        }
        if ($errors = $session->get('errors')) {
            $session->remove('errors');
        }

        $typeList = $this->getDoctrine()->getRepository('MadefComptaBundle:Type')
                ->getList();

        $companyList = $this->getDoctrine()->getRepository('MadefComptaBundle:Company')
                ->getList();

        return new Response($this->renderView('MadefComptaBundle:AccountLine:edit.html.twig', array(
                    'section' => 'edit',
                    'accountLine' => $accountLine,
                    'errors' => $errors,
                    'hasErrors' => (bool) count($errors),
                    'typeList' => json_encode($typeList),
                    'companyList' => json_encode($companyList),
        )));
    }

    /**
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        $session = $request->getSession();

        $accountLine = new \Madef\ComptaBundle\Entity\AccountLine();

        if ($accountLineFromSession = $session->get('accountLine')) {
            $accountLine = $accountLineFromSession;
            $session->remove('accountLine');
        }
        if ($errors = $session->get('errors')) {
            $session->remove('errors');
        }

        $typeList = $this->getDoctrine()->getRepository('MadefComptaBundle:Type')
                ->getList();

        $companyList = $this->getDoctrine()->getRepository('MadefComptaBundle:Company')
                ->getList();

        return new Response($this->renderView('MadefComptaBundle:AccountLine:add.html.twig', array(
                    'section' => 'add',
                    'accountLine' => $accountLine,
                    'errors' => $errors,
                    'hasErrors' => (bool) count($errors),
                    'typeList' => json_encode($typeList),
                    'companyList' => json_encode($companyList),
        )));
    }

    /**
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function saveAction(Request $request)
    {
        $session = $request->getSession();

        $errors = array();

        $em = $this->getDoctrine()->getEntityManager();
        if ($id = $request->get('id')) {
            $accountLine = $em->find('MadefComptaBundle:AccountLine', $id);
            if ($request->get('remove')) {
                $accountLine->setInvoice(null);
                $em->remove($accountLine);
                $em->flush();
                $session->getFlashBag()->set('successMessage', $this->get('translator')->trans('account.removed'));

                return $this->redirect($this->generateUrl('madef_compta_accountline_list'));
            }
        } else {
            $accountLine = new \Madef\ComptaBundle\Entity\AccountLine();
        }

        $accountLine->setDescription($request->get('description'));
        $accountLine->setTaxValue($request->get('taxValue'));
        $accountLine->setTaxRate($request->get('taxRate'));
        $accountLine->setValueTaxInclude($request->get('valueTaxInclude'));
        $accountLine->setValueTaxExclude($request->get('valueTaxExclude'));

        $type = $request->get('type');
        if (empty($type)) {
            $accountLine->setType(null);
        } else {
            $repository = $this->getDoctrine()->getRepository('MadefComptaBundle:Type');
            $typeObject = $repository->findOneByName($type);
            if (is_null($typeObject)) {
                $typeObject = new \Madef\ComptaBundle\Entity\Type();
                $typeObject->setName($type);
                $em->persist($typeObject);
            }
            $accountLine->setType($typeObject);
        }

        $receiver = $request->get('receiver');
        if (empty($receiver)) {
            $accountLine->setReceiver(null);
        } else {
            $repository = $this->getDoctrine()->getRepository('MadefComptaBundle:Company');
            $receiverObject = $repository->findOneByName($receiver);
            if (is_null($receiverObject)) {
                $receiverObject = new \Madef\ComptaBundle\Entity\Company();
                $receiverObject->setName($receiver);
                $em->persist($receiverObject);
            }
            $accountLine->setReceiver($receiverObject);
        }

        $transmitter = $request->get('transmitter');
        if (empty($transmitter)) {
            $accountLine->setTransmitter(null);
        } else {
            $repository = $this->getDoctrine()->getRepository('MadefComptaBundle:Company');
            $transmitterObject = $repository->findOneByName($transmitter);
            if (is_null($transmitterObject)) {
                $transmitterObject = new \Madef\ComptaBundle\Entity\Company();
                $transmitterObject->setName($transmitter);
                $em->persist($transmitterObject);
            }
            $accountLine->setTransmitter($transmitterObject);
        }

        if ($request->get('invoiceId') && $invoice = $em->find('\Madef\ComptaBundle\Entity\Invoice', $request->get('invoiceId'))) {
            $accountLine->setInvoice($invoice);
        } else {
            $accountLine->setInvoice(null);
        }

        if (!$request->get('date')) {
            $errors['date'] = $this->get('translator')->trans('date.required');
        } else {
            try {
                $date = \DateTime::createFromFormat('Y-m-d', $request->get('date'));
                if (!$date) {
                    throw new \Exception('Bad date format');
                }
                $accountLine->setDate($date);
            } catch (\Exception $e) {
                $errors['date'] = $this->get('translator')->trans('date.incorrectFormat');
            }
        }

        if (!count($errors)) {
            $em->persist($accountLine);
            $em->flush();
            if ($id) {
                $session->getFlashBag()->set('successMessage', $this->get('translator')->trans('account.modified'));
            } else {
                $session->getFlashBag()->set('successMessage', $this->get('translator')->trans('account.saved'));
            }

            return $this->redirect($this->generateUrl('madef_compta_accountline_list'));
        } else {
            $session->set('accountLine', $accountLine);
            $session->set('errors', $errors);
            if ($id) {
                return $this->redirect($this->generateUrl('madef_compta_accountline_edit', array('accountLine' => $accountLine)));
            } else {
                return $this->redirect($this->generateUrl('madef_compta_accountline_add'));
            }
        }
    }

    /**
     * Get all potential invoices linked to the accountLine
     * @param  \Symfony\Component\HttpFoundation\Request          $request
     * @param  \Silex\Application                                 $app
     * @param  \Madef\ComptaBundle\Entity\AccountLine\AccountLine $accountLine
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getLinkedInvoiceListAction(Request $request, $accountLine = null)
    {
        if (!$accountLine) {
            $accountLine = new \Madef\ComptaBundle\Entity\AccountLine();
        }

        $results = array();

        $invoiceCollection = $this->getDoctrine()->getRepository('MadefComptaBundle:Invoice')
                ->findByName($request->get('query'), $request->get('amountTaxInclude'));

        foreach ($invoiceCollection as $invoice) {
            $results[] = $invoice['object']->getId() . ' - ' . number_format($invoice['object']->getValueTaxInclude(), 2, ',', "'") . ' €' . ' - ' . $invoice['object']->getDescription() . ' du ' . $invoice['object']->getDate()->format('j M. Y');
        }

        return new Response(json_encode($results));
    }

}
