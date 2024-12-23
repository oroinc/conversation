<?php

namespace Oro\Bundle\ConversationBundle\Datagrid;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\DataGridBundle\Entity\GridView;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\EnumFilterType;

/**
 * Return the grid view configuration for conversations datagrid.
 */
class ConversationViewList extends AbstractViewsList
{
    private const GRID_NAME = 'conversations-grid';

    protected $systemViews = [
        'my_conversations' => [
            'name' => 'my_conversations',
            'label' => 'oro.conversation.datagrid.views.my',
            'is_default' => false,
            'grid_name' => self::GRID_NAME,
            'type' => GridView::TYPE_PUBLIC,
            'filters' => [
                'is_my_conversation' => [
                    'value' => BooleanFilterType::TYPE_YES,
                ],
                'status' => [
                    'type' => EnumFilterType::TYPE_IN,
                    'value' => ['conversation_status.' . Conversation::STATUS_ACTIVE]
                ]
            ],
            'sorters' => [],
            'columns' => []
        ]
    ];

    #[\Override]
    protected function getViewsList()
    {
        return $this->getSystemViewsList();
    }
}
