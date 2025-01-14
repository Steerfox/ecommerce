<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\InvoiceBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sonata\Component\Invoice\InvoiceInterface;
use Sonata\Component\Invoice\InvoiceManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class InvoiceController
{
    /**
     * @var \Sonata\Component\Invoice\InvoiceManagerInterface
     */
    protected $invoiceManager;

    /**
     * @param InvoiceManagerInterface $invoiceManager
     */
    public function __construct(InvoiceManagerInterface $invoiceManager)
    {
        $this->invoiceManager = $invoiceManager;
    }

    /**
     * Returns a paginated list of invoices.
     *
     * @ApiDoc(
     *  resource=true,
     *  output={"class"="Sonata\DatagridBundle\Pager\PagerInterface", "groups"={"sonata_api_read"}}
     * )
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page for invoices list pagination")
     * @QueryParam(name="count", requirements="\d+", default="10", description="Number of invoices by page")
     * @QueryParam(name="orderBy", map=true, requirements="ASC|DESC", nullable=true, strict=true, description="Sort specification for the resultset (key is field, value is direction")
     * @QueryParam(name="status", requirements="\d+", nullable=true, strict=true, description="Filter on invoice statuses")
     *
     * @View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return \Sonata\DatagridBundle\Pager\PagerInterface
     */
    public function getInvoicesAction(ParamFetcherInterface $paramFetcher)
    {
        $supportedCriteria = [
            'status' => '',
        ];

        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('count');
        $sort = $paramFetcher->get('orderBy');
        $criteria = array_intersect_key($paramFetcher->all(), $supportedCriteria);

        foreach ($criteria as $key => $value) {
            if (null === $value) {
                unset($criteria[$key]);
            }
        }

        if (!$sort) {
            $sort = [];
        } elseif (!\is_array($sort)) {
            $sort = [$sort => 'asc'];
        }

        return $this->invoiceManager->getPager($criteria, $page, $limit, $sort);
    }

    /**
     * Retrieves a specific invoice.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="invoice id"}
     *  },
     *  output={"class"="Sonata\Component\Invoice\InvoiceInterface", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when invoice is not found"
     *  }
     * )
     *
     * @View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return InvoiceInterface
     */
    public function getInvoiceAction($id)
    {
        return $this->getInvoice($id);
    }

    /**
     * Retrieves a specific invoice's elements.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="invoice id"}
     *  },
     *  output={"class"="Sonata\Component\Invoice\InvoiceElementInterface", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when invoice is not found"
     *  }
     * )
     *
     * @View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return array
     */
    public function getInvoiceInvoiceelementsAction($id)
    {
        return $this->getInvoice($id)->getInvoiceElements();
    }

    /**
     * Retrieves invoice with id $id or throws an exception if it doesn't exist.
     *
     * @param $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return InvoiceInterface
     */
    protected function getInvoice($id)
    {
        $invoice = $this->invoiceManager->findOneBy(['id' => $id]);

        if (null === $invoice) {
            throw new NotFoundHttpException(sprintf('Invoice (%d) not found', $id));
        }

        return $invoice;
    }
}
