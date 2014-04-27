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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class InvoiceController extends Controller
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
            $session->set('startDate', $startDate);
        } elseif (!($startDate = $session->get('startDate'))) { // Si la date n'est pas en session
            $startDate = \DateTime::createFromFormat('Y-m-d', $currentDate->format('Y-m-01'));
        }

        if (!is_null($endDateRow) && !$endDateErrors->count()) { // Si la date est soumise par post et est valide, on l'enregistre en session
            $endDate = \DateTime::createFromFormat('Y-m-d', $endDateRow);
            $session->set('endDate', $endDate);
        } elseif (!($endDate = $session->get('endDate'))) { // Si la date n'est pas en session
            $endDate = \DateTime::createFromFormat('Y-m-d', $currentDate->format('Y-m-t')); // t = Nombre de jours dans le mois
        }

        // Get the resa list
        $invoiceCollection = $this->getDoctrine()->getRepository('\Madef\ComptaBundle\Entity\Invoice')
                ->findByDate($startDate, $endDate, $request->get('type'), $request->get('transmitter'), $request->get('receiver'));

        $total = $this->getDoctrine()->getRepository('\Madef\ComptaBundle\Entity\Invoice')
                ->getTotal($startDate, $endDate, $request->get('type'));

        $typeList = $this->getDoctrine()->getRepository('MadefComptaBundle:Type')
                ->getList();

        $companyList = $this->getDoctrine()->getRepository('MadefComptaBundle:Company')
                ->getList();

        return new Response($this->renderView('MadefComptaBundle:Invoice:list.html.twig', array(
            'startDate' => $startDate->format('j M. Y'),
            'endDate' => $endDate->format('j M. Y'),
            'startDateRow' => $startDate->format('Y-m-d'),
            'endDateRow' => $endDate->format('Y-m-d'),
            'totalTaxRate' => ($total['totalTaxExclude']) ? $total['taxTotal'] / $total['totalTaxExclude'] * 100 : 0,
            'totalTaxValue' => $total['taxTotal'],
            'totalTaxExclude' => $total['totalTaxExclude'],
            'totalTaxInclude' => $total['totalTaxInclude'],
            'section' => 'listInvoice',
            'invoiceCollection' => $invoiceCollection,
            'successMessage' => $session->getFlashBag()->get('successMessage'),
            'typeList' => $typeList,
            'currentType' => $request->get('type'),
            'companyList' => $companyList,
            'currentTransmitter' => $request->get('transmitter'),
            'currentReceiver' => $request->get('receiver')
        )));
    }

    /**
     * @ParamConverter("invoice", class="MadefComptaBundle:Invoice")
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Madef\ComptaBundle\Entity\Invoice         $invoice
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, \Madef\ComptaBundle\Entity\Invoice $invoice)
    {
        $session = $request->getSession();

        if ($invoiceFromSession = $session->get('invoice')) {
            $invoice = $invoiceFromSession;
            $session->remove('invoice');
        }
        if ($errors = $session->get('errors')) {
            $session->remove('errors');
        }

        $typeList = $this->getDoctrine()->getRepository('MadefComptaBundle:Type')
                ->getList();

        $companyList = $this->getDoctrine()->getRepository('MadefComptaBundle:Company')
                ->getList();

        return new Response($this->renderView('MadefComptaBundle:Invoice:edit.html.twig', array(
                    'section' => 'edit',
                    'invoice' => $invoice,
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

        $invoice = new \Madef\ComptaBundle\Entity\Invoice();

        if ($invoiceFromSession = $session->get('invoice')) {
            $invoice = $invoiceFromSession;
            $session->remove('invoice');
        }
        if ($errors = $session->get('errors')) {
            $session->remove('errors');
        }

        $typeList = $this->getDoctrine()->getRepository('MadefComptaBundle:Type')
                ->getList();

        $companyList = $this->getDoctrine()->getRepository('MadefComptaBundle:Company')
                ->getList();

        return new Response($this->renderView('MadefComptaBundle:Invoice:add.html.twig', array(
                    'section' => 'addInvoice',
                    'invoice' => $invoice,
                    'errors' => $errors,
                    'hasErrors' => (bool) count($errors),
                    'typeList' => json_encode($typeList),
                    'companyList' => json_encode($companyList),
        )));
    }

    /**
     * @ParamConverter("invoice", class="MadefComptaBundle:Invoice")
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Madef\ComptaBundle\Entity\Invoice         $invoice
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadAction(Request $request, \Madef\ComptaBundle\Entity\Invoice $invoice)
    {
        $file = $invoice->getFilename(true);

        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', 'text/plain');
        $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, $invoice->getFormatedFilename()
        );

        return $response;
    }

    /**
     * @ParamConverter("startDate", options={"format": "Y-m-d"})
     * @ParamConverter("endDate", options={"format": "Y-m-d"})
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \DateTime                                  $startDate
     * @param  \DateTime                                  $endDate
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadListAction(Request $request, \DateTime $startDate, \DateTime $endDate)
    {
        // Get the resa list
        $invoiceCollection = $this->getDoctrine()->getRepository('\Madef\ComptaBundle\Entity\Invoice')
                ->findByDate($startDate, $endDate, $request->get('type'), $request->get('transmitter'), $request->get('receiver'));

        $zip = new \ZipArchive();

        $suffix = '';
        if ($request->get('type')) {
            $suffix .= '_' . $request->get('type');
        }

        if ($request->get('transmitter')) {
            $suffix .= '_from-' . $request->get('transmitter');
        }

        if ($request->get('receiver')) {
            $suffix .= '_to-' . $request->get('receiver');
        }

        $filename = "Invoice-{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}{$suffix}.zip";
        $path = sys_get_temp_dir() . "/Invoice-{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.zip";
        if ($zip->open($path, \ZIPARCHIVE::OVERWRITE) !== TRUE) {
            error_log('Error: Cannot create ZIP archive');
        }

        foreach ($invoiceCollection as $invoice) {
            if ($invoice->hasFilename()) {
                $zip->addFile($invoice->getFilename(true), $invoice->getFormatedFilename());
            }
        }
        $zip->close();

        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', 'text/plain');
        $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename
        );

        return $response;
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
            $invoice = $em->find('\Madef\ComptaBundle\Entity\Invoice', $id);
            if ($request->get('remove')) {
                $em->remove($invoice);
                $em->flush();
                $session->getFlashBag()->set('successMessage', $this->get('translator')->trans('invoice.removed'));

                return $this->redirect($this->generateUrl('madef_compta_invoice_list'));
            }
        } else {
            $invoice = new \Madef\ComptaBundle\Entity\Invoice();
        }

        $invoice->setDescription($request->get('description'));
        $invoice->setTaxValue($request->get('taxValue'));
        $invoice->setTaxRate($request->get('taxRate'));
        $invoice->setValueTaxInclude($request->get('valueTaxInclude'));
        $invoice->setValueTaxExclude($request->get('valueTaxExclude'));

        $invoice->setType($request->get('type'));

        if ($_FILES['file']['size']) {
            $filename = md5($_FILES['file']['name'] . rand(1, 1000000));

            if (!preg_match('/\.(pdf|png|jpg|jpeg|gif|zip|tgz|tbz2|gz|bz2|ods|odt|csv|doc|docx)$/Usi', $_FILES['file']['name'])) {
                $errors['file'] = $this->get('translator')->trans('file.unavailableFormat');
            } else {
                $directory = realpath(__DIR__ . '/../Resources/download/invoice');
                move_uploaded_file($_FILES['file']['tmp_name'], $directory . '/' . $filename);
                $invoice->setFilename($filename);
                $invoice->setFiletype(strtolower(preg_replace('/^.*\.(.*)$/', '$1', $_FILES['file']['name'])));
            }
        }

        if (!$request->get('date')) {
            $errors['date'] = $this->get('translator')->trans('date.required');
        } else {
            try {
                $date = \DateTime::createFromFormat('Y-m-d', $request->get('date'));
                if (!$date) {
                    throw new \Exception('Bad date format');
                }
                $invoice->setDate($date);
            } catch (\Exception $e) {
                $errors['date'] = $this->get('translator')->trans('date.incorrectFormat');
            }
        }

        if (!count($errors)) {
            $em->persist($invoice);
            $em->flush();

            if ($id) {
                $session->getFlashBag()->set('successMessage', $this->get('translator')->trans('invoice.modified'));
            } else {
                $session->getFlashBag()->set('successMessage', $this->get('translator')->trans('invoice.saved'));
            }

            return $this->redirect($this->generateUrl('madef_compta_invoice_list'));
        } else {
            $session->set('invoice', $invoice);
            $session->set('errors', $errors);
            if ($id) {
                return $this->redirect($this->generateUrl('madef_compta_invoice_edit', array('invoice' => $invoice)));
            } else {
                return $this->redirect($this->generateUrl('madef_compta_invoice_add'));
            }
        }
    }

}
