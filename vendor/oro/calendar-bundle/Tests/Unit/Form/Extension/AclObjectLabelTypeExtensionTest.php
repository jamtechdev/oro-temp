<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CalendarBundle\Form\Extension\AclObjectLabelTypeExtension;
use Oro\Bundle\SecurityBundle\Form\Type\ObjectLabelType;
use Symfony\Component\Form\FormView;

class AclObjectLabelTypeExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var AclObjectLabelTypeExtension */
    protected $formExtension;

    protected function setUp()
    {
        $this->formExtension = new AclObjectLabelTypeExtension();
    }

    /**
     * @dataProvider buildViewProvider
     */
    public function testBuildView($oldValue, $newValue)
    {
        $formView = new FormView();
        $formView->vars['value'] = $oldValue;

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->formExtension->buildView($formView, $form, []);

        $this->assertEquals($newValue, $formView->vars['value']);
    }

    public function buildViewProvider()
    {
        return [
            ['oro.calendar.systemcalendar.entity_label', 'oro.calendar.organization_calendar'],
            ['oro.calendar.calendar.entity_label', 'oro.calendar.calendar.entity_label'],
        ];
    }

    public function testGetExtendedTypes()
    {
        $this->assertEquals([ObjectLabelType::class], AclObjectLabelTypeExtension::getExtendedTypes());
    }
}
