<?php

namespace Oro\Bundle\ConversationBundle\Form\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Manager\ConversationMessageManager;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form handler that saves conversation message.
 */
class ConversationMessageHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    private ConversationMessageManager $conversationMessageManager;
    private ManagerRegistry $doctrine;

    public function __construct(ConversationMessageManager $conversationMessageManager, ManagerRegistry $doctrine)
    {
        $this->conversationMessageManager = $conversationMessageManager;
        $this->doctrine = $doctrine;
    }

    #[\Override]
    public function process($data, FormInterface $form, Request $request): bool
    {
        /** @var ConversationMessage $data */
        $form->setData($data);

        if (\in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($form, $request);
            if ($form->isValid()) {
                $participant = $form->get('participant')->getData();
                if ($participant) {
                    $this->conversationMessageManager->setMessageParticipant($data, $participant);
                }

                $em = $this->doctrine->getManager();
                $em->persist($data);
                $em->flush();

                return true;
            }
        }

        return false;
    }
}
