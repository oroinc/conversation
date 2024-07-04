<?php

namespace Oro\Bundle\ConversationBundle\EntityConfig;

use Oro\Bundle\EntityConfigBundle\EntityConfig\EntityConfigInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Provides validations entity config for conversation_participant scope.
 */
class ConversationParticipantConfiguration implements EntityConfigInterface
{
    #[\Override]
    public function getSectionName(): string
    {
        return 'conversation_participant';
    }

    #[\Override]
    public function configure(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->node('enabled', 'normalized_boolean')
                ->info('`boolean` indicates whether the entity can be a conversation participant. By default false.')
                ->defaultFalse()
            ->end()
            ->node('immutable', 'normalized_boolean')
                ->info(
                    '`boolean` can be used to prohibit changing the conversation participant status (regardless of '
                    . 'whether it is enabled or not). If TRUE, then the current status cannot be changed.'
                    . ' By default is true.'
                )
            ->end();
    }
}
