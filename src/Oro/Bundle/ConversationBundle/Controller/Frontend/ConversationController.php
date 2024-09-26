<?php

namespace Oro\Bundle\ConversationBundle\Controller\Frontend;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The page with conversations for the storefront.
 */
class ConversationController extends AbstractController
{
    #[Route(path: '/', name: 'oro_conversation_frontend_conversation_index')]
    #[Layout]
    #[Acl(
        id: 'oro_conversation_frontend_view',
        type: 'entity',
        class: Conversation::class,
        permission: 'VIEW',
        groupName: 'commerce'
    )]
    public function indexAction(): array
    {
        return ['data' => ['entity' => $this->getUser()]];
    }
}
