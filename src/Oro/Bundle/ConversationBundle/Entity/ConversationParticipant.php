<?php

namespace Oro\Bundle\ConversationBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroConversationBundle_Entity_ConversationParticipant;
use Oro\Bundle\ConversationBundle\Entity\Repository\ConversationParticipantRepository;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Represents the conversation participant.
 *
 * @method bool supportConversationParticipantTarget($value)
 * @method getConversationParticipantTarget()
 * @method setConversationParticipantTarget($value)
 * @mixin OroConversationBundle_Entity_ConversationParticipant
 */
#[ORM\Entity(repositoryClass: ConversationParticipantRepository::class)]
#[ORM\Table(name: 'oro_conversation_participant')]
#[Config(defaultValues: ['entity' => ['icon' => 'fa-commenting-o']])]
class ConversationParticipant implements DatesAwareInterface, ExtendEntityInterface
{
    use DatesAwareTrait;
    use ExtendEntityTrait;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Conversation::class, cascade: ['persist'], inversedBy: 'participants')]
    #[ORM\JoinColumn(name: 'conversation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne(targetEntity: ConversationMessage::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'last_read_message_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ConversationMessage $lastReadMessage = null;

    #[ORM\Column(name: 'last_read_message_index', type: Types::INTEGER)]
    private ?int $lastReadMessageIndex = 0;

    #[ORM\Column(name: 'last_read_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $lastReadDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastReadMessage(): ?ConversationMessage
    {
        return $this->lastReadMessage;
    }

    public function setLastReadMessage(ConversationMessage $lastReadMessage): self
    {
        $this->lastReadMessage = $lastReadMessage;

        return $this;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(Conversation $conversation): self
    {
        $this->conversation = $conversation;

        return $this;
    }

    public function getLastReadMessageIndex(): int
    {
        return $this->lastReadMessageIndex;
    }

    public function setLastReadMessageIndex(int $lastReadMessageIndex): self
    {
        $this->lastReadMessageIndex = $lastReadMessageIndex;

        return $this;
    }

    public function getLastReadDate(): ?\DateTime
    {
        return $this->lastReadDate;
    }

    public function setLastReadDate(?\DateTime $lastReadDate): self
    {
        $this->lastReadDate = $lastReadDate;

        return $this;
    }
}
