<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Form;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Form\ConversationFormTemplateDataProvider;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class ConversationFormTemplateDataProviderTest extends TestCase
{
    use EntityTrait;

    private RouterInterface|MockObject $router;

    private ConversationFormTemplateDataProvider $formTemplateDataProvider;

    #[\Override]
    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->formTemplateDataProvider = new ConversationFormTemplateDataProvider($this->router);
    }

    public function testGetDataOnNewEntity(): void
    {
        $entity = $this->getEntity(Conversation::class);
        $form = $this->createMock(FormInterface::class);
        $request = $this->createMock(Request::class);
        $formView = $this->createMock(FormView::class);

        $this->router->expects(self::once())
            ->method('generate')
            ->with('oro_conversation_create')
            ->willReturn('generated_create_url');
        $form->expects(self::once())
            ->method('createView')
            ->willReturn($formView);

        self::assertEquals(
            [
                'entity' => $entity,
                'form' => $formView,
                'formAction' => 'generated_create_url'
            ],
            $this->formTemplateDataProvider->getData($entity, $form, $request)
        );
    }

    public function testGetDataOnExistingEntity(): void
    {
        $entity = $this->getEntity(Conversation::class, ['id' => 123]);
        $form = $this->createMock(FormInterface::class);
        $request = $this->createMock(Request::class);
        $formView = $this->createMock(FormView::class);

        $this->router->expects(self::once())
            ->method('generate')
            ->with('oro_conversation_update', ['id' => 123])
            ->willReturn('generated_update_url');
        $form->expects(self::once())
            ->method('createView')
            ->willReturn($formView);

        self::assertEquals(
            [
                'entity' => $entity,
                'form' => $formView,
                'formAction' => 'generated_update_url'
            ],
            $this->formTemplateDataProvider->getData($entity, $form, $request)
        );
    }
}
