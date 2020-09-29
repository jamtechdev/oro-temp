<?php

namespace Oro\Bundle\CheckoutBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CheckoutBundle\EventListener\CheckoutListener;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Provider\DefaultUserProvider;

class CheckoutListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var DefaultUserProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $defaultUserProvider;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var CheckoutListener */
    private $listener;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->defaultUserProvider = $this->createMock(DefaultUserProvider::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->listener = new CheckoutListener($this->defaultUserProvider, $this->tokenAccessor);
    }

    public function testPostUpdate()
    {
        $checkout = new Checkout();

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects($this->once())
            ->method('scheduleExtraUpdate')
            ->with(
                $checkout,
                ['completedData' => [null, $checkout->getCompletedData()]]
            );

        /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('getUnitOfWork')->willReturn($uow);

        $this->listener->postUpdate($checkout, new LifecycleEventArgs($checkout, $em));
    }

    /**
     * @dataProvider persistNotSetDefaultOwnerDataProvider
     *
     * @param string $token
     * @param Checkout $checkout
     */
    public function testPrePersistNotSetDefaultOwner($token, Checkout $checkout)
    {
        $this->tokenAccessor
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $newUser = new User();
        $newUser->setFirstName('first_name');
        $this->listener->prePersist($checkout);
        $this->assertNotSame($newUser, $checkout->getOwner());
    }

    /**
     * @return array
     */
    public function persistNotSetDefaultOwnerDataProvider()
    {
        return [
            'without token and without owner' => [
                'token' => null,
                'checkout' => new Checkout()
            ],
            'unsupported token and without owner' => [
                'token' => new \stdClass(),
                'checkout' => new Checkout()
            ],
            'with owner' => [
                'token' => new AnonymousCustomerUserToken(''),
                'checkout' => (new Checkout())->setOwner(new User())
            ]
        ];
    }

    /**
     * @dataProvider persistSetDefaultOwnerDataProvider
     *
     * @param string $token
     * @param Checkout $checkout
     * @param Organization|null $organization
     */
    public function testPrePersistSetDefaultOwner($token, Checkout $checkout, $organization)
    {
        $this->tokenAccessor
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $newUser = new User();
        $newUser->setFirstName('first_name');
        $this->defaultUserProvider
            ->expects($this->once())
            ->method('getDefaultUser')
            ->with('oro_checkout', 'default_guest_checkout_owner')
            ->willReturn($newUser);

        $this->tokenAccessor
            ->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $this->listener->prePersist($checkout);
        $this->assertSame($newUser, $checkout->getOwner());
        $this->assertEquals($organization, $checkout->getOrganization());
    }

    /**
     * @return array
     */
    public function persistSetDefaultOwnerDataProvider()
    {
        return [
            'with token, without owner, without current organization' => [
                'token' => new AnonymousCustomerUserToken(''),
                'checkout' => new Checkout(),
                'expectedOrganization' => null
            ],
            'with token, without owner, with organization' => [
                'token' => new AnonymousCustomerUserToken(''),
                'checkout' => new Checkout(),
                'organization' => new Organization()
            ],
        ];
    }
}