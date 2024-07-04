<?php

namespace Oro\Bundle\ConversationBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroConversationBundle_Entity_ConversationMessage;
use Oro\Bundle\ConversationBundle\Entity\Repository\ConversationMessageRepository;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Represents the conversation message.
 *
 * @method AbstractEnumValue getType()
 * @method Conversation setType(AbstractEnumValue $type)
 * @mixin OroConversationBundle_Entity_ConversationMessage
 */
#[ORM\Entity(repositoryClass: ConversationMessageRepository::class)]
#[ORM\Table(name: 'oro_conversation_message')]
#[Config(defaultValues: ['entity' => ['icon' => 'fa-commenting-o']])]
class ConversationMessage implements DatesAwareInterface, ExtendEntityInterface
{
    use DatesAwareTrait;
    use ExtendEntityTrait;

    public const TYPE_SYSTEM = 'system';
    public const TYPE_TEXT = 'text';

    public const MESSAGE_TYPE_ENUM_CODE = 'conversation_message_type';

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'index', type: Types::INTEGER)]
    private ?int $index = 0;

    #[ORM\Column(name: 'body', type: Types::TEXT, nullable: true)]
    private string $body = '';

    #[ORM\ManyToOne(targetEntity: Conversation::class, cascade: ['persist'], inversedBy: 'messages')]
    #[ORM\JoinColumn(name: 'conversation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne(targetEntity: ConversationParticipant::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'participant_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ConversationParticipant $participant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIndex(): ?int
    {
        return $this->index;
    }

    public function setIndex(?int $index): self
    {
        $this->index = $index;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

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

    public function getParticipant(): ?ConversationParticipant
    {
        return $this->participant;
    }

    public function setParticipant(?ConversationParticipant $participant): void
    {
        $this->participant = $participant;
    }
}
