<?php

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
                ->findByDate($startDate, $endDate, $request->get('type'), $request->get('flowDirection'));

        $total = $this->getDoctrine()->getRepository('\Madef\ComptaBundle\Entity\Invoice')
                ->getTotal($startDate, $endDate, $request->get('type'), $request->get('flowDirection'));

        $typeList = $this->getDoctrine()->getRepository('\Madef\ComptaBundle\Entity\Invoice')
                ->getTypeList();

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
            'flowDirectionList' => array('in' => 'Émises', 'out' => 'Reçues'),
            'currentFlowDirection' => $request->get('flowDirection'),
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

        $typeList = $this->getDoctrine()->getRepository('\Madef\ComptaBundle\Entity\Invoice')
                ->getTypeList();

        return new Response($this->renderView('MadefComptaBundle:Invoice:edit.html.twig', array(
                    'section' => 'edit',
                    'invoice' => $invoice,
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

        $invoice = new \Madef\ComptaBundle\Entity\Invoice();

        if ($invoiceFromSession = $session->get('invoice')) {
            $invoice = $invoiceFromSession;
            $session->remove('invoice');
        }
        if ($errors = $session->get('errors')) {
            $session->remove('errors');
        }

        $typeList = $this->getDoctrine()->getRepository('\Madef\ComptaBundle\Entity\Invoice')
                ->getTypeList();

        return new Response($this->renderView('MadefComptaBundle:Invoice:add.html.twig', array(
                    'section' => 'addInvoice',
                    'invoice' => $invoice,
                    'errors' => $errors,
                    'hasErrors' => (bool) count($errors),
                    'typeList' => json_encode($typeList),
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
                ->findByDate($startDate, $endDate, $request->get('type'), $request->get('flowDirection'));

        $zip = new \ZipArchive();

        $suffix = '';
        if ($request->get('type') || $request->get('flowDirection')) {
            if ($request->get('type')) {
                $suffix .= '_' . $request->get('type');
            }
            if ($request->get('flowDirection') == 'in') {
                $suffix .= '_émises';
            } elseif ($request->get('flowDirection') == 'out') {
                $suffix .= '_reçues';
            }
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
                $session->getFlashBag()->set('successMessage', 'Facture supprimée.');

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

        if (is_null($request->get('flowDirection')) || !in_array($request->get('flowDirection'), array(\Madef\ComptaBundle\Entity\Invoice::FLOW_DIRECTION_IN, \Madef\ComptaBundle\Entity\Invoice::FLOW_DIRECTION_OUT))) {
            $errors['flowDirection'] = 'Le sens du flux est obligatoire';
        } else {
            $invoice->setFlowDirection($request->get('flowDirection'));
        }

        $invoice->setType($request->get('type'));

        if ($_FILES['file']['size']) {
            $filename = md5($_FILES['file']['name'] . rand(1, 1000000));

            if (!preg_match('/\.(pdf|png|jpg|jpeg|gif|zip|tgz|tbz2|gz|bz2|ods|odt|csv|doc|docx)$/Usi', $_FILES['file']['name'])) {
                $errors['file'] = 'Format de fichier non supporté';
            } else {
                $directory = realpath(__DIR__ . '/../Resources/download/invoice');
                move_uploaded_file($_FILES['file']['tmp_name'], $directory . '/' . $filename);
                $invoice->setFilename($filename);
                $invoice->setFiletype(strtolower(preg_replace('/^.*\.(.*)$/', '$1', $_FILES['file']['name'])));
            }
        }

        if (!$request->get('date')) {
            $errors['date'] = 'Le date est obligatoire';
        } else {
            try {
                $date = \DateTime::createFromFormat('Y-m-d', $request->get('date'));
                if (!$date) {
                    throw new \Exception('Bad date format');
                }
                $invoice->setDate($date);
            } catch (\Exception $e) {
                $errors['date'] = 'Le format de la date est incorrect';
            }
        }

        if (!count($errors)) {
            $em->persist($invoice);
            $em->flush();

            if ($id) {
                $session->getFlashBag()->set('successMessage', 'Facture modifiée.');
            } else {
                $session->getFlashBag()->set('successMessage', 'Facture enregistrée.');
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
