<?php

namespace Oro\Bundle\ProductBundle\Tests\Functional\ImportExport\Reader;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Oro\Bundle\ImportExportBundle\Context\Context;
use Oro\Bundle\ProductBundle\ImportExport\Reader\RelatedProductEntityReader;
use Oro\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Oro\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadRelatedProductData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class RelatedProductEntityReaderTest extends WebTestCase
{
    /** @var RelatedProductEntityReader */
    private $reader;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadRelatedProductData::class]);

        $container = $this->getContainer();

        $this->reader = $container->get('oro_product.importexport.reader.related_product.entity');
    }

    public function testRead(): void
    {
        $executionContext = $this->createMock(ExecutionContext::class);
        $jobInstance = $this->createMock(JobInstance::class);

        $jobExecution = $this->createMock(JobExecution::class);
        $jobExecution->expects($this->any())
            ->method('getJobInstance')
            ->willReturn($jobInstance);

        $stepExecution = $this->createMock(StepExecution::class);
        $stepExecution->expects($this->any())
            ->method('getExecutionContext')
            ->willReturn($executionContext);
        $stepExecution->expects($this->any())
            ->method('getJobExecution')
            ->willReturn($jobExecution);

        $this->reader->initializeByContext(new Context([]));
        $this->reader->setStepExecution($stepExecution);

        $actual = $this->reader->read();

        $this->assertIsArray($actual);
        $this->assertCount(2, $actual);
        $this->assertArrayHasKey('sku', $actual);
        $this->assertEquals(LoadProductData::PRODUCT_3, $actual['sku']);
        $this->assertArrayHasKey('related_skus', $actual);
        $this->assertCount(2, $actual['related_skus']);
        $this->assertContains(LoadProductData::PRODUCT_1, $actual['related_skus']);
        $this->assertContains(LoadProductData::PRODUCT_2, $actual['related_skus']);
    }
}
