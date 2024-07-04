<?php

namespace Oro\Bundle\ConversationBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ActivityListBundle\Migrations\Data\ORM\AddActivityListsData;
use Oro\Bundle\ConversationBundle\Entity\Conversation;

/**
 * Adds activity lists for Conversation entity.
 */
class AddConversationActivityLists extends AddActivityListsData
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $this->addActivityListsForActivityClass(
            $manager,
            Conversation::class
        );
    }
}
