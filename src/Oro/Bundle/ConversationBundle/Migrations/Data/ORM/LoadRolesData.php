<?php

namespace Oro\Bundle\ConversationBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;

/**
 * Loads ACL data for conversation entity.
 */
class LoadRolesData extends AbstractLoadAclData
{
    #[\Override]
    public function getDataPath(): string
    {
        return '@OroConversationBundle/Migrations/Data/ORM/Roles/roles.yml';
    }

    #[\Override]
    public function load(ObjectManager $manager)
    {
        $configManager = $this->container->get('oro_config.manager');
        // enable conversations feature to be able to load roles data
        // as the role file have data of permissions to workflow `conversation_flow`
        // which disables by this feature toggle
        $configManager->set('oro_conversation.enable_conversation', true);
        $configManager->flush();
        try {
            parent::load($manager);
        } finally {
            // set the default value of the feature
            $configManager->reset('oro_conversation.enable_conversation', false);
            $configManager->flush();
        }
    }
}
