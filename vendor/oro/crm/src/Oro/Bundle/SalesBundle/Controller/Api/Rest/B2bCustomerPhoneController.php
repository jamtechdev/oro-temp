<?php

namespace Oro\Bundle\SalesBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\FormBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * API CRUD controller for B2bCustomerPhone entity.
 *
 * @RouteResource("b2bcustomer_phone")
 * @NamePrefix("oro_api_")
 */
class B2bCustomerPhoneController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @Get(requirements={"customerId"="\d+"})
     *
     * @ApiDoc(
     *      description="Get all phones items",
     *      resource=true
     * )
     * @AclAncestor("oro_b2bcustomer_view")
     * @param int $customerId
     * @return Response
     */
    public function cgetAction(int $customerId)
    {
        /** @var B2bCustomer $customer */
        $customer = $this->getB2bCustomerManager()->find($customerId);
        $result = [];
        if (!empty($customer)) {
            $items = $customer->getPhones();

            foreach ($items as $item) {
                $result[] = $this->getPreparedItem($item);
            }
        }

        return new JsonResponse(
            $result,
            empty($customer) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK
        );
    }

    /**
     * REST GET primary phone
     *
     * @param int $customerId
     *
     * @Get(requirements={"customerId"="\d+"})
     *
     * @ApiDoc(
     *      description="Get customer primary phone",
     *      resource=true
     * )
     * @AclAncestor("oro_b2bcustomer_view")
     * @return Response
     */
    public function getPrimaryAction(int $customerId)
    {
        /** @var B2bCustomer $customer */
        $customer = $this->getB2bCustomerManager()->find($customerId);

        if ($customer) {
            $phone = $customer->getPrimaryPhone();
        } else {
            $phone = null;
        }

        $responseData = $phone ? json_encode($this->getPreparedItem($phone)) : '';

        return new Response($responseData, $phone ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * Create entity B2bCustomerPhone
     * oro_api_post_b2bcustomer_phone
     *
     * @return Response
     *
     * @ApiDoc(
     *      description="Create entity",
     *      resource=true,
     *      requirements = {
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     */
    public function postAction()
    {
        $response = $this->handleCreateRequest();

        return $response;
    }

    /**
     * Delete entity B2bCustomerPhone
     * oro_api_delete_b2bcustomer_phone
     *
     * @param int $id
     *
     * @Delete(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Delete B2bCustomerPhone"
     * )
     *
     * @return Response
     */
    public function deleteAction(int $id)
    {
        try {
            $this->getDeleteHandler()->handleDelete($id, $this->getManager());

            return new JsonResponse(["id" => ""]);
        } catch (\Exception $e) {
            return new JsonResponse(["code" => $e->getCode(), "message"=>$e->getMessage() ], $e->getCode());
        }
    }

    protected function getB2bCustomerManager()
    {
        return $this->get('oro_sales.b2bcustomer.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_sales.b2bcustomer_phone.manager.api');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPreparedItem($entity, $resultFields = [])
    {
        $result['id']      = $entity->getId();
        $result['owner']   = (string) $entity->getOwner();
        $result['phone']   = $entity->getPhone();
        $result['primary'] = $entity->isPrimary();

        return $result;
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_sales.form.type.b2bcustomer_phone.handler');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_sales.form.type.b2bcustomer_phone.type');
    }
}
