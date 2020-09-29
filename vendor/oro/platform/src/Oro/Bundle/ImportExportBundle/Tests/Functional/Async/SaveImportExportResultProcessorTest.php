<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Functional\Async\Export;

use Oro\Bundle\ImportExportBundle\Async\Export\ExportMessageProcessor;
use Oro\Bundle\ImportExportBundle\Async\SaveImportExportResultProcessor;
use Oro\Bundle\ImportExportBundle\Entity\ImportExportResult;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * @dbIsolationPerTest
 */
class SaveImportExportResultProcessorTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp()
    {
        $this->initClient();
    }

    public function testCouldBeConstructedByContainer(): void
    {
        $instance = $this->getContainer()->get('oro_importexport.async.save_import_export_result_processor');

        $this->assertInstanceOf(SaveImportExportResultProcessor::class, $instance);
    }

    public function testProcessSaveJobWithValidData(): void
    {
        $manager = $this->getManager();
        $importExportResultManager = $manager->getRepository(ImportExportResult::class);

        $rootJob = $this->getJobProcessor()->findOrCreateRootJob(
            'test_export_result_message',
            'oro:export:test_export_result_message'
        );

        $message = new NullMessage();
        $message->setMessageId('abc');
        $message->setBody(JSON::encode([
            'jobId' => $rootJob->getId(),
            'type' => ProcessorRegistry::TYPE_EXPORT,
            'entity' => ImportExportResult::class
        ]));

        $processor = $this->getContainer()->get('oro_importexport.async.save_import_export_result_processor');
        $processorResult = $processor->process($message, $this->createSessionMock());

        /** @var ImportExportResult $resultJob */
        $rootJobResult = $importExportResultManager->findOneBy(['jobId' => $rootJob]);

        self::assertEquals(ExportMessageProcessor::ACK, $processorResult);
        self::assertAttributeEquals($rootJob->getId(), 'jobId', $rootJobResult);
        self::assertAttributeEquals(ProcessorRegistry::TYPE_EXPORT, 'type', $rootJobResult);
        self::assertAttributeEquals(ImportExportResult::class, 'entity', $rootJobResult);
    }

    public function testProcessSaveJobWithInvalidData():void
    {
        $message = new NullMessage();
        $message->setMessageId('abc');
        $message->setBody(JSON::encode([]));

        $processor = $this->getContainer()->get('oro_importexport.async.save_import_export_result_processor');
        $processorResult = $processor->process($message, $this->createSessionMock());

        self::assertEquals(ExportMessageProcessor::REJECT, $processorResult);
    }

    /**
     * @return ManagerRegistry
     */
    private function getManager(): ManagerRegistry
    {
        return $this->getContainer()->get('doctrine');
    }

    /**
     * @returnJobProcessor
     */
    private function getJobProcessor(): JobProcessor
    {
        return $this->getContainer()->get('oro_message_queue.job.processor');
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|SessionInterface
     */
    private function createSessionMock()
    {
        return $this->createMock(SessionInterface::class);
    }
}
