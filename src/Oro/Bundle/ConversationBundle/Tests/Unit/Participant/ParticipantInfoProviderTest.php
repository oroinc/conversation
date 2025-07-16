<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Participant;

use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoInterface;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class ParticipantInfoProviderTest extends TestCase
{
    private ParticipantInfoInterface&MockObject $userParticipantInfoProvider;
    private ParticipantInfoInterface&MockObject $commonProvider;
    private ParticipantInfoProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->userParticipantInfoProvider = $this->createMock(ParticipantInfoInterface::class);
        $this->commonProvider = $this->createMock(ParticipantInfoInterface::class);

        $container = new Container();
        $container->set(User::class, $this->userParticipantInfoProvider);

        $this->provider = new ParticipantInfoProvider($container, $this->commonProvider);
    }

    public function testGetParticipantInfoForUserEntity(): void
    {
        $entity = new User();

        $this->userParticipantInfoProvider->expects(self::once())
            ->method('isItMe')
            ->with($entity)
            ->willReturn(true);
        $this->userParticipantInfoProvider->expects(self::once())
            ->method('getTitle')
            ->with($entity)
            ->willReturn('Some User Title');
        $this->userParticipantInfoProvider->expects(self::once())
            ->method('getAcronym')
            ->with($entity)
            ->willReturn('SU');
        $this->userParticipantInfoProvider->expects(self::once())
            ->method('getAvatarImage')
            ->with($entity)
            ->willReturn([]);
        $this->userParticipantInfoProvider->expects(self::once())
            ->method('getAvatarIcon')
            ->with($entity)
            ->willReturn('fa_user');
        $this->userParticipantInfoProvider->expects(self::once())
            ->method('getPosition')
            ->with($entity)
            ->willReturn('left');
        $this->userParticipantInfoProvider->expects(self::once())
             ->method('getTypeString')
             ->with($entity)
             ->willReturn('User');

        $this->commonProvider->expects(self::never())
            ->method('isItMe');
        $this->commonProvider->expects(self::never())
            ->method('getTitle');
        $this->commonProvider->expects(self::never())
            ->method('getAcronym');
        $this->commonProvider->expects(self::never())
            ->method('getAvatarImage');
        $this->commonProvider->expects(self::never())
            ->method('getAvatarIcon');
        $this->commonProvider->expects(self::never())
            ->method('getPosition');
        $this->commonProvider->expects(self::never())
            ->method('getTypeString');


        self::assertEquals(
            [
                'isOwnMessage' => true,
                'title' => 'Some User Title',
                'titleAcronym' =>  'SU',
                'avatarImage' =>  [],
                'avatarIcon' =>  'fa_user',
                'position' => 'left',
                'type' =>  'User'
            ],
            $this->provider->getParticipantInfo($entity)
        );
    }

    public function testGetParticipantInfoFromCommonProvider(): void
    {
        $entity = new \stdClass();

        $this->commonProvider->expects(self::once())
            ->method('isItMe')
            ->with($entity)
            ->willReturn(false);
        $this->commonProvider->expects(self::once())
            ->method('getTitle')
            ->with($entity)
            ->willReturn('Another user');
        $this->commonProvider->expects(self::once())
            ->method('getAcronym')
            ->with($entity)
            ->willReturn('AU');
        $this->commonProvider->expects(self::once())
            ->method('getAvatarImage')
            ->with($entity)
            ->willReturn([]);
        $this->commonProvider->expects(self::once())
            ->method('getAvatarIcon')
            ->with($entity)
            ->willReturn('fa_user1');
        $this->commonProvider->expects(self::once())
            ->method('getPosition')
            ->with($entity)
            ->willReturn('right');
        $this->commonProvider->expects(self::once())
            ->method('getTypeString')
            ->with($entity)
            ->willReturn('Another UserType');

        $this->userParticipantInfoProvider->expects(self::never())
            ->method('isItMe');
        $this->userParticipantInfoProvider->expects(self::never())
            ->method('getTitle');
        $this->userParticipantInfoProvider->expects(self::never())
            ->method('getAcronym');
        $this->userParticipantInfoProvider->expects(self::never())
            ->method('getAvatarImage');
        $this->userParticipantInfoProvider->expects(self::never())
            ->method('getAvatarIcon');
        $this->userParticipantInfoProvider->expects(self::never())
            ->method('getPosition');
        $this->userParticipantInfoProvider->expects(self::never())
            ->method('getTypeString');


        self::assertEquals(
            [
                'isOwnMessage' => false,
                'title' => 'Another user',
                'titleAcronym' =>  'AU',
                'avatarImage' =>  [],
                'avatarIcon' =>  'fa_user1',
                'position' => 'right',
                'type' =>  'Another UserType'
            ],
            $this->provider->getParticipantInfo($entity)
        );
    }
}
