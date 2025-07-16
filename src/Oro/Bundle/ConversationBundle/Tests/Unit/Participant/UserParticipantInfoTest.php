<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Participant;

use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Provider\PictureSourcesProviderInterface;
use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Oro\Bundle\ConversationBundle\Participant\UserParticipantInfo;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Tests\Unit\Stub\AvatarAwareUserStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserParticipantInfoTest extends TestCase
{
    private PictureSourcesProviderInterface&MockObject $pictureSourcesProvider;
    private UserParticipantInfo $userParticipantInfo;

    #[\Override]
    protected function setUp(): void
    {
        $this->pictureSourcesProvider = $this->createMock(PictureSourcesProviderInterface::class);
        $this->userParticipantInfo = new UserParticipantInfo(
            $this->pictureSourcesProvider,
            $this->createMock(EntityNameResolver::class),
            $this->createMock(TokenAccessorInterface::class),
            $this->createMock(EntityConfigHelper::class)
        );
    }

    public function testGetAvatarImage(): void
    {
        $expectedResult =             [
            'src' => '/url/for/resized/image.png',
            'sources' => [
                [
                    'srcset' => '/url/for/resized/image.jpg',
                    'type' => 'image/jpg',
                ]
            ]
        ];

        $avatar = new File();
        $user = new AvatarAwareUserStub();
        $user->setAvatar($avatar);

        $this->pictureSourcesProvider->expects(self::once())
            ->method('getResizedPictureSources')
            ->with($avatar, 32, 32)
            ->willReturn($expectedResult);

        self::assertEquals(
            $expectedResult,
            $this->userParticipantInfo->getAvatarImage($user)
        );
    }

    public function testGetPosition(): void
    {
        self::assertEquals(
            ParticipantInfoProvider::MESSAGE_POSITION_RIGHT,
            $this->userParticipantInfo->getPosition(new User())
        );
    }
}
