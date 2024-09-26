<?php

namespace Oro\Bundle\ConversationBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Computes the plain message body field of the conversation message API resource.
 */
class ComputePlainBodyValue implements ProcessorInterface
{
    private const FIELD_PLAIN_BODY = 'plainBody';
    private const FIELD_BODY = 'body';

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeLoadedDataContext $context */

        $data = $context->getData();

        if (!$context->isFieldRequested(self::FIELD_PLAIN_BODY, $data)) {
            return;
        }

        $bodyField = $context->getResultFieldName(self::FIELD_BODY);

        if (!$bodyField || empty($data[$bodyField])) {
            return;
        }

        $data[self::FIELD_PLAIN_BODY] = strip_tags($data[$bodyField]);
        $context->setData($data);
    }
}
