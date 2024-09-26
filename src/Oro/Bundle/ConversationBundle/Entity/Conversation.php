<?php

namespace Oro\Bundle\ConversationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroConversationBundle_Entity_Conversation;
use Oro\Bundle\ActivityBundle\Model\ActivityInterface;
use Oro\Bundle\ActivityBundle\Model\ExtendActivity;
use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\Ownership\AuditableFrontendCustomerUserAwareTrait;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Represents the conversations.
 *
 * @method AbstractEnumValue getStatus()
 * @method Conversation setStatus(AbstractEnumValue $status)
 *
 * @mixin OroConversationBundle_Entity_Conversation
 */
#[ORM\Entity]
#[ORM\Table(name: 'oro_conversation')]
#[ORM\Index(columns: ['source_entity_class', 'source_entity_id'], name: 'conversation_source_idx')]
#[Config(
    routeName: 'oro_conversation_index',
    routeView: 'oro_conversation_view',
    defaultValues: [
        'entity'    => ['icon' => 'fa-commenting-o'],
        'security'  => ['type' => 'ACL', 'group_name' => 'commerce', 'category' => 'account_management'],
        'ownership' => [
            'owner_type' => 'USER',
            'owner_field_name'  => 'owner',
            'owner_column_name' => 'user_owner_id',
            'organization_field_name'  => 'organization',
            'organization_column_name' => 'organization_id',
            'frontend_owner_type' => 'FRONTEND_USER',
            'frontend_owner_field_name' => 'customerUser',
            'frontend_owner_column_name' => 'customer_user_id',
            'frontend_customer_field_name' => 'customer',
            'frontend_customer_column_name' => 'customer_id'
        ],
        'grouping'  => ['groups' => ['activity']],
        'dataaudit' => ['auditable' => true],
        'workflow'  => ['show_step_in_grid' => false],
        'activity'  => [
            'route'                => 'oro_conversation_activity_view',
            'acl'                  => 'oro_conversation_view',
            'action_button_widget' => 'oro_add_conversation_button',
            'action_link_widget'   => 'oro_add_conversation_link',
        ],
        'grid'      => ['default' => 'conversations-grid'],
    ]
)]
class Conversation implements
    DatesAwareInterface,
    ActivityInterface,
    ExtendEntityInterface,
    CustomerOwnerAwareInterface
{
    use DatesAwareTrait;
    use ExtendEntityTrait;
    use ExtendActivity;
    use AuditableFrontendCustomerUserAwareTrait;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    private ?string $name = null;

    #[ORM\Column(name: 'messages_number', type: Types::INTEGER)]
    private ?int $messagesNumber = 0;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    private ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    private ?Organization $organization = null;

    #[ORM\Column(name: 'source_entity_class', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    private ?string $sourceEntityClass = null;

    #[ORM\Column(name: 'source_entity_id', type: Types::INTEGER, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    private ?int $sourceEntityId = null;

    #[ORM\ManyToOne(targetEntity: ConversationMessage::class)]
    #[ORM\JoinColumn(name: 'last_message_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    private ?ConversationMessage $lastMessage = null;

    /**
     * @var Collection<int, ConversationMessage>
     */
    #[ORM\OneToMany(
        mappedBy: 'conversation',
        targetEntity: ConversationMessage::class,
        cascade: ['ALL'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['index' => Criteria::DESC])]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    private ?Collection $messages = null;

    /**
     * @var Collection<int, ConversationParticipant>
     */
    #[ORM\OneToMany(
        mappedBy: 'conversation',
        targetEntity: ConversationParticipant::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    private ?Collection $participants = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setMessagesNumber(?int $messagesNumber): self
    {
        $this->messagesNumber = $messagesNumber;

        return $this;
    }

    public function getMessagesNumber(): ?int
    {
        return $this->messagesNumber;
    }

    public function setOwner(User $owner = null): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }


    public function setOrganization(Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(ConversationMessage $message): self
    {
        $this->messages->add($message);
        $message->setConversation($this);

        return $this;
    }

    public function removeMessage(ConversationMessage $message): self
    {
        $this->messages->removeElement($message);

        return $this;
    }

    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(ConversationParticipant $participant): self
    {
        $this->participants->add($participant);
        $participant->setConversation($this);

        return $this;
    }

    public function removeParticipant(ConversationParticipant $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getSourceEntityClass(): ?string
    {
        return $this->sourceEntityClass;
    }

    public function setSourceEntityClass(string $sourceEntityClass): self
    {
        $this->sourceEntityClass = $sourceEntityClass;

        return $this;
    }

    public function getSourceEntityId(): ?int
    {
        return $this->sourceEntityId;
    }

    public function setSourceEntityId(int $sourceEntityId): self
    {
        $this->sourceEntityId = $sourceEntityId;

        return $this;
    }

    public function setSourceEntity(?string $entityClass, ?int $entityId): self
    {
        $this->sourceEntityClass = $entityClass;
        $this->sourceEntityId = $entityId;

        return $this;
    }

    public function getLastMessage(): ?ConversationMessage
    {
        return $this->lastMessage;
    }

    public function setLastMessage(?ConversationMessage $lastMessage): void
    {
        $this->lastMessage = $lastMessage;
    }
}
