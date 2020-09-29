<?php

namespace Oro\Bundle\MarketingListBundle\Controller;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Form\Handler\MarketingListHandler;
use Oro\Bundle\MarketingListBundle\Form\Type\MarketingListType;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\Manager;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD controller for MarketingList entity
 *
 * @Route("/marketing-list")
 */
class MarketingListController extends AbstractController
{
    /**
     * @Route("/", name="oro_marketing_list_index")
     * @AclAncestor("oro_marketing_list_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => MarketingList::class
        ];
    }

    /**
     * @Route("/view/{id}", name="oro_marketing_list_view", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Acl(
     *      id="oro_marketing_list_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroMarketingListBundle:MarketingList"
     * )
     * @Template
     *
     * @param MarketingList $entity
     *
     * @return array
     */
    public function viewAction(MarketingList $entity)
    {
        $this->checkMarketingList($entity);

        $entityConfig = $this->getEntityProvider()->getEntity($entity->getEntity());

        return [
            'entity'   => $entity,
            'config'   => $entityConfig,
            'gridName' => ConfigurationProvider::GRID_PREFIX . $entity->getId()
        ];
    }

    /**
     * @Route("/create", name="oro_marketing_list_create")
     * @Template("OroMarketingListBundle:MarketingList:update.html.twig")
     * @Acl(
     *      id="oro_marketing_list_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroMarketingListBundle:MarketingList"
     * )
     */
    public function createAction()
    {
        return $this->update(new MarketingList());
    }

    /**
     * @Route("/update/{id}", name="oro_marketing_list_update", requirements={"id"="\d+"}, defaults={"id"=0})
     *
     * @Template
     * @Acl(
     *      id="oro_marketing_list_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroMarketingListBundle:MarketingList"
     * )
     *
     * @param MarketingList $entity
     *
     * @return array
     */
    public function updateAction(MarketingList $entity)
    {
        $this->checkMarketingList($entity);

        return $this->update($entity);
    }

    /**
     * @param MarketingList $entity
     *
     * @return array
     */
    protected function update(MarketingList $entity)
    {
        $form = $this->get('form.factory')
            ->createNamed('oro_marketing_list_form', MarketingListType::class);

        $response = $this->get(UpdateHandlerFacade::class)->update(
            $entity,
            $form,
            $this->get(TranslatorInterface::class)->trans('oro.marketinglist.entity.saved'),
            null,
            $this->get(MarketingListHandler::class)
        );

        if (is_array($response)) {
            return array_merge(
                $response,
                [
                    'entities' => $this->getEntityProvider()->getEntities(),
                    'metadata' => $this->get(Manager::class)->getMetadata('segment')
                ]
            );
        }

        return $response;
    }

    /**
     * @param MarketingList $marketingList
     */
    protected function checkMarketingList(MarketingList $marketingList)
    {
        if ($marketingList->getEntity() &&
            !$this->getFeatureChecker()->isResourceEnabled($marketingList->getEntity(), 'entities')
        ) {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @return FeatureChecker
     */
    protected function getFeatureChecker()
    {
        return $this->get(FeatureChecker::class);
    }

    /**
     * @return EntityProvider
     */
    private function getEntityProvider()
    {
        return $this->get('oro_marketing_list.entity_provider');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'oro_marketing_list.entity_provider' => EntityProvider::class,
                TranslatorInterface::class,
                FeatureChecker::class,
                UpdateHandlerFacade::class,
                ValidatorInterface::class,
                Manager::class,
                MarketingListHandler::class,
            ]
        );
    }
}
