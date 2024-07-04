<?php

namespace Oro\Bundle\ConversationBundle\Form;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\FormBundle\Provider\FormTemplateDataProviderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Form provider returns data to template during the CRUD operations.
 */
class ConversationFormTemplateDataProvider implements FormTemplateDataProviderInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param Conversation $entity
     */
    #[\Override]
    public function getData($entity, FormInterface $form, Request $request): array
    {
        if ($entity->getId()) {
            $formAction = $this->router->generate('oro_conversation_update', ['id' => $entity->getId()]);
        } else {
            $formAction = $this->router->generate('oro_conversation_create');
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'formAction' => $formAction
        ];
    }
}
