<?php

namespace Oro\Bundle\WebCatalog\Tests\Unit\Form\Extension;

use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\FormBundle\Tests\Unit\Form\Stub\EntityIdentifierType as EntityIdentifierTypeStub;
use Oro\Bundle\ScopeBundle\Form\Type\ScopeType;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Oro\Bundle\WebCatalogBundle\Entity\WebCatalog;
use Oro\Bundle\WebCatalogBundle\Form\Extension\WebCatalogScopeExtension;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class WebCatalogScopeExtensionTest extends FormIntegrationTestCase
{
    use EntityTrait;

    /**
     * @var WebCatalogScopeExtension
     */
    protected $extension;

    /**
     * @var ScopeManager|\PHPUnit\Framework\MockObject\MockObject $scopeManager
     */
    protected $scopeManager;

    protected function setUp()
    {
        $this->extension = new WebCatalogScopeExtension();

        parent::setUp();
    }

    public function testBuildForm()
    {
        $this->scopeManager->expects($this->once())
            ->method('getScopeEntities')
            ->with('web_content')
            ->willReturn(['webCatalog' => WebCatalog::class]);

        $form = $this->factory->create(
            ScopeType::class,
            null,
            [
                ScopeType::SCOPE_TYPE_OPTION => 'web_content',
                'web_catalog' => $this->getEntity(WebCatalog::class, ['id' => 1])
            ]
        );

        $this->assertTrue($form->has('webCatalog'));
    }

    public function testBuildFormWithoutRequiredOption()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The option "web_catalog" must be set.');
        $this->factory->create(
            ScopeType::class,
            null,
            [
                ScopeType::SCOPE_TYPE_OPTION => 'web_content'
            ]
        );
    }

    public function testGetExtendedTypes()
    {
        $this->assertEquals([ScopeType::class], WebCatalogScopeExtension::getExtendedTypes());
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $this->scopeManager = $this->getMockBuilder(ScopeManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            new PreloadedExtension(
                [
                    ScopeType::class => new ScopeType($this->scopeManager),
                    EntityIdentifierType::class => new EntityIdentifierTypeStub(
                        [
                            1 => $this->getEntity(WebCatalog::class, ['id' => 1])
                        ]
                    ),
                ],
                [
                    ScopeType::class => [$this->extension],
                ]
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }
}