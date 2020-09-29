<?php

namespace Oro\Bundle\PricingBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\PricingBundle\Entity\CombinedPriceListActivationRule;
use Oro\Bundle\PricingBundle\Entity\PriceList;
use Oro\Bundle\PricingBundle\Entity\PriceListSchedule;
use Oro\Bundle\PricingBundle\Entity\Repository\CombinedPriceListActivationRuleRepository;
use Oro\Bundle\PricingBundle\Entity\Repository\PriceListRepository;
use Oro\Bundle\PricingBundle\Entity\Repository\PriceListScheduleRepository;
use Oro\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadPriceListSchedules;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class PriceListScheduleTest extends RestJsonApiTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadPriceListSchedules::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequestDataFolderName()
    {
        return parent::getRequestDataFolderName() . DIRECTORY_SEPARATOR . 'price_list_schedule';
    }

    /**
     * {@inheritdoc}
     */
    protected function getResponseDataFolderName()
    {
        return parent::getResponseDataFolderName() . DIRECTORY_SEPARATOR . 'price_list_schedule';
    }

    /**
     * @return PriceListRepository
     */
    private function getPriceListRepository(): PriceListRepository
    {
        return $this->getEntityManager()->getRepository(PriceList::class);
    }

    /**
     * @return PriceListScheduleRepository
     */
    private function getSchedulesRepository(): PriceListScheduleRepository
    {
        return $this->getEntityManager()->getRepository(PriceListSchedule::class);
    }

    /**
     * @return CombinedPriceListActivationRuleRepository
     */
    private function getCombinedPriceListActivationRuleRepository(): CombinedPriceListActivationRuleRepository
    {
        return $this->getEntityManager()->getRepository(CombinedPriceListActivationRule::class);
    }

    /**
     * @param \DateTime $activateAt
     * @param \DateTime $deactivateAt
     * @param PriceList $priceList
     *
     * @return Response
     */
    private function sendCreateScheduleRequest(
        \DateTime $activateAt,
        \DateTime $deactivateAt,
        PriceList $priceList
    ): Response {
        return $this->post(
            ['entity' => 'pricelistschedules'],
            [
                'data' => [
                    'type'          => 'pricelistschedules',
                    'attributes'    => [
                        'activeAt'     => $activateAt->format('c'),
                        'deactivateAt' => $deactivateAt->format('c')
                    ],
                    'relationships' => [
                        'priceList' => [
                            'data' => [
                                'type' => 'pricelists',
                                'id'   => (string)$priceList->getId()
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * @param PriceListSchedule $schedule
     * @param \DateTime         $activateAt
     * @param \DateTime         $deactivateAt
     *
     * @return Response
     */
    private function sendUpdateScheduleRequest(
        PriceListSchedule $schedule,
        \DateTime $activateAt,
        \DateTime $deactivateAt
    ): Response {
        return $this->patch(
            ['entity' => 'pricelistschedules', 'id' => $schedule->getId()],
            [
                'data' =>
                    [
                        'type'       => 'pricelistschedules',
                        'id'         => (string)$schedule->getId(),
                        'attributes' => [
                            'activeAt'     => $activateAt->format('c'),
                            'deactivateAt' => $deactivateAt->format('c')
                        ]
                    ]
            ]
        );
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'pricelistschedules', 'id' => '<toString(@schedule.3->id)>']
        );

        $this->assertResponseContains('price_list_schedules_get.yml', $response);
    }

    public function testGetList()
    {
        $response = $this->cget(['entity' => 'pricelistschedules']);

        $this->assertResponseContains('price_list_schedules_get_list.yml', $response);
    }

    public function testGetListByPriceListFilter()
    {
        $response = $this->cget(
            ['entity' => 'pricelistschedules'],
            ['filter' => ['priceList' => '@price_list_1->id']]
        );

        $this->assertResponseContains('price_list_schedules_get_list_by_pl_filter.yml', $response);
    }

    public function testCreate()
    {
        $data = $this->getRequestData('price_list_schedules_create.yml');
        $response = $this->post(
            ['entity' => 'pricelistschedules'],
            $data
        );

        $this->assertResponseContains($data, $response);
    }

    public function testUpdate()
    {
        $scheduleId = $this->getReference('schedule.1')->getId();

        $this->patch(
            ['entity' => 'pricelistschedules', 'id' => (string)$scheduleId],
            'price_list_schedules_update.yml'
        );

        /** @var PriceListSchedule $schedule */
        $schedule = $this->getEntityManager()->find(PriceListSchedule::class, $scheduleId);
        self::assertEquals(new \DateTime('2017-04-12T14:11:39Z'), $schedule->getActiveAt());
        self::assertEquals(new \DateTime('2017-04-24T14:11:39Z'), $schedule->getDeactivateAt());
    }

    public function testDelete()
    {
        $scheduleId = $this->getReference('schedule.1')->getId();

        $this->delete(['entity' => 'pricelistschedules', 'id' => $scheduleId]);

        $this->assertNull($this->getSchedulesRepository()->find($scheduleId));
    }

    public function testDeleteList()
    {
        $priceListId = $this->getReference('price_list_1')->getId();

        $this->cdelete(
            ['entity' => 'pricelistschedules'],
            ['filter' => ['priceList' => $priceListId]]
        );

        self::assertCount(0, $this->getSchedulesRepository()->findBy(['priceList' => $priceListId]));
    }

    public function testGetSubResourceForPriceList()
    {
        $response = $this->getSubresource(
            ['entity' => 'pricelistschedules', 'id' => '<toString(@schedule.1->id)>', 'association' => 'priceList']
        );

        $this->assertResponseContains('price_list_schedules_get_sub_resources_pl.yml', $response);
    }

    public function testGetRelationshipForPriceList()
    {
        $response = $this->getRelationship(
            ['entity' => 'pricelistschedules', 'id' => '<toString(@schedule.1->id)>', 'association' => 'priceList']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    'type' => 'pricelists',
                    'id'   => '<toString(@price_list_1->id)>'
                ]
            ],
            $response
        );
    }

    public function testUpdateSchedulesIntersect()
    {
        $schedule = $this->getReference('schedule.1');

        $response = $this->patch(
            ['entity' => 'pricelistschedules', 'id' => '<toString(@schedule.2->id)>'],
            [
                'data' => [
                    'type'       => 'pricelistschedules',
                    'id'         => '<toString(@schedule.2->id)>',
                    'attributes' => [
                        'activeAt'     => $schedule->getActiveAt()->format('c'),
                        'deactivateAt' => $schedule->getDeactivateAt()->format('c')
                    ]
                ]
            ],
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'schedule intervals intersection constraint',
                'detail' => 'Price list schedule segments should not intersect'
            ],
            $response
        );
    }

    public function testUpdateSchedulesIntersectB()
    {
        $schedule = $this->getReference('schedule.1');

        $response = $this->patch(
            ['entity' => 'pricelistschedules', 'id' => '<toString(@schedule.2->id)>'],
            [
                'data' => [
                    'type'       => 'pricelistschedules',
                    'id'         => '<toString(@schedule.2->id)>',
                    'attributes' => [
                        'deactivateAt' => $schedule->getDeactivateAt()
                            ->add(new \DateInterval('P1D'))
                            ->format('c')
                    ]
                ]
            ],
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'schedule intervals intersection constraint',
                'detail' => 'Price list schedule segments should not intersect'
            ],
            $response
        );
    }

    public function testCreateSchedulesIntersect()
    {
        $data = $this->getRequestData('price_list_schedules_create.yml');
        $response = $this->post(
            ['entity' => 'pricelistschedules'],
            $data
        );
        $this->assertResponseContains($data, $response);

        $response = $this->post(['entity' => 'pricelistschedules'], $data, [], false);
        $this->assertResponseValidationError(
            [
                'title'  => 'schedule intervals intersection constraint',
                'detail' => 'Price list schedule segments should not intersect'
            ],
            $response
        );
    }

    public function testCombinedPriceListBuildOnScheduleCreate()
    {
        $priceList = $this->getPriceListRepository()->getDefault();

        $createdActivationRules = $this->getCombinedPriceListActivationRuleRepository()->findAll();
        $this->assertCount(0, $createdActivationRules);

        $this->sendCreateScheduleRequest(
            new \DateTime(),
            new \DateTime('tomorrow'),
            $priceList
        );

        $createdActivationRules = $this->getCombinedPriceListActivationRuleRepository()->findAll();
        $this->assertGreaterThan(0, count($createdActivationRules));
    }

    public function testCombinedPriceListBuildOnScheduleUpdate()
    {
        $priceList = $this->getPriceListRepository()->getDefault();

        $schedule = new PriceListSchedule(new \DateTime(), new \DateTime('tomorrow'));
        $schedule->setPriceList($priceList);
        $this->getEntityManager()->persist($schedule);
        $this->getEntityManager()->flush();

        $createdActivationRules = $this->getCombinedPriceListActivationRuleRepository()->findAll();
        $this->assertCount(0, $createdActivationRules);

        $this->sendUpdateScheduleRequest(
            $schedule,
            new \DateTime('+2 days'),
            new \DateTime('+3 days')
        );

        $createdActivationRules = $this->getCombinedPriceListActivationRuleRepository()->findAll();
        $this->assertGreaterThan(0, count($createdActivationRules));
    }

    public function testCombinedPriceListBuildOnScheduleDelete()
    {
        $priceList = $this->getPriceListRepository()->getDefault();

        $schedule = new PriceListSchedule(new \DateTime(), new \DateTime('tomorrow'));
        $schedule->setPriceList($priceList);

        $scheduleTwo = new PriceListSchedule(new \DateTime('+2 days'), new \DateTime('+3 days'));
        $scheduleTwo->setPriceList($priceList);

        $this->getEntityManager()->persist($schedule);
        $this->getEntityManager()->persist($scheduleTwo);
        $this->getEntityManager()->flush();

        $createdActivationRules = $this->getCombinedPriceListActivationRuleRepository()->findAll();
        $this->assertCount(0, $createdActivationRules);

        $this->delete(['entity' => 'pricelistschedules', 'id' => $scheduleTwo->getId()]);

        $createdActivationRules = $this->getCombinedPriceListActivationRuleRepository()->findAll();
        $this->assertGreaterThan(0, count($createdActivationRules));
    }

    public function testCombinedPriceListBuildOnScheduleListDelete()
    {
        $priceList = $this->getPriceListRepository()->getDefault();

        $schedule = new PriceListSchedule(new \DateTime(), new \DateTime('tomorrow'));
        $schedule->setPriceList($priceList);

        $scheduleTwo = new PriceListSchedule(new \DateTime('+2 days'), new \DateTime('+3 days'));
        $scheduleTwo->setPriceList($priceList);

        $this->getEntityManager()->persist($schedule);
        $this->getEntityManager()->persist($scheduleTwo);
        $this->getEntityManager()->flush();

        $createdActivationRules = $this->getCombinedPriceListActivationRuleRepository()->findAll();
        $this->assertCount(0, $createdActivationRules);

        $this->cdelete(
            ['entity' => 'pricelistschedules'],
            ['filter' => ['id' => $scheduleTwo->getId()]]
        );

        $createdActivationRules = $this->getCombinedPriceListActivationRuleRepository()->findAll();
        $this->assertGreaterThan(0, count($createdActivationRules));
    }

    public function testCombinedPriceListBuildOnScheduleCreateAsIncludedData()
    {
        $priceList = $this->getPriceListRepository()->getDefault();

        $createdActivationRules = $this->getCombinedPriceListActivationRuleRepository()->findAll();
        $this->assertCount(0, $createdActivationRules);

        $this->patch(
            ['entity' => 'pricelists', 'id' => (string)$priceList->getId()],
            [
                'data'     => [
                    'type'          => 'pricelists',
                    'id'            => (string)$priceList->getId(),
                    'relationships' => [
                        'schedules' => [
                            'data' => [
                                ['type' => 'pricelistschedules', 'id' => 'new_schedule']
                            ]
                        ]
                    ]
                ],
                'included' => [
                    [
                        'type'       => 'pricelistschedules',
                        'id'         => 'new_schedule',
                        'attributes' => [
                            'activeAt'     => (new \DateTime())->format('c'),
                            'deactivateAt' => (new \DateTime('tomorrow'))->format('c')
                        ]
                    ]
                ]
            ]
        );

        $createdActivationRules = $this->getCombinedPriceListActivationRuleRepository()->findAll();
        $this->assertGreaterThan(0, count($createdActivationRules));
    }

    public function testCombinedPriceListBuildOnScheduleUpdateAsIncludedData()
    {
        $priceList = $this->getPriceListRepository()->getDefault();

        $schedule = new PriceListSchedule(new \DateTime(), new \DateTime('tomorrow'));
        $schedule->setPriceList($priceList);
        $this->getEntityManager()->persist($schedule);
        $this->getEntityManager()->flush();

        $createdActivationRules = $this->getCombinedPriceListActivationRuleRepository()->findAll();
        $this->assertCount(0, $createdActivationRules);

        $this->patch(
            ['entity' => 'pricelists', 'id' => (string)$priceList->getId()],
            [
                'data'     => [
                    'type'          => 'pricelists',
                    'id'            => (string)$priceList->getId(),
                    'relationships' => [
                        'schedules' => [
                            'data' => [
                                ['type' => 'pricelistschedules', 'id' => (string)$schedule->getId()]
                            ]
                        ]
                    ]
                ],
                'included' => [
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'pricelistschedules',
                        'id'         => (string)$schedule->getId(),
                        'attributes' => [
                            'activeAt'     => (new \DateTime('+2 days'))->format('c'),
                            'deactivateAt' => (new \DateTime('+3 days'))->format('c')
                        ]
                    ]
                ]
            ]
        );

        $this->sendUpdateScheduleRequest(
            $schedule,
            new \DateTime('+2 days'),
            new \DateTime('+3 days')
        );

        $createdActivationRules = $this->getCombinedPriceListActivationRuleRepository()->findAll();
        $this->assertGreaterThan(0, count($createdActivationRules));
    }

    public function testCreateUpdatesScheduleContains()
    {
        /** @var PriceList $priceList */
        $priceList = $this->getReference('price_list_5');
        $priceListId = $priceList->getId();

        $this->assertFalse($priceList->isContainSchedule());

        $this->sendCreateScheduleRequest(new \DateTime(), new \DateTime(), $priceList);

        /** @var PriceList $priceList */
        $priceList = $this->getEntityManager()->find(PriceList::class, $priceListId);
        $this->assertTrue($priceList->isContainSchedule());
    }

    public function testDeleteScheduleContains()
    {
        /** @var PriceListSchedule $schedule */
        $schedule = $this->getReference('schedule.4');
        $scheduleId = $schedule->getId();
        $priceListId = $schedule->getPriceList()->getId();

        $this->assertTrue($schedule->getPriceList()->isContainSchedule());

        $this->delete(['entity' => 'pricelistschedules', 'id' => (string)$scheduleId]);

        /** @var PriceList $priceList */
        $priceList = $this->getEntityManager()->find(PriceList::class, $priceListId);
        $this->assertFalse($priceList->isContainSchedule());
    }

    public function testDeleteListScheduleContains()
    {
        /** @var PriceList $priceList */
        $priceList = $this->getReference('price_list_1');
        $priceListId = $priceList->getId();

        $this->assertTrue($priceList->isContainSchedule());

        $this->cdelete(
            ['entity' => 'pricelistschedules'],
            ['filter' => ['priceList' => $priceListId]]
        );

        $priceList = $this->getEntityManager()->find(PriceList::class, $priceListId);
        $this->assertFalse($priceList->isContainSchedule());
    }

    public function testCreateAsIncludedDataUpdatesScheduleContains()
    {
        /** @var PriceList $priceList */
        $priceList = $this->getReference('price_list_5');
        $priceListId = $priceList->getId();

        $this->assertFalse($priceList->isContainSchedule());

        $this->patch(
            ['entity' => 'pricelists', 'id' => (string)$priceListId],
            [
                'data'     => [
                    'type'          => 'pricelists',
                    'id'            => (string)$priceListId,
                    'relationships' => [
                        'schedules' => [
                            'data' => [
                                ['type' => 'pricelistschedules', 'id' => 'new_schedule']
                            ]
                        ]
                    ]
                ],
                'included' => [
                    [
                        'type'       => 'pricelistschedules',
                        'id'         => 'new_schedule',
                        'attributes' => [
                            'activeAt'     => (new \DateTime())->format('c'),
                            'deactivateAt' => (new \DateTime())->format('c')
                        ]
                    ]
                ]
            ]
        );

        $priceList = $this->getEntityManager()->find(PriceList::class, $priceListId);
        $this->assertTrue($priceList->isContainSchedule());
    }
}
