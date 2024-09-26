<?php

namespace Oro\Bundle\ConversationBundle\Form\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form handler that saves conversation.
 */
class ConversationHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[\Override]
    public function process($data, FormInterface $form, Request $request): bool
    {
        /** @var Conversation $data */
        $form->setData($data);

        if (\in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($form, $request);
            if ($form->isValid()) {
                $em = $this->doctrine->getManager();
                $em->persist($data);
                $em->flush();

                return true;
            }
        }

        return false;
    }
}
