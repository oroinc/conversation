<?php

namespace Oro\Bundle\ConversationBundle\Controller;

use Oro\Bundle\ConversationBundle\Acl\Voter\ManageConversationMessagesVoter;
use Oro\Bundle\ConversationBundle\Autocomplete\ParticipantSearchHandler;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Form\Handler\ConversationMessageHandler;
use Oro\Bundle\ConversationBundle\Form\Type\ConversationMessageType;
use Oro\Bundle\ConversationBundle\Manager\ConversationMessageManager;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Bundle\DataGridBundle\Provider\MultiGridProvider;
use Oro\Bundle\FormBundle\Model\AutocompleteRequest;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD functionality for conversation messages.
 */
#[Route(path: '/message')]
class ConversationMessageController extends AbstractController
{
    #[Route(
        path: '/{id}/create',
        name: 'oro_conversation_message_create',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST']
    )]
    #[Template('@OroConversation/ConversationMessage/dialog/update.html.twig')]
    #[AclAncestor(id: 'oro_conversation_edit')]
    public function createAction(Request $request, Conversation $conversation): array|RedirectResponse
    {
        if (!$this->isGranted(ManageConversationMessagesVoter::PERMISSION_NAME, $conversation)) {
            throw $this->createAccessDeniedException(
                sprintf('You does not have access to manage messages for conversation "%s"', $conversation->getName())
            );
        }

        return $this->update(
            $request,
            $this->container->get(ConversationMessageManager::class)->createMessage($conversation)
        );
    }

    #[Route(
        path: '/{id}/widget-list/direct',
        name: 'oro_conversation_messages_list_direct',
        requirements: ['id' => '\d+'],
        defaults: ['inverse' => false],
        methods: ['GET', 'POST']
    )]
    #[Route(
        path: '/{id}/widget-list/inverse',
        name: 'oro_conversation_messages_list_inverse',
        requirements: ['id' => '\d+'],
        defaults: ['inverse' => true],
        methods: ['GET', 'POST']
    )]
    #[Template('@OroConversation/ConversationMessage/widget/getList.html.twig')]
    #[AclAncestor(id: 'oro_conversation_view')]
    public function getListAction(Request $request, Conversation $conversation, bool $inverse): array|RedirectResponse
    {
        $page = $request->query->get('page', 1);
        if ($page === 1) {
            $this->container->get(ConversationParticipantManager::class)
                ->setLastReadMessageForParticipantAndSendNotification($conversation, $this->getUser());
        }

        return array_merge(
            $this->container->get(ConversationMessageManager::class)->getMessages(
                $conversation,
                $page,
                $request->query->get('perPage', 5),
                'DESC',
                $inverse,
            ),
            [
                'route_name' => $inverse
                    ? 'oro_conversation_messages_list_inverse'
                    : 'oro_conversation_messages_list_direct'
            ]
        );
    }

    #[Route(path: '/participants-grid-dialog', name: 'oro_conversation_messages_participants_grid_dialog')]
    #[Template('@OroDataGrid/Grid/dialog/multi.html.twig')]
    #[AclAncestor('oro_conversation_edit')]
    public function gridDialogAction(): array
    {
        $targetClasses = $this->container->get(ConversationParticipantManager::class)->getParticipantTargetClasses();

        return [
            'gridWidgetName' => 'source-multi-grid-widget',
            'dialogWidgetName' => 'source-dialog',
            'sourceEntityClassAlias' => 'conversationparticipantss',
            'entityTargets' => $this->container->get(MultiGridProvider::class)->getEntitiesData($targetClasses),
        ];
    }

    #[Route(
        path: '/participants/search/autocomplete',
        name: 'oro_conversation_messages_participants_autocomplete_search'
    )]
    #[AclAncestor('oro_conversation_edit')]
    public function autocompleteAction(Request $request): JsonResponse
    {
        $autocompleteRequest = new AutocompleteRequest($request);
        $validator = $this->container->get(ValidatorInterface::class);
        $isXmlHttpRequest = $request->isXmlHttpRequest();
        $code = 200;
        $result = [
            'results' => [],
            'hasMore' => false,
            'errors'  => []
        ];

        $violations = $validator->validate($autocompleteRequest);
        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $result['errors'][] = $violation->getMessage();
        }
        if (!empty($result['errors'])) {
            if ($isXmlHttpRequest) {
                return new JsonResponse($result, $code);
            }

            throw new HttpException($code, implode(', ', $result['errors']));
        }

        return new JsonResponse(
            $this->container->get(ParticipantSearchHandler::class)->search(
                $autocompleteRequest->getQuery(),
                $autocompleteRequest->getPage(),
                $autocompleteRequest->getPerPage(),
                $autocompleteRequest->isSearchById()
            )
        );
    }

    private function update(Request $request, ConversationMessage $message): array|RedirectResponse
    {
        $translator = $this->container->get(TranslatorInterface::class);

        return $this->container->get(UpdateHandlerFacade::class)->update(
            $message,
            $this->createForm(ConversationMessageType::class, $message),
            $translator->trans('oro.conversation.conversationmessage.saved.message'),
            $request,
            $this->container->get(ConversationMessageHandler::class)
        );
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            ConversationMessageManager::class,
            ConversationParticipantManager::class,
            ParticipantSearchHandler::class,
            UpdateHandlerFacade::class,
            TranslatorInterface::class,
            ConversationMessageHandler::class,
            MultiGridProvider::class,
            ValidatorInterface::class,
        ]);
    }
}
