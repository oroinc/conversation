<?php

namespace Oro\Bundle\ConversationBundle\Model\WebSocket;

use Oro\Bundle\SyncBundle\Client\ConnectionChecker;
use Oro\Bundle\SyncBundle\Client\WebsocketClientInterface;

/**
 * Sends messages about new messages to websocket server.
 */
class WebSocketSendProcessor
{
    const TOPIC = 'oro/conversation_event/%s/%s';

    public function __construct(
        private WebsocketClientInterface $websocketClient,
        private ConnectionChecker $connectionChecker
    ) {
    }

    public static function getUserTopic(int $userId, int $organizationId): string
    {
        return sprintf(
            self::TOPIC,
            $userId,
            $organizationId
        );
    }

    public function send(array $usersWithNewMessages): void
    {
        if (count($usersWithNewMessages) && $this->connectionChecker->checkConnection()) {
            foreach ($usersWithNewMessages as $userId => $organizations) {
                foreach ($organizations as $organization) {
                    $this->websocketClient->publish(
                        self::getUserTopic($userId, $organization),
                        ['hasNewMessages' => true]
                    );
                }
            }
        }
    }

    public function sendForUser(int $userId, int $organizationId): void
    {
        if (!$this->connectionChecker->checkConnection()) {
            return;
        }

        $this->websocketClient->publish(
            self::getUserTopic($userId, $organizationId),
            ['hasNewMessages' => true]
        );
    }
}
