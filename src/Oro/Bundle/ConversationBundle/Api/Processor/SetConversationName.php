<?php

namespace Oro\Bundle\ConversationBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeFormData\CustomizeFormDataContext;
use Oro\Bundle\ConversationBundle\Manager\ConversationManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Sets the conversation name if it is empty during creating of the conversation API resource.
 */
class SetConversationName implements ProcessorInterface
{
    private ConversationManager $conversationManager;

    public function __construct(ConversationManager $conversationManager)
    {
        $this->conversationManager = $conversationManager;
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeFormDataContext $context */

        /** @var Conversation $conversation */
        $conversation = $context->getData();
        if (null === $conversation->getName()) {
            $sourceEntityClass = $conversation->getSourceEntityClass();
            $sourceEntityId = $conversation->getSourceEntityId();
            if ($sourceEntityClass && $sourceEntityId) {
                $conversation->setName($this->conversationManager->getConversationName(
                    $sourceEntityClass
                ));
            }
        }
    }
}
