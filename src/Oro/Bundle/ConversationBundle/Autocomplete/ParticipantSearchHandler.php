<?php

namespace Oro\Bundle\ConversationBundle\Autocomplete;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ActivityBundle\Autocomplete\ContextSearchHandler;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\EntityBundle\Tools\EntityClassNameHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Search handler that searches participant target entities.
 */
class ParticipantSearchHandler extends ContextSearchHandler
{
    private ConversationParticipantManager $conversationParticipantManager;

    public function __construct(
        TranslatorInterface $translator,
        Indexer $indexer,
        ActivityManager $activityManager,
        ConfigManager $configManager,
        EntityClassNameHelper $entityClassNameHelper,
        ObjectManager $objectManager,
        EntityNameResolver $nameResolver,
        EventDispatcherInterface $dispatcher,
        ConversationParticipantManager $conversationParticipantManager
    ) {
        parent::__construct(
            $translator,
            $indexer,
            $activityManager,
            $configManager,
            $entityClassNameHelper,
            $objectManager,
            $nameResolver,
            $dispatcher
        );

        $this->conversationParticipantManager = $conversationParticipantManager;
    }

    #[\Override]
    protected function getSearchAliases(): array
    {
        return array_values($this->indexer->getEntityAliases(
            $this->conversationParticipantManager->getParticipantTargetClasses()
        ));
    }
}
