<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Model\WebSocket;

use Oro\Bundle\ConversationBundle\Model\WebSocket\WebSocketSendProcessor;
use Oro\Bundle\SyncBundle\Client\ConnectionChecker;
use Oro\Bundle\SyncBundle\Client\WebsocketClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebSocketSendProcessorTest extends TestCase
{
    private WebsocketClientInterface&MockObject $websocketClient;
    private ConnectionChecker&MockObject $connectionChecker;
    private WebSocketSendProcessor $processor;

    #[\Override]
    protected function setUp(): void
    {
        $this->websocketClient = $this->createMock(WebsocketClientInterface::class);
        $this->connectionChecker = $this->createMock(ConnectionChecker::class);

        $this->processor = new WebSocketSendProcessor($this->websocketClient, $this->connectionChecker);
    }

    public function testGetUserTopic(): void
    {
        self::assertEquals('oro/conversation_event/2/3', WebSocketSendProcessor::getUserTopic(2, 3));
    }

    public function testSendOnEmptyMessages(): void
    {
        $this->connectionChecker->expects(self::never())
            ->method('checkConnection');
        $this->websocketClient->expects(self::never())
            ->method('publish');

        $this->processor->send([]);
    }

    public function testSendOnClosedConnection(): void
    {
        $this->connectionChecker->expects(self::once())
            ->method('checkConnection')
            ->willReturn(false);
        $this->websocketClient->expects(self::never())
            ->method('publish');

        $this->processor->send([1 => [2, 3]]);
    }

    public function testSend(): void
    {
        $this->connectionChecker->expects(self::once())
            ->method('checkConnection')
            ->willReturn(true);
        $this->websocketClient->expects(self::exactly(3))
            ->method('publish')
            ->withConsecutive(
                ['oro/conversation_event/1/2', ['hasNewMessages' => true]],
                ['oro/conversation_event/1/3', ['hasNewMessages' => true]],
                ['oro/conversation_event/10/2', ['hasNewMessages' => true]]
            );

        $this->processor->send([
            1 => [2, 3],
            10 => [2],
        ]);
    }

    public function testSendForUserOnClosedConnection(): void
    {
        $this->connectionChecker->expects(self::once())
            ->method('checkConnection')
            ->willReturn(false);
        $this->websocketClient->expects(self::never())
            ->method('publish');

        $this->processor->sendForUser(1, 2);
    }

    public function testSendForUser(): void
    {
        $this->connectionChecker->expects(self::once())
            ->method('checkConnection')
            ->willReturn(true);
        $this->websocketClient->expects(self::once())
            ->method('publish')
            ->with('oro/conversation_event/1/2', ['hasNewMessages' => true]);

        $this->processor->sendForUser(1, 2);
    }
}
