<?php

namespace Oro\Bundle\ConversationBundle\Participant;

use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Bundle\AttachmentBundle\Provider\PictureSourcesProviderInterface;
use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Representation of ParticipantInfoInterface for user participants.
 */
class UserParticipantInfo extends CommonParticipantInfo
{
    private PictureSourcesProviderInterface $pictureSourcesProvider;

    public function __construct(
        PictureSourcesProviderInterface $pictureSourcesProvider,
        EntityNameResolver $nameResolver,
        TokenAccessorInterface $tokenAccessor,
        EntityConfigHelper $entityConfigHelper
    ) {
        parent::__construct($nameResolver, $tokenAccessor, $entityConfigHelper);
        $this->pictureSourcesProvider = $pictureSourcesProvider;
    }


    #[\Override]
    public function getAvatarImage(object $participant): array
    {
        return $this->pictureSourcesProvider->getResizedPictureSources(
            $participant->getAvatar(),
            AttachmentManager::SMALL_IMAGE_WIDTH,
            AttachmentManager::SMALL_IMAGE_HEIGHT
        );
    }

    #[\Override]
    public function getPosition(object $participant): string
    {
        return ParticipantInfoProvider::MESSAGE_POSITION_RIGHT;
    }
}
