<?php

namespace Oro\Bundle\ConversationBundle\Participant;

/**
 * Returns information about given participant object.
 */
interface ParticipantInfoInterface
{
    public function getAvatarImage(object $participant): array;
    public function getAvatarIcon(object $participant): string;
    public function isItMe(object $participant): bool;
    public function getTitle(object $participant): string;
    public function getAcronym(object $participant): string;
    public function getPosition(object $participant): string;
    public function getTypeString(object $participant): string;
}
