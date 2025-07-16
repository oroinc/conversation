<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Participant;

use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\ConversationBundle\Participant\CommonParticipantInfo;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CommonParticipantInfoTest extends TestCase
{
    use EntityTrait;

    private EntityNameResolver&MockObject $nameResolver;
    private TokenAccessorInterface&MockObject $tokenAccessor;
    private EntityConfigHelper&MockObject $entityConfigHelper;
    private User $user;
    private CommonParticipantInfo $participantInfo;

    #[\Override]
    protected function setUp(): void
    {
        $this->nameResolver = $this->createMock(EntityNameResolver::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->entityConfigHelper = $this->createMock(EntityConfigHelper::class);

        $this->user = $this->getEntity(User::class, ['id' => 123]);

        $this->participantInfo = new CommonParticipantInfo(
            $this->nameResolver,
            $this->tokenAccessor,
            $this->entityConfigHelper
        );
    }

    public function testGetAvatarImage(): void
    {
        self::assertEquals([], $this->participantInfo->getAvatarImage($this->user));
    }


    public function testGetAvatarIconForEntityWithoutConfiguredIcon(): void
    {
        $this->entityConfigHelper->expects(self::once())
            ->method('getIcon')
            ->willReturn(null);

        self::assertEquals('fa_user', $this->participantInfo->getAvatarIcon($this->user));
    }

    public function testGetAvatarIcon(): void
    {
        $this->entityConfigHelper->expects(self::once())
            ->method('getIcon')
            ->willReturn('fa_icon');

        self::assertEquals('fa_icon', $this->participantInfo->getAvatarIcon($this->user));
    }

    public function testIsAmWithoutUserInToken(): void
    {
        $this->tokenAccessor->expects(self::once())
            ->method('hasUser')
            ->willReturn(false);

        self::assertFalse($this->participantInfo->isItMe($this->user));
    }


    public function testIsAmWithAnotherUserId(): void
    {
        $this->tokenAccessor->expects(self::once())
            ->method('hasUser')
            ->willReturn(true);

        $this->tokenAccessor->expects(self::once())
            ->method('getUserId')
            ->willReturn(555);

        self::assertFalse($this->participantInfo->isItMe($this->user));
    }

    public function testIsAmWithAnotherUserType(): void
    {
        $this->tokenAccessor->expects(self::once())
            ->method('hasUser')
            ->willReturn(true);

        $this->tokenAccessor->expects(self::once())
            ->method('getUserId')
            ->willReturn(123);


        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn(new \stdClass());

        self::assertFalse($this->participantInfo->isItMe($this->user));
    }

    public function testIsAm(): void
    {
        $this->tokenAccessor->expects(self::once())
            ->method('hasUser')
            ->willReturn(true);

        $this->tokenAccessor->expects(self::once())
            ->method('getUserId')
            ->willReturn(123);


        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($this->user);

        self::assertTrue($this->participantInfo->isItMe($this->user));
    }

    public function testGetTitle(): void
    {
        $this->nameResolver->expects(self::once())
            ->method('getName')
            ->with($this->user)
            ->willReturn('name');

        self::assertEquals('name', $this->participantInfo->getTitle($this->user));
    }

    public function testGetAcronym(): void
    {
        $this->nameResolver->expects(self::once())
            ->method('getName')
            ->with($this->user)
            ->willReturn('super Puper user');

        self::assertEquals('SP', $this->participantInfo->getAcronym($this->user));
    }

    public function testGetPosition(): void
    {
        self::assertEquals(
            ParticipantInfoProvider::MESSAGE_POSITION_LEFT,
            $this->participantInfo->getPosition($this->user)
        );
    }

    public function testGetTypeString(): void
    {
        $this->entityConfigHelper->expects(self::once())
            ->method('getLabel')
            ->with($this->user)
            ->willReturn('User');

        self::assertEquals('User', $this->participantInfo->getTypeString($this->user));
    }
}
