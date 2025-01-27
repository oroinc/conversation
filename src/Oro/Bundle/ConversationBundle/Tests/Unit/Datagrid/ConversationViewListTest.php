<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\ConversationBundle\Datagrid\ConversationViewList;
use Oro\Bundle\DataGridBundle\Extension\GridViews\View;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConversationViewListTest extends TestCase
{
    private ConversationViewList $viewList;

    #[\Override]
    protected function setUp(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::any())
            ->method('trans')
            ->willReturnCallback(function ($key) {
                return sprintf('*%s*', $key);
            });

        $this->viewList = new ConversationViewList($translator);
    }

    public function testGetList(): void
    {
        $view = new View(
            'my_conversations',
            [
                'is_my_conversation' => [
                    'value' => BooleanFilterType::TYPE_YES
                ],
                'status' => [
                    'type' => 1,
                    'value' => ['active']
                ]
            ]
        );
        $view->setLabel('*oro.conversation.datagrid.views.my*');

        self::assertEquals(new ArrayCollection([$view]), $this->viewList->getList());
    }
}
