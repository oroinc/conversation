<?php

namespace Oro\Bundle\ConversationBundle\Provider;

use Doctrine\ORM\Mapping\ClassMetadata;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\EntityBundle\Provider\AbstractExclusionProvider;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

/**
 * The implementation of ExclusionProviderInterface that ignores
 * relations which are a conversationParticipant associations.
 */
class ParticipantExclusionProvider extends AbstractExclusionProvider
{
    public function isIgnoredRelation(ClassMetadata $metadata, $associationName)
    {

        $mapping = $metadata->getAssociationMapping($associationName);
        if (
            !$mapping['isOwningSide']
            || $mapping['sourceEntity'] !== ConversationParticipant::class
            || $mapping['type'] !== ClassMetadata::MANY_TO_ONE
        ) {
            return false;
        }

        $participantAssociationName = ExtendHelper::buildAssociationName(
            $mapping['targetEntity'],
            'conversationParticipant'
        );

        return $associationName === $participantAssociationName;
    }
}
