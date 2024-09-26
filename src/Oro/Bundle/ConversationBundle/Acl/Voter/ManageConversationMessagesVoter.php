<?php

namespace Oro\Bundle\ConversationBundle\Acl\Voter;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\SecurityBundle\Acl\Domain\DomainObjectReference;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Denies manage messages for closed conversation.
 */
class ManageConversationMessagesVoter implements VoterInterface
{
    public const PERMISSION_NAME = 'MANAGE_MESSAGES';

    private const WORKFLOW_NAME = 'conversation_flow';
    private const STEP_CLOSED = 'closed';

    private WorkflowManager $workflowManager;
    private ManagerRegistry $doctrine;

    public function __construct(
        WorkflowManager $workflowManager,
        ManagerRegistry $doctrine
    ) {
        $this->workflowManager = $workflowManager;
        $this->doctrine = $doctrine;
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    #[\Override]
    public function vote(TokenInterface $token, mixed $subject, array $attributes): int
    {
        if (!\is_object($subject)) {
            return self::ACCESS_ABSTAIN;
        }

        if (!\in_array(self::PERMISSION_NAME, $attributes, true)) {
            return self::ACCESS_ABSTAIN;
        }

        if (!$token instanceof OrganizationAwareTokenInterface) {
            return self::ACCESS_ABSTAIN;
        }

        $class = $subject instanceof DomainObjectReference ? $subject->getType() : ClassUtils::getClass($subject);
        if ($class !== Conversation::class) {
            return self::ACCESS_ABSTAIN;
        }

        if (!$this->workflowManager->isActiveWorkflow(self::WORKFLOW_NAME)) {
            return self::ACCESS_ABSTAIN;
        }

        $workflowItem = $this->getWorkflowItem($subject);
        if (null === $workflowItem) {
            return self::ACCESS_ABSTAIN;
        }

        return $workflowItem->getCurrentStep()->getName() === self::STEP_CLOSED
            ? self::ACCESS_DENIED
            : self::ACCESS_ABSTAIN;
    }

    private function getWorkflowItem(Conversation|DomainObjectReference $entity): ?WorkflowItem
    {
        if ($entity instanceof DomainObjectReference) {
            $entity = $this->doctrine->getManagerForClass(Conversation::class)
                ->getReference(Conversation::class, $entity->getIdentifier());
        }
        foreach ($this->workflowManager->getWorkflowItemsByEntity($entity) as $item) {
            if ($item->getWorkflowName() === self::WORKFLOW_NAME) {
                return $item;
            }
        }

        return null;
    }
}
