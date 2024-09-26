<?php

namespace Oro\Bundle\ConversationBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Tests\Functional\Api\DataFixtures\LoadMessages;

class ConversationTest extends RestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([LoadMessages::class]);
    }

    public function testGetList(): void
    {
        $response = $this->cget(
            ['entity' => 'conversations']
        );

        $this->assertResponseContains('cget_conversation.yml', $response);
    }

    public function testGet(): void
    {
        $conversation = $this->getReference('conversation-2')->getId();
        $response = $this->get(
            ['entity' => 'conversations', 'id' => $conversation]
        );

        $this->assertResponseContains('get_conversation.yml', $response);
    }

    public function testCreate(): void
    {
        $data = $this->getRequestData('create_conversation.yml');
        $response = $this->post(
            ['entity' => 'conversations'],
            $data
        );

        $conversationId = (int)$this->getResourceId($response);
        $responseContent = $this->updateResponseContent('create_conversation.yml', $response);
        $this->assertResponseContains($responseContent, $response);

        /** @var Conversation $conversation */
        $conversation = $this->getEntityManager()
            ->find(Conversation::class, $conversationId);

        self::assertEquals($data['data']['attributes']['name'], $conversation->getName());
    }

    public function testTryToCreateWithoutData(): void
    {
        $response = $this->post(
            ['entity' => 'conversations'],
            ['data' => ['type' => 'conversations']],
            [],
            false
        );

        $this->assertResponseValidationErrors(
            [
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/attributes/name']],
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/relationships/customerUser/data']]
            ],
            $response
        );
    }

    public function testUpdate(): void
    {
        $conversationId = $this->getReference('conversation-2')->getId();

        $data = [
            'data' => [
                'type'          => 'conversations',
                'id'            => (string)$conversationId,
                'attributes'    => [
                    'name' => 'test_edited_conversation',
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'conversations', 'id' => $conversationId],
            $data
        );

        $conversation = $this->getEntityManager()
            ->find(Conversation::class, $conversationId);
        self::assertEquals('test_edited_conversation', $conversation->getName());
    }

    public function testDelete(): void
    {
        $conversation = $this->getReference('conversation-2')->getId();

        $this->delete(
            ['entity' => 'conversations', 'id' => $conversation]
        );

        $deletedConversation = $this->getEntityManager()
            ->find(Conversation::class, $conversation);
        self::assertTrue(null === $deletedConversation);
    }

    public function testDeleteList(): void
    {
        $this->cdelete(
            ['entity' => 'conversations'],
            ['filter[customerUser]' => $this->getReference('grzegorz.brzeczyszczykiewicz@example.com')->getId()]
        );

        $count = $this->getEntityManager()
            ->getRepository(Conversation::class)
            ->count([]);
        self::assertEquals(0, $count);
    }
}
