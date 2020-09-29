<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Async;

use Oro\Bundle\CustomerBundle\Async\BusinessUnitOwnerTreeCacheJobProcessor;
use Oro\Bundle\CustomerBundle\Async\Topics;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Model\BusinessUnitMessageFactory;
use Oro\Bundle\CustomerBundle\Owner\FrontendOwnerTreeProvider;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Test\JobRunner;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\Null\NullSession;
use Oro\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;

class BusinessUnitOwnerTreeCacheJobProcessorTest extends \PHPUnit\Framework\TestCase
{
    private const JOB_ID = 5;

    /**
     * @var BusinessUnitMessageFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageFactory;

    /**
     * @var FrontendOwnerTreeProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    private $frontendOwnerTreeProvider;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var BusinessUnitOwnerTreeCacheJobProcessor
     */
    private $processor;

    protected function setUp()
    {
        $this->messageFactory = $this->createMock(BusinessUnitMessageFactory::class);
        $this->frontendOwnerTreeProvider = $this->createMock(FrontendOwnerTreeProvider::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->processor = new BusinessUnitOwnerTreeCacheJobProcessor(
            new JobRunner(),
            $this->frontendOwnerTreeProvider,
            $this->messageFactory,
            $this->logger
        );
    }

    public function testProcessWhenInvalidMessage(): void
    {
        $exception = new InvalidArgumentException();
        $this->messageFactory
            ->expects($this->once())
            ->method('getJobIdFromMessage')
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Queue Message is invalid', ['exception' => $exception]);

        $message = new NullMessage();
        $message->setBody(JSON::encode([]));

        $this->assertEquals(MessageProcessorInterface::REJECT, $this->processor->process($message, new NullSession()));
    }

    public function testProcessWhenRuntimeException(): void
    {
        $this->messageFactory
            ->expects($this->once())
            ->method('getJobIdFromMessage')
            ->willReturn(self::JOB_ID);

        $businessUnit = new Customer();
        $this->messageFactory
            ->expects($this->once())
            ->method('getBusinessUnitFromMessage')
            ->willReturn($businessUnit);

        $exception = new \Exception();
        $this->frontendOwnerTreeProvider
            ->expects($this->once())
            ->method('getTreeByBusinessUnit')
            ->with($businessUnit)
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Unexpected exception occurred during queue message processing', [
                'exception' => $exception,
                'topic' => Topics::CALCULATE_BUSINESS_UNIT_OWNER_TREE_CACHE
            ]);

        $message = new NullMessage();
        $message->setBody(JSON::encode([]));

        $this->assertEquals(MessageProcessorInterface::REJECT, $this->processor->process($message, new NullSession()));
    }

    public function testProcess(): void
    {
        $this->messageFactory
            ->expects($this->once())
            ->method('getJobIdFromMessage')
            ->willReturn(self::JOB_ID);

        $businessUnit = new Customer();
        $this->messageFactory
            ->expects($this->once())
            ->method('getBusinessUnitFromMessage')
            ->willReturn($businessUnit);

        $this->frontendOwnerTreeProvider
            ->expects($this->once())
            ->method('getTreeByBusinessUnit')
            ->with($businessUnit);

        $this->logger
            ->expects($this->never())
            ->method('error');

        $message = new NullMessage();
        $message->setBody(JSON::encode([]));

        $this->assertEquals(MessageProcessorInterface::ACK, $this->processor->process($message, new NullSession()));
    }
}
