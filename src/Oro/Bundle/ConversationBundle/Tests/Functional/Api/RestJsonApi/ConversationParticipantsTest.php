<?php

namespace Oro\Bundle\ConversationBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\ConversationBundle\Tests\Functional\Api\DataFixtures\LoadMessages;
use Symfony\Component\HttpFoundation\Response;

class ConversationParticipantsTest extends RestJsonApiTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadMessages::class]);
        $this->setEnableConversationFeature(true);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->setEnableConversationFeature(false);
        parent::tearDown();
    }

    private function setEnableConversationFeature(bool $value): void
    {
        $configManager = self::getConfigManager();
        $configManager->set('oro_conversation.enable_conversation', $value);
        $configManager->flush();
    }

    public function testGetList(): void
    {
        $response = $this->cget(
            ['entity' => 'conversationparticipants']
        );

        $this->assertResponseContains('cget_conversationparticipant.yml', $response);
    }

    public function testGet(): void
    {
        $conversationParticipant = $this->getReference('participant-5-admin')->getId();
        $response = $this->get(
            ['entity' => 'conversationparticipants', 'id' => $conversationParticipant]
        );

        $this->assertResponseContains('get_conversationparticipant.yml', $response);
    }

    public function testTryToCreate(): void
    {
        $response = $this->post(
            ['entity' => 'conversationparticipants'],
            ['data' => ['type' => 'conversationparticipants']],
            [],
            false
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testUpdate(): void
    {
        $participant = $this->getReference('participant-1-admin')->getId();
        $message = $this->getReference('conversationmessage-1-10');

        $data = [
            'data' => [
                'type'          => 'conversationparticipants',
                'id'            => (string)$participant,
                'relationships' => [
                    'lastReadMessage' => [
                        'data' => [
                            'type' => 'conversationmessages',
                            'id'   => (string)$message->getId()
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => 'conversationparticipants', 'id' => $participant],
            $data
        );

        $this->assertResponseContains('update_conversationparticipant.yml', $response);

        $updatedParticipant = $this->getEntityManager()
            ->find(ConversationParticipant::class, $participant);
        self::assertEquals(10, $updatedParticipant->getLastReadMessageIndex());
        self::assertEquals($message->getId(), $updatedParticipant->getLastReadMessage()->getId());
    }

    public function testTryToDelete(): void
    {
        $response = $this->delete(
            ['entity' => 'conversationparticipants', 'id' => $this->getReference('participant-1-admin')->getId()],
            [],
            [],
            false
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testTryToDeleteList(): void
    {
        $response = $this->cdelete(
            ['entity' => 'conversationparticipants'],
            ['filter[id]' => $this->getReference('participant-1-admin')->getId()],
            [],
            false
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_METHOD_NOT_ALLOWED);
    }
}
