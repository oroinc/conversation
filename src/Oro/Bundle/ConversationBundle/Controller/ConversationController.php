<?php

namespace Oro\Bundle\ConversationBundle\Controller;

use Oro\Bundle\ActivityBundle\Autocomplete\ContextSearchHandler;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Form\Handler\ConversationHandler;
use Oro\Bundle\ConversationBundle\Form\Type\ConversationType;
use Oro\Bundle\ConversationBundle\Manager\ConversationManager;
use Oro\Bundle\DataGridBundle\Provider\MultiGridProvider;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\FormBundle\Model\AutocompleteRequest;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD functionality for conversations.
 */
class ConversationController extends AbstractController
{
    #[Route(path: '/widget/info/{id}', name: 'oro_conversation_widget_info', requirements: ['id' => '\d+'])]
    #[Template('@OroConversation/Conversation/widget/info.html.twig')]
    #[AclAncestor('oro_conversation_view')]
    public function infoAction(Request $request, Conversation $entity): array
    {
        $targetEntity = $this->getTargetEntity($request);
        $renderContexts = null !== $targetEntity;

        return [
            'entity' => $entity,
            'target' => $targetEntity,
            'renderContexts' => $renderContexts,
        ];
    }

    #[Route(path: '/', name: 'oro_conversation_index')]
    #[AclAncestor('oro_conversation_view')]
    #[Template('@OroConversation/Conversation/index.html.twig')]
    public function indexAction(): array
    {
        return ['entity_class' => Conversation::class];
    }

    #[Route(path: '/view/{id}', name: 'oro_conversation_view', requirements: ['id' => '\d+'])]
    #[Acl(id: 'oro_conversation_view', type: 'entity', class: Conversation::class, permission: 'VIEW')]
    #[Template]
    public function viewAction(Conversation $conversation): array
    {
        return ['entity' => $conversation];
    }

    #[Route(path: '/create', name: 'oro_conversation_create')]
    #[Template('@OroConversation/Conversation/update.html.twig')]
    #[Acl(id: 'oro_conversation_create', type: 'entity', class: Conversation::class, permission: 'CREATE')]
    public function createAction(Request $request): array|RedirectResponse
    {
        $entityRoutingHelper = $this->container->get(EntityRoutingHelper::class);

        return $this->update(
            $request,
            $this->container->get('oro_conversation.manager.conversation')->createConversation(
                $entityRoutingHelper->getEntityClassName($request),
                $entityRoutingHelper->getEntityId($request)
            )
        );
    }

    #[Route(path: '/update/{id}', name: 'oro_conversation_update', requirements: ['id' => '\d+'])]
    #[Template]
    #[Acl(id: 'oro_conversation_update', type: 'entity', class: Conversation::class, permission: 'EDIT')]
    public function updateAction(Request $request, Conversation $conversation): array|RedirectResponse
    {
        return $this->update($request, $conversation);
    }

    #[Route(path: '/source-grid-dialog', name: 'oro_conversation_source_grid_dialog')]
    #[Template('@OroDataGrid/Grid/dialog/multi.html.twig')]
    #[AclAncestor('oro_conversation_edit')]
    public function gridDialogAction(): array
    {
        $activityManager = $this->container->get(ActivityManager::class);
        $targetClasses = array_keys($activityManager->getActivityTargets(Conversation::class));
        $targets =  $this->container->get(MultiGridProvider::class)->getEntitiesData($targetClasses);

        return [
            'gridWidgetName'         => 'source-multi-grid-widget',
            'dialogWidgetName'       => 'source-dialog',
            'sourceEntityClassAlias' => 'conversations',
            'entityTargets'          => $targets
        ];
    }

    #[Route(path: '/source/search/autocomplete', name: 'oro_conversation_source_autocomplete_search')]
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
            'errors' => []
        ];

        if ($violations = $validator->validate($autocompleteRequest)) {
            /** @var ConstraintViolation $violation */
            foreach ($violations as $violation) {
                $result['errors'][] = $violation->getMessage();
            }
        }

        if (!empty($result['errors'])) {
            if ($isXmlHttpRequest) {
                return new JsonResponse($result, $code);
            }

            throw new HttpException($code, implode(', ', $result['errors']));
        }

        $searchHandler = $this->container->get(ContextSearchHandler::class);
        $searchHandler->setClass(Conversation::class);

        return new JsonResponse($searchHandler->search(
            $autocompleteRequest->getQuery(),
            $autocompleteRequest->getPage(),
            $autocompleteRequest->getPerPage(),
            $autocompleteRequest->isSearchById()
        ));
    }

    private function update(Request $request, Conversation $conversation): array|RedirectResponse
    {
        return $this->container->get(UpdateHandlerFacade::class)->update(
            $conversation,
            $this->createForm(ConversationType::class, $conversation),
            $this->container->get(TranslatorInterface::class)->trans('oro.conversation.saved.message'),
            $request,
            $this->container->get(ConversationHandler::class),
            'oro_conversation_update'
        );
    }

    private function getTargetEntity(Request $request): ?object
    {
        $entityRoutingHelper = $this->container->get(EntityRoutingHelper::class);
        $targetEntityClass = $entityRoutingHelper->getEntityClassName($request, 'targetActivityClass');
        $targetEntityId = $entityRoutingHelper->getEntityId($request, 'targetActivityId');
        if (!$targetEntityClass || !$targetEntityId) {
            return null;
        }

        return $entityRoutingHelper->getEntity($targetEntityClass, $targetEntityId);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'oro_conversation.manager.conversation' => ConversationManager::class,
            EntityRoutingHelper::class,
            UpdateHandlerFacade::class,
            TranslatorInterface::class,
            ActivityManager::class,
            MultiGridProvider::class,
            ContextSearchHandler::class,
            ValidatorInterface::class,
            EntityRoutingHelper::class,
            ConversationHandler::class
        ]);
    }
}
