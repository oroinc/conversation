<?php

namespace Oro\Bundle\ConversationBundle\Api\Processor;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\ConversationBundle\Provider\StorefrontConversationProviderInterface;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Computes values for "sourceTitle" and "sourceUrl" fields of a conversation.
 */
class ComputeSourceValue implements ProcessorInterface
{
    private const FIELD_SOURCE_CLASS = 'sourceEntityClass';
    private const FIELD_SOURCE_ID = 'sourceEntityId';

    private const FIELD_SOURCE_TITLE = 'sourceTitle';
    private const FIELD_SOURCE_URL = 'sourceUrl';

    public function __construct(
        private EntityNameResolver $entityNameResolver,
        private ManagerRegistry $doctrine,
        private StorefrontConversationProviderInterface $storefrontConversationProvider
    ) {
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeLoadedDataContext $context */
        $data = $context->getData();
        $sourceClassField = $context->getResultFieldName(self::FIELD_SOURCE_CLASS);
        $sourceIdField = $context->getResultFieldName(self::FIELD_SOURCE_ID);
        $sourceClass = $data[$sourceClassField];
        $sourceId = $data[$sourceIdField];
        $hasSource = $sourceClass && $sourceId;

        if ($context->isFieldRequested(self::FIELD_SOURCE_TITLE, $data)) {
            $sourceTitle = null;
            if ($hasSource) {
                $sourceTitle = $this->entityNameResolver->getName(
                    $this->doctrine->getManagerForClass($sourceClass)->find(
                        $sourceClass,
                        $sourceId
                    )
                );
            }
            $data[self::FIELD_SOURCE_TITLE] = $sourceTitle;
        }

        if ($context->isFieldRequested(self::FIELD_SOURCE_URL, $data)) {
            $sourceUrl = null;
            if ($hasSource) {
                $sourceUrl = $this->storefrontConversationProvider->getSourceUrl($sourceClass, $sourceId);
            }
            $data[self::FIELD_SOURCE_URL] = $sourceUrl;
        }

        $context->setData($data);
    }
}
