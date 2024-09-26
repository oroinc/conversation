<?php

namespace Oro\Bundle\ConversationBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\ConversationBundle\Manager\ConversationManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Computes source related data fields of the conversation API resource.
 */
class ComputeSourceValue implements ProcessorInterface
{
    private const FIELD_SOURCE_CLASS = 'sourceEntityClass';
    private const FIELD_SOURCE_ID = 'sourceEntityId';
    private const FIELD_SOURCE_TITLE = 'sourceTitle';
    private const FIELD_SOURCE_URL = 'sourceUrl';

    private ConversationManager $conversationManager;

    public function __construct(ConversationManager $conversationManager)
    {
        $this->conversationManager = $conversationManager;
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeLoadedDataContext $context */

        $data = $context->getData();

        if (!$context->isFieldRequested(self::FIELD_SOURCE_TITLE, $data)
            && !$context->isFieldRequested(self::FIELD_SOURCE_URL, $data)
        ) {
            return;
        }

        $sourceClassField = $context->getResultFieldName(self::FIELD_SOURCE_CLASS);
        $sourceIdField = $context->getResultFieldName(self::FIELD_SOURCE_ID);

        if (!$sourceClassField
            || empty($data[$sourceClassField])
            || !$sourceIdField
            || empty($data[$sourceIdField])
        ) {
            return;
        }

        if ($context->isFieldRequested(self::FIELD_SOURCE_TITLE, $data)) {
            $data[self::FIELD_SOURCE_TITLE] = $this->conversationManager->getSourceTitle(
                $data[$sourceClassField],
                $data[$sourceIdField]
            );
        }

        if ($context->isFieldRequested(self::FIELD_SOURCE_URL, $data)) {
            $data[self::FIELD_SOURCE_URL] = $this->conversationManager->getStorefrontSourceUrl(
                $data[$sourceClassField],
                $data[$sourceIdField]
            );
        }

        $context->setData($data);
    }
}
