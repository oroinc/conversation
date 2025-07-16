<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Form\Handler\ConversationHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ConversationHandlerTest extends TestCase
{
    private ManagerRegistry&MockObject $doctrine;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->handler = new ConversationHandler($this->doctrine);
    }

    public function testProcessOnGetRequest(): void
    {
        $request = new Request();
        $request->setMethod('GET');

        $data = new ConversationMessage();

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())
            ->method('setData')
            ->with($data);

        self::assertFalse($this->handler->process($data, $form, $request));
    }

    public function testProcessOnNotValidForm(): void
    {
        $request = new Request();
        $request->setMethod('POST');

        $data = new ConversationMessage();

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())
            ->method('setData')
            ->with($data);
        $form->expects(self::once())
            ->method('submit');
        $form->expects(self::once())
            ->method('isValid')
            ->willReturn(false);

        self::assertFalse($this->handler->process($data, $form, $request));
    }

    public function testProcessOnValidForm(): void
    {
        $request = new Request();
        $request->setMethod('POST');

        $data = new ConversationMessage();

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('persist')
            ->with($data);
        $em->expects(self::once())
            ->method('flush');

        $this->doctrine->expects(self::once())
            ->method('getManager')
            ->willReturn($em);

        self::assertTrue($this->handler->process($data, $form, $request));
    }
}
