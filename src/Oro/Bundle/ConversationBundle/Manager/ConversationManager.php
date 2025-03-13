<?php

namespace Oro\Bundle\ConversationBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ApiBundle\Provider\EntityAliasResolverRegistry;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\ConversationBundle\Provider\StorefrontConversationProviderInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Manager class for conversation entity.
 */
class ConversationManager
{
    private EntityRoutingHelper $entityRoutingHelper;
    private EntityNameResolver $entityNameResolver;
    private EntityConfigHelper $entityConfigHelper;
    private OwnershipMetadataProviderInterface $metadataProvider;
    private PropertyAccessor $propertyAccessor;
    private ManagerRegistry $doctrine;
    private StorefrontConversationProviderInterface $storefrontConversationProvider;
    private EntityAliasResolverRegistry  $aliasResolverRegistry;

    public function __construct(
        EntityRoutingHelper $entityRoutingHelper,
        EntityNameResolver $entityNameResolver,
        EntityConfigHelper $entityConfigHelper,
        OwnershipMetadataProviderInterface $metadataProvider,
        PropertyAccessor $propertyAccessor,
        ManagerRegistry $doctrine,
        StorefrontConversationProviderInterface $storefrontConversationProvider,
        EntityAliasResolverRegistry  $aliasResolverRegistry
    ) {
        $this->entityRoutingHelper = $entityRoutingHelper;
        $this->entityNameResolver = $entityNameResolver;
        $this->entityConfigHelper = $entityConfigHelper;
        $this->metadataProvider = $metadataProvider;
        $this->propertyAccessor = $propertyAccessor;
        $this->doctrine = $doctrine;
        $this->storefrontConversationProvider = $storefrontConversationProvider;
        $this->aliasResolverRegistry = $aliasResolverRegistry;
    }

    public function createConversation(?string $sourceEntityClass = null, ?int $sourceEntityId = null): Conversation
    {
        $conversation = $this->getNewConversation();
        if ($sourceEntityClass && $sourceEntityId) {
            $sourceEntity = $this->entityRoutingHelper->getEntity($sourceEntityClass, $sourceEntityId);
            if ($sourceEntity) {
                $conversation->setName($this->getConversationName($sourceEntity));
                $this->setCustomerUserToConversation($conversation, $sourceEntity);
            }
        }
        $this->ensureConversationHaveStatus($conversation);

        return $conversation;
    }

    public function ensureConversationHaveStatus(Conversation $conversation): void
    {
        if (!$conversation->getStatus()) {
            $status = $this->doctrine->getManagerForClass(Conversation::class)
                ->find(ExtendHelper::buildEnumValueClassName('conversation_status'), 'active');
            $conversation->setStatus($status);
        }
    }

    /**
     * @deprecated
     */
    public function saveConversation(Conversation $conversation): Conversation
    {
        $em = $this->doctrine->getManagerForClass(Conversation::class);
        $em->persist($conversation);
        $em->flush();

        return $conversation;
    }

    public function getConversationName(object $sourceEntity): ?string
    {
        if ($sourceEntity) {
            return sprintf(
                '%s %s',
                $this->entityConfigHelper->getLabel($sourceEntity),
                $this->entityNameResolver->getName($sourceEntity)
            );
        }

        return null;
    }

    public function getConversationSourceApiInfo(object $sourceEntity, bool $isBackend = false): array
    {
        $apiRequestAspects = [RequestType::REST, RequestType::JSON_API];
        if (!$isBackend) {
            $apiRequestAspects[] = "frontend";
        }

        return [
            'type' => $this->aliasResolverRegistry->getEntityAliasResolver(new RequestType($apiRequestAspects))
                ->getPluralAlias(ClassUtils::getClass($sourceEntity)),
            'id' => $sourceEntity->getId(),
        ];
    }

    public function hasConversationsBySource(object $sourceEntity): bool
    {
        return (bool) $this->doctrine->getManagerForClass(Conversation::class)
            ->createQueryBuilder()
            ->from(Conversation::class, 'c')
            ->select('1')
            ->where('c.sourceEntityClass = :sourceEntityClass')
            ->andWhere('c.sourceEntityId = :sourceEntityId')
            ->setParameter('sourceEntityClass', ClassUtils::getClass($sourceEntity))
            ->setParameter('sourceEntityId', $sourceEntity->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * @deprecated
     */
    public function getSourceTitle(string $sourceEntityClass, int $sourceEntityId): string
    {
        return $this->entityNameResolver->getName(
            $this->entityRoutingHelper->getEntity($sourceEntityClass, $sourceEntityId)
        );
    }

    /**
     * @deprecated
     */
    public function getStorefrontSourceUrl(string $sourceEntityClass, int $sourceEntityId): string
    {
        return $this->storefrontConversationProvider->getSourceUrl($sourceEntityClass, $sourceEntityId);
    }

    public function setCustomerUserToConversation(
        Conversation $conversation,
        object $sourceEntity
    ): void {
        if ($sourceEntity instanceof CustomerUser) {
            $conversation->setCustomerUser($sourceEntity);
            $conversation->setCustomer($sourceEntity->getCustomer());

            return;
        }

        $ownershipMetadata = $this->metadataProvider->getMetadata(ClassUtils::getClass($sourceEntity));
        if ($ownershipMetadata->getOwnerType() === FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_USER) {
            /** @var CustomerUser $customerUser */
            $customerUser = $this->propertyAccessor->getValue(
                $sourceEntity,
                $ownershipMetadata->getOwnerFieldName()
            );
            if ($customerUser) {
                $conversation->setCustomerUser($customerUser);
                $conversation->setCustomer($customerUser->getCustomer());
            }
        }
    }

    protected function getNewConversation(): Conversation
    {
        return new Conversation();
    }
}
