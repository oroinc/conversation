<?php

namespace Oro\Bundle\ConversationBundle\Migrations\Data\ORM;

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
}
