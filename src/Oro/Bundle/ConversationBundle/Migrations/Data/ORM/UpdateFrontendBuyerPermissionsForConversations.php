<?php

namespace Oro\Bundle\ConversationBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Migrations\Data\ORM\LoadCustomerUserRoles;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractUpdatePermissions;

/**
 * Sets permissions for conversations in buyer storefront role.
 */
class UpdateFrontendBuyerPermissionsForConversations extends AbstractUpdatePermissions implements
    DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [LoadCustomerUserRoles::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $aclManager = $this->getAclManager();
        if (!$aclManager->isAclEnabled()) {
            return;
        }

        $this->setEntityPermissions(
            $aclManager,
            $this->getRole($manager, 'ROLE_FRONTEND_BUYER', CustomerUserRole::class),
            Conversation::class,
            ['VIEW_BASIC', 'CREATE_BASIC', 'EDIT_BASIC', 'DELETE_BASIC', 'ASSIGN_BASIC']
        );
        $aclManager->flush();
    }
}
