<?php

namespace Oro\Bundle\ConversationBundle\Participant;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Representation of ParticipantInfoInterface for common participant objects.
 */
class CommonParticipantInfo implements ParticipantInfoInterface
{
    private const DEFAULT_ICON = 'fa_user';

    private EntityNameResolver $nameResolver;
    private TokenAccessorInterface $tokenAccessor;
    private EntityConfigHelper $entityConfigHelper;

    public function __construct(
        EntityNameResolver $nameResolver,
        TokenAccessorInterface $tokenAccessor,
        EntityConfigHelper $entityConfigHelper
    ) {
        $this->nameResolver = $nameResolver;
        $this->tokenAccessor = $tokenAccessor;
        $this->entityConfigHelper = $entityConfigHelper;
    }

    #[\Override]
    public function getAvatarImage(object $participant): array
    {
        return [];
    }

    #[\Override]
    public function getAvatarIcon(object $participant): string
    {
        $icon = $this->entityConfigHelper->getIcon($participant);

        return $icon ?: self::DEFAULT_ICON;
    }

    #[\Override]
    public function isItMe(object $participant): bool
    {
        return $this->tokenAccessor->hasUser()
            && $participant->getId() === $this->tokenAccessor->getUserId()
            && ClassUtils::getClass($participant) === ClassUtils::getClass($this->tokenAccessor->getUser());
    }

    #[\Override]
    public function getTitle(object $participant): string
    {
        return $this->nameResolver->getName($participant);
    }

    #[\Override]
    public function getAcronym(object $participant): string
    {
        $entityTitle = $this->nameResolver->getName($participant);

        $words = explode(" ", $entityTitle);
        $acronym = '';
        foreach ($words as $w) {
            $acronym .= mb_substr($w, 0, 1);
            if (\strlen($acronym) === 2) {
                break;
            }
        }

        return strtoupper($acronym);
    }

    #[\Override]
    public function getPosition(object $participant): string
    {
        return ParticipantInfoProvider::MESSAGE_POSITION_LEFT;
    }

    #[\Override]
    public function getTypeString(object $participant): string
    {
        return $this->entityConfigHelper->getLabel($participant);
    }
}
