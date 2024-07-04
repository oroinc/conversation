<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Acl\Voter;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConversationBundle\Acl\Voter\ManageConversationMessagesVoter;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\SecurityBundle\Authentication\Token\ConsoleToken;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ManageConversationMessagesVoterTest extends TestCase
{
    private WorkflowManager|MockObject $workflowManager;

    private ManageConversationMessagesVoter $voter;

    protected function setUp(): void
    {
        $this->workflowManager = $this->createMock(WorkflowManager::class);
        $doctrine = $this->createMock(ManagerRegistry::class);

        $this->voter = new ManageConversationMessagesVoter($this->workflowManager, $doctrine);
    }

    public function testVoteOnNonObject(): void
    {
        self::assertEquals(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote(new ConsoleToken(), 'string', ['MANAGE_MESSAGES'])
        );
    }

    public function testVoteOnWrongPermission(): void
    {
        self::assertEquals(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote(new ConsoleToken(), new Conversation(), ['VIEW'])
        );
    }

    public function testVoteOnWrongToken(): void
    {
        self::assertEquals(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote(new UsernamePasswordToken(new User(), 'main'), new Conversation(), ['MANAGE_MESSAGES'])
        );
    }

    public function testVoteOnConversationObject(): void
    {
        self::assertEquals(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote(new ConsoleToken(), new \stdClass(), ['MANAGE_MESSAGES'])
        );
    }

    public function testVoteOnNonActiveWorkflow(): void
    {
        $this->workflowManager->expects(self::once())
            ->method('isActiveWorkflow')
            ->with('conversation_flow')
            ->willReturn(false);

        self::assertEquals(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote(new ConsoleToken(), new Conversation(), ['MANAGE_MESSAGES'])
        );
    }

    public function testVoteWithoutWorkflowItem(): void
    {
        $entity = new Conversation();

        $this->workflowManager->expects(self::once())
            ->method('isActiveWorkflow')
            ->with('conversation_flow')
            ->willReturn(true);

        $this->workflowManager->expects(self::once())
            ->method('getWorkflowItemsByEntity')
            ->with($entity)
            ->willReturn([]);

        self::assertEquals(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote(new ConsoleToken(), $entity, ['MANAGE_MESSAGES'])
        );
    }

    public function testVoteWithNonClosedWorkflowItem(): void
    {
        $entity = new Conversation();

        $step = new WorkflowStep();
        $step->setName('open');
        $workflowItem = new WorkflowItem();
        $workflowItem->setWorkflowName('conversation_flow');
        $workflowItem->setCurrentStep($step);

        $this->workflowManager->expects(self::once())
            ->method('isActiveWorkflow')
            ->with('conversation_flow')
            ->willReturn(true);

        $this->workflowManager->expects(self::once())
            ->method('getWorkflowItemsByEntity')
            ->with($entity)
            ->willReturn([$workflowItem]);

        self::assertEquals(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote(new ConsoleToken(), $entity, ['MANAGE_MESSAGES'])
        );
    }

    public function testVoteWithClosedWorkflowItem(): void
    {
        $entity = new Conversation();

        $step = new WorkflowStep();
        $step->setName('closed');
        $workflowItem = new WorkflowItem();
        $workflowItem->setWorkflowName('conversation_flow');
        $workflowItem->setCurrentStep($step);

        $this->workflowManager->expects(self::once())
            ->method('isActiveWorkflow')
            ->with('conversation_flow')
            ->willReturn(true);

        $this->workflowManager->expects(self::once())
            ->method('getWorkflowItemsByEntity')
            ->with($entity)
            ->willReturn([$workflowItem]);

        self::assertEquals(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote(new ConsoleToken(), $entity, ['MANAGE_MESSAGES'])
        );
    }
}
