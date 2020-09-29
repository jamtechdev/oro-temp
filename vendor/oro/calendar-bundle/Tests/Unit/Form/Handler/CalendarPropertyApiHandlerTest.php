<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CalendarBundle\Entity\CalendarProperty;
use Oro\Bundle\CalendarBundle\Form\Handler\CalendarPropertyApiHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CalendarPropertyApiHandlerTest extends \PHPUnit\Framework\TestCase
{
    const FORM_DATA = ['field' => 'value'];

    /**
     * @dataProvider supportedMethods
     *
     * @param string $method
     */
    public function testProcess($method)
    {
        $form = $this->createMock(Form::class);
        $om = $this->createMock(ObjectManager::class);

        $request = new Request();

        $request->initialize([], self::FORM_DATA);
        $request->setMethod($method);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $obj = new CalendarProperty();

        $form->expects($this->once())
            ->method('setData')
            ->with($this->identicalTo($obj));
        $form->expects($this->once())
            ->method('submit')
            ->with($this->identicalTo(self::FORM_DATA));
        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));
        $om->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($obj));
        $om->expects($this->once())
            ->method('flush');

        $handler = new CalendarPropertyApiHandler($form, $requestStack, $om);
        $handler->process($obj);
    }

    public function supportedMethods()
    {
        return [
            ['POST'],
            ['PUT']
        ];
    }
}
