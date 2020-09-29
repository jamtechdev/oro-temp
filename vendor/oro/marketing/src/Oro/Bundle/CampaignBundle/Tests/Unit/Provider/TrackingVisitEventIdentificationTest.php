<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Provider;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Provider\TrackingVisitEventIdentification;
use Oro\Bundle\TrackingBundle\Entity\TrackingEvent;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;

class TrackingVisitEventIdentificationTest extends \PHPUnit\Framework\TestCase
{
    /** @var TrackingVisitEventIdentification */
    protected $provider;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $em;

    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($this->em);
        $this->provider = new TrackingVisitEventIdentification($doctrine);
    }

    public function testIsApplicable()
    {
        $this->assertFalse($this->provider->isApplicable(new TrackingVisit()));
    }

    public function testGetIdentityTarget()
    {
        $this->assertNull($this->provider->getIdentityTarget());
    }

    public function testGetEventTargets()
    {
        $this->assertEquals(
            [
                'Oro\Bundle\CampaignBundle\Entity\Campaign'
            ],
            $this->provider->getEventTargets()
        );
    }

    public function testIsApplicableVisitEvent()
    {
        $event = new TrackingVisitEvent();
        $webEvent = new TrackingEvent();
        $event->setWebEvent($webEvent);
        $this->assertFalse($this->provider->isApplicableVisitEvent($event));
        $webEvent->setCode('test');
        $this->assertTrue($this->provider->isApplicableVisitEvent($event));
    }

    /**
     * @dataProvider processData
     * @param $isFind
     */
    public function testProcessEvent($isFind)
    {
        $event = new TrackingVisitEvent();
        $webEvent = new TrackingEvent();
        $webEvent->setCode('test');
        $event->setWebEvent($webEvent);

        $testResult = new \stdClass();

        $repo = $this->getMockBuilder('Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(Campaign::class)
            ->willReturn($repo);
        $repo->expects($this->once())->method('findOneByCode')
            ->with('test')
            ->willReturn($isFind ? $testResult : null);

        $this->assertEquals($isFind ? [$testResult] : [], $this->provider->processEvent($event));
    }

    /**
     * @return array
     */
    public function processData()
    {
        return [
            [true],
            [false]
        ];
    }
}
