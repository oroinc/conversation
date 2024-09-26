<?php

namespace Oro\Bundle\ConversationBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Computes author related fields for Participant resource:
 * - isMe - boolean field that is true if current logged user is for given participant
 * - author - string representation of the author, f.e. John Doe
 * - authorAcronym - acronym build from the string representation of the author, f.e. JD
 * - authorType - string representation of author entity type, f.e. User, Customer User
 */
class ComputeAuthorFieldsToParticipant implements ProcessorInterface
{
    private const FIELD_IS_ME = 'isMe';
    private const FIELD_AUTHOR = 'author';
    private const FIELD_ACRONYM = 'authorAcronym';
    private const FIELD_TYPE = 'authorType';

    private ConversationParticipantManager $participantManager;

    public function __construct(ConversationParticipantManager $participantManager)
    {
        $this->participantManager = $participantManager;
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeLoadedDataContext $context */

        $data = $context->getData();

        if (!$this->isAnyFieldRequested($context)) {
            return;
        }

        $participantIdFieldName = $context->getResultFieldName('id');
        if (!$participantIdFieldName || empty($data[$participantIdFieldName])) {
            return;
        }

        $participantInfo = $this->participantManager->getParticipantInfoById($data[$participantIdFieldName]);

        if ($participantInfo) {
            if ($context->isFieldRequested(self::FIELD_IS_ME, $data)) {
                $data[self::FIELD_IS_ME] = $participantInfo['isOwnMessage'];
            }

            if ($context->isFieldRequested(self::FIELD_AUTHOR, $data)) {
                $data[self::FIELD_AUTHOR] = $participantInfo['title'];
            }

            if ($context->isFieldRequested(self::FIELD_ACRONYM, $data)) {
                $data[self::FIELD_ACRONYM] = $participantInfo['titleAcronym'];
            }

            if ($context->isFieldRequested(self::FIELD_TYPE, $data)) {
                $data[self::FIELD_TYPE] = $participantInfo['type'];
            }
        }

        $context->setData($data);
    }

    private function isAnyFieldRequested(CustomizeLoadedDataContext $context): bool
    {
        $data = $context->getData();

        return $context->isFieldRequested(self::FIELD_IS_ME, $data)
            || $context->isFieldRequested(self::FIELD_AUTHOR, $data)
            || $context->isFieldRequested(self::FIELD_ACRONYM, $data)
            || $context->isFieldRequested(self::FIELD_TYPE, $data);
    }
}
