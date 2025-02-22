<?php

namespace Oro\Bundle\ConversationBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Tests\Functional\Api\DataFixtures\LoadMessages;
use Symfony\Component\HttpFoundation\Response;

class ConversationMessagesTest extends RestJsonApiTestCase
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
            ['entity' => 'conversationmessages']
        );

        $this->assertResponseContains('cget_conversationmessage.yml', $response);
    }

    public function testGet(): void
    {
        $conversationMessage = $this->getReference('conversationmessage-1-2')->getId();
        $response = $this->get(
            ['entity' => 'conversationmessages', 'id' => $conversationMessage]
        );

        $this->assertResponseContains('get_conversationmessage.yml', $response);
    }

    public function testCreate(): void
    {
        $data = $this->getRequestData('create_message.yml');
        $response = $this->post(
            ['entity' => 'conversationmessages'],
            $data
        );

        $messageId = (int)$this->getResourceId($response);
        $responseContent = $this->updateResponseContent('create_message.yml', $response);
        $this->assertResponseContains($responseContent, $response);

        /** @var ConversationMessage $message */
        $message = $this->getEntityManager()
            ->find(ConversationMessage::class, $messageId);

        self::assertEquals($data['data']['attributes']['body'], $message->getBody());
    }

    public function testTryToUpdate(): void
    {
        $message = $this->getReference('conversationmessage-1-2')->getId();

        $data = [
            'data' => [
                'type'          => 'conversationmessages',
                'id'            => (string)$message,
                'attributes' => [
                    'body' => 'edited'
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => 'conversationmessages', 'id' => $message],
            $data,
            [],
            false
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testTryToDelete(): void
    {
        $response = $this->delete(
            ['entity' => 'conversationmessages', 'id' => $this->getReference('conversationmessage-1-2')->getId()],
            [],
            [],
            false
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testTryToDeleteList(): void
    {
        $response = $this->cdelete(
            ['entity' => 'conversationmessages'],
            ['filter[id]' => $this->getReference('conversationmessage-1-2')->getId()],
            [],
            false
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_METHOD_NOT_ALLOWED);
    }
}
