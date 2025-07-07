<?php

namespace Oro\Bundle\ConversationBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ApiBundle\Provider\EntityAliasResolverRegistry;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Manager class for conversation entity.
 */
class ConversationManager
{
    public function __construct(
        private EntityRoutingHelper $entityRoutingHelper,
        private EntityNameResolver $entityNameResolver,
        private EntityConfigHelper $entityConfigHelper,
        private OwnershipMetadataProviderInterface $metadataProvider,
        private PropertyAccessorInterface $propertyAccessor,
        private ManagerRegistry $doctrine,
        private EntityAliasResolverRegistry $aliasResolverRegistry
    ) {
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
            $enumOptionId = ExtendHelper::buildEnumOptionId(Conversation::STATUS_CODE, Conversation::STATUS_ACTIVE);
            $status = $this->doctrine->getManagerForClass(EnumOption::class)->find(EnumOption::class, $enumOptionId);
            $conversation->setStatus($status);
        }
    }

    public function getConversationName(object $sourceEntity): ?string
    {
        if ($sourceEntity) {
            return sprintf(
                '%s %s',
                $this->entityConfigHelper->getLabel($sourceEntity),
                $this->entityNameResolver->getName($sourceEntity, EntityNameProviderInterface::SHORT)
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
