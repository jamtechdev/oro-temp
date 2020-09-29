<?php

namespace Oro\Bundle\PromotionBundle\Tests\Unit\Form\Type;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\PromotionBundle\Entity\Coupon;
use Oro\Bundle\PromotionBundle\Form\Type\CouponAddType;
use Oro\Bundle\PromotionBundle\Form\Type\CouponAutocompleteType;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class CouponAddTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    /**
     * @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $doctrineHelper;

    /**
     * @var CouponAddType
     */
    private $formType;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->formType = new CouponAddType($this->doctrineHelper);
        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions()
    {
        /** @var Coupon $coupon */
        $coupon1 = $this->getEntity(Coupon::class, ['id' => 1]);

        /** @var Coupon $coupon */
        $coupon2 = $this->getEntity(Coupon::class, ['id' => 2]);

        return [
            new PreloadedExtension(
                [
                    CouponAddType::class => $this->formType,
                    CouponAutocompleteType::class => new EntityType(
                        ['coupon1' => $coupon1],
                        CouponAutocompleteType::NAME
                    ),
                    EntityIdentifierType::class => new EntityType([1 => $coupon1, 2 => $coupon2]),
                ],
                []
            ),
        ];
    }

    public function testBuildForm()
    {
        $entity = $this->getEntity(Order::class, ['id' => 777]);
        $form = $this->factory->create(CouponAddType::class, null, ['entity' => $entity]);

        $this->assertTrue($form->has('coupon'));
        $this->assertTrue($form->has('addedCoupons'));
    }

    public function testGetBlockPrefix()
    {
        $this->assertEquals(CouponAddType::NAME, $this->formType->getBlockPrefix());
    }

    /**
     * @dataProvider submitProvider
     *
     * @param array $submittedData
     * @param array $expectedData
     */
    public function testSubmit(array $submittedData, array $expectedData)
    {
        $entity = $this->getEntity(Order::class, ['id' => 777]);
        $form = $this->factory->create(CouponAddType::class, null, ['entity' => $entity]);
        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitProvider()
    {
        return [
            'empty data' => [
                'submittedData' => [
                    'coupon' => 'coupon1',
                    'addedCoupons' => '',
                ],
                'expectedData' => [],
            ],
            'two coupons added' => [
                'submittedData' => [
                    'coupon' => '',
                    'addedCoupons' => [1, 2],
                ],
                'expectedData' => [
                    $this->getEntity(Coupon::class, ['id' => 1]),
                    $this->getEntity(Coupon::class, ['id' => 2])
                ],
            ]
        ];
    }

    public function testFinishView()
    {
        $view = new FormView();
        $entityId = 777;
        $entity = $this->getEntity(Order::class, ['id' => $entityId]);

        /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject $form */
        $form = $this->createMock(FormInterface::class);
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($entity)
            ->willReturn($entityId);
        $this->formType->finishView($view, $form, ['entity' => $entity]);
        $this->assertArrayHasKey('entityClass', $view->vars);
        $this->assertEquals(Order::class, $view->vars['entityClass']);
        $this->assertArrayHasKey('entityId', $view->vars);
        $this->assertEquals($entityId, $view->vars['entityId']);
    }
}