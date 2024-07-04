<?php

namespace Oro\Bundle\ConversationBundle\Form\Handler;

use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Manager\ConversationMessageManager;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form handler that saves conversation message via ConversationMessageManager.
 */
class ConversationMessageHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    private ConversationMessageManager $conversationMessageManager;

    public function __construct(ConversationMessageManager $conversationMessageManager)
    {
        $this->conversationMessageManager = $conversationMessageManager;
    }

    #[\Override]
    public function process($data, FormInterface $form, Request $request): bool
    {
        /** @var ConversationMessage $data */
        $form->setData($data);

        if (\in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($form, $request);
            if ($form->isValid()) {
                $this->conversationMessageManager->saveMessage($data, $form->get('participant')->getData());

                return true;
            }
        }

        return false;
    }
}
