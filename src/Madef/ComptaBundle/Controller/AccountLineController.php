<?php

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
                ->findByDate($startDate, $endDate, $request->get('type'), $request->get('flowDirection'));

        $solde = $this->getDoctrine()->getRepository('MadefComptaBundle:AccountLine')
                ->getTotal($startDate, false, $request->get('type'), $request->get('flowDirection'));
        $total = $this->getDoctrine()->getRepository('MadefComptaBundle:AccountLine')
                ->getTotal($endDate, true, $request->get('type'), $request->get('flowDirection'));
        $range = $this->getDoctrine()->getRepository('MadefComptaBundle:AccountLine')
                ->getRangeTotal($startDate, $endDate, $request->get('type'), $request->get('flowDirection'));

        $format = '.html';
        if ($request->get('format') === 'csv') {
            $format = '.csv';
        }

        $typeList = $this->getDoctrine()->getRepository('MadefComptaBundle:AccountLine')
                ->getTypeList();

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
                    'flowDirectionList' => array('in' => 'Crédit', 'out' => 'Débit'),
                    'currentFlowDirection' => $request->get('flowDirection'),
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

        $typeList = $this->getDoctrine()->getRepository('MadefComptaBundle:AccountLine')
                ->getTypeList();

        return new Response($this->renderView('MadefComptaBundle:AccountLine:edit.html.twig', array(
                    'section' => 'edit',
                    'accountLine' => $accountLine,
                    'errors' => $errors,
                    'hasErrors' => (bool) count($errors),
                    'typeList' => json_encode($typeList),
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

        $typeList = $this->getDoctrine()->getRepository('MadefComptaBundle:AccountLine')
                ->getTypeList();

        return new Response($this->renderView('MadefComptaBundle:AccountLine:add.html.twig', array(
                    'section' => 'add',
                    'accountLine' => $accountLine,
                    'errors' => $errors,
                    'hasErrors' => (bool) count($errors),
                    'typeList' => json_encode($typeList),
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
                $session->getFlashBag()->set('successMessage', 'Ligne de compte supprimée.');

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
        $accountLine->setType($request->get('type'));

        if (is_null($request->get('flowDirection')) || !in_array($request->get('flowDirection'), array(\Madef\ComptaBundle\Entity\AccountLine::FLOW_DIRECTION_IN, \Madef\ComptaBundle\Entity\AccountLine::FLOW_DIRECTION_OUT))) {
            $errors['flowDirection'] = 'Le sens du flux est obligatoire';
        } else {
            $accountLine->setFlowDirection($request->get('flowDirection'));
        }

        if ($request->get('invoiceId') && $invoice = $em->find('\Madef\ComptaBundle\Entity\Invoice', $request->get('invoiceId'))) {
            $accountLine->setInvoice($invoice);
        } else {
            $accountLine->setInvoice(null);
        }

        if (!$request->get('date')) {
            $errors['date'] = 'Le date est obligatoire';
        } else {
            try {
                $date = \DateTime::createFromFormat('Y-m-d', $request->get('date'));
                if (!$date) {
                    throw new \Exception('Bad date format');
                }
                $accountLine->setDate($date);
            } catch (\Exception $e) {
                $errors['date'] = 'Le format de la date est incorrect';
            }
        }

        if (!count($errors)) {
            $em->persist($accountLine);
            $em->flush();
            if ($id) {
                $session->getFlashBag()->set('successMessage', 'Ligne de compte modifiée.');
            } else {
                $session->getFlashBag()->set('successMessage', 'Ligne de compte enregistrée.');
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
