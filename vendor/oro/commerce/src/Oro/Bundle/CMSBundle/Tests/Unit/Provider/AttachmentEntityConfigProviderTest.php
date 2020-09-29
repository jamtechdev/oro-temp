<?php

namespace Oro\Bundle\CMSBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Oro\Bundle\AttachmentBundle\Provider\AttachmentEntityConfigProviderInterface;
use Oro\Bundle\CMSBundle\DBAL\Types\WYSIWYGPropertiesType;
use Oro\Bundle\CMSBundle\DBAL\Types\WYSIWYGStyleType;
use Oro\Bundle\CMSBundle\Provider\AttachmentEntityConfigProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AttachmentEntityConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var RegistryInterface */
    private $doctrine;

    /** @var AttachmentEntityConfigProviderInterface */
    private $innerAttachmentEntityConfigProvider;

    /** @var AttachmentEntityConfigProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(RegistryInterface::class);
        $this->innerAttachmentEntityConfigProvider = $this->createMock(AttachmentEntityConfigProviderInterface::class);

        $this->provider = new AttachmentEntityConfigProvider(
            $this->doctrine,
            $this->innerAttachmentEntityConfigProvider
        );
    }

    public function testGetEntityConfig(): void
    {
        $this->innerAttachmentEntityConfigProvider
            ->expects($this->once())
            ->method('getEntityConfig')
            ->with($entityClass = 'SampleClass')
            ->willReturn($config = $this->createMock(ConfigInterface::class));

        $this->assertSame($config, $this->provider->getEntityConfig($entityClass));
    }

    public function testGetFieldConfigWhenEmtpyEntityClass(): void
    {
        $this->doctrine
            ->expects($this->never())
            ->method('getManagerForClass');

        $this->assertNull($this->provider->getFieldConfig('', 'sampleFieldName'));
    }

    public function testGetFieldConfigWhenNoEntityManager(): void
    {
        $this->doctrine
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with($entityClass = 'SampleClass')
            ->willReturn(null);

        $this->assertNull($this->provider->getFieldConfig($entityClass, 'sampleFieldName'));
    }

    /**
     * @dataProvider fieldConfigDataProvider
     *
     * @param string $fieldName
     * @param string $fieldType
     */
    public function getFieldConfig(string $fieldName, string $fieldType): void
    {
        $this->doctrine
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with($entityClass = 'SampleClass')
            ->willReturn($entityManager = $this->createMock(EntityManager::class));

        $entityManager
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($entityClass)
            ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));

        $classMetadata
            ->expects($this->once())
            ->method('getTypeOfField')
            ->willReturn($fieldType);

        $this->innerAttachmentEntityConfigProvider
            ->expects($this->once())
            ->method('getFieldConfig')
            ->with($entityClass, 'sampleField')
            ->willReturn($config = $this->createMock(ConfigInterface::class));

        $this->assertSame($config, $this->provider->getFieldConfig($entityClass, $fieldName));
    }

    /**
     * @return array
     */
    public function fieldConfigDataProvider(): array
    {
        return [
            'regular field' => [
                'fieldName' => 'sampleField',
                'fieldType' => 'sampleType',
            ],
            'style field' => [
                'fieldName' => 'sampleField_style',
                'fieldType' => WYSIWYGStyleType::TYPE,
            ],
            'properties field' => [
                'fieldName' => 'sampleField_properties',
                'fieldType' => WYSIWYGPropertiesType::TYPE,
            ],
        ];
    }
}
