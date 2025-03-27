<?php

namespace Oro\Bundle\ConversationBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Tests\Functional\Api\DataFixtures\LoadMessages;

class ConversationTest extends RestJsonApiTestCase
{
    use ConfigManagerAwareTestTrait;

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
            ['entity' => 'conversations']
        );

        $this->assertResponseContains('cget_conversation.yml', $response);
    }

    public function testGet(): void
    {
        $conversation = $this->getReference('conversation-2')->getId();
        $response = $this->get(
            ['entity' => 'conversations', 'id' => (string)$conversation]
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
        $conversation = $this->getEntityManager()->find(Conversation::class, $conversationId);
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
                [
                    'title' => 'not blank constraint',
                    'detail' => 'This value should not be blank.',
                    'source' => ['pointer' => '/data/attributes/name']
                ],
                [
                    'title' => 'not blank constraint',
                    'detail' => 'This value should not be blank.',
                    'source' => ['pointer' => '/data/relationships/customerUser/data']
                ]
            ],
            $response
        );
    }

    public function testTryToCreateWhenCustomerUserDoesNotBelongsToCustomer(): void
    {
        $data = $this->getRequestData('create_conversation.yml');
        $data['data']['relationships']['customer']['data']['id'] = '<toString(@customer.level_1_1->id)>';
        $response = $this->post(
            ['entity' => 'conversations'],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title' => 'customer owner constraint',
                'detail' => 'The customer user does not belong to the customer.'
            ],
            $response
        );
    }

    public function testUpdate(): void
    {
        $conversationId = $this->getReference('conversation-2')->getId();
        $data = [
            'data' => [
                'type' => 'conversations',
                'id' => (string)$conversationId,
                'attributes' => [
                    'name' => 'test_edited_conversation'
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'conversations', 'id' => (string)$conversationId],
            $data
        );

        /** @var Conversation $conversation */
        $conversation = $this->getEntityManager()->find(Conversation::class, $conversationId);
        self::assertEquals('test_edited_conversation', $conversation->getName());
    }

    public function testTryToUpdateWhenCustomerUserDoesNotBelongsToCustomer(): void
    {
        $conversationId = $this->getReference('conversation-2')->getId();
        $response = $this->patch(
            ['entity' => 'conversations', 'id' => (string)$conversationId],
            [
                'data' => [
                    'type' => 'conversations',
                    'id' => (string)$conversationId,
                    'relationships' => [
                        'customer' => [
                            'data' => ['type' => 'customers', 'id' => '<toString(@customer.level_1_1->id)>']
                        ]
                    ]
                ]
            ],
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title' => 'customer owner constraint',
                'detail' => 'The customer user does not belong to the customer.'
            ],
            $response
        );
    }

    public function testDelete(): void
    {
        $conversationId = $this->getReference('conversation-2')->getId();
        $this->delete(
            ['entity' => 'conversations', 'id' => (string)$conversationId]
        );

        /** @var Conversation|null $conversation */
        $deletedConversation = $this->getEntityManager()->find(Conversation::class, $conversationId);
        self::assertTrue(null === $deletedConversation);
    }

    public function testDeleteList(): void
    {
        $this->cdelete(
            ['entity' => 'conversations'],
            ['filter[customerUser]' => $this->getReference('grzegorz.brzeczyszczykiewicz@example.com')->getId()]
        );

        $count = $this->getEntityManager()->getRepository(Conversation::class)->count([]);
        self::assertEquals(0, $count);
    }
}
