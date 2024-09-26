<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Action\Configuration;

use Oro\Bundle\ActionBundle\Configuration\ConfigurationProviderInterface;
use Oro\Bundle\ConversationBundle\Action\Configuration\ConfigurationProviderDecorator;
use Oro\Bundle\ConversationBundle\Provider\StorefrontConversationProviderInterface;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigurationProviderDecoratorTest extends TestCase
{
    private ConfigurationProviderInterface|MockObject $innerProvider;
    private StorefrontConversationProviderInterface|MockObject $storefrontConversationProvider;
    private ThemeConfigurationProvider|MockObject $themeConfigurationProvider;
    private ThemeManager|MockObject $themeManager;

    private ConfigurationProviderDecorator $decorator;

    protected function setUp(): void
    {
        $this->innerProvider = $this->createMock(ConfigurationProviderInterface::class);
        $this->storefrontConversationProvider = $this->createMock(StorefrontConversationProviderInterface::class);
        $this->themeConfigurationProvider = $this->createMock(ThemeConfigurationProvider::class);
        $this->themeManager = $this->createMock(ThemeManager::class);

        $this->decorator = new ConfigurationProviderDecorator(
            $this->innerProvider,
            $this->storefrontConversationProvider,
            $this->themeConfigurationProvider,
            $this->themeManager
        );
    }

    public function testGetConfigurationWithoutAction(): void
    {
        $this->themeConfigurationProvider->expects(self::once())
            ->method('getThemeName')
            ->willReturn('default');

        $this->innerProvider->expects(self::once())
            ->method('getConfiguration')
            ->willReturn([]);

        $this->storefrontConversationProvider->expects(self::never())
            ->method('getAllowedRoutes');

        self::assertEquals([], $this->decorator->getConfiguration());
    }

    public function testGetConfiguration(): void
    {
        $this->themeConfigurationProvider->expects(self::once())
            ->method('getThemeName')
            ->willReturn('default');

        $this->themeManager->expects(self::once())
            ->method('themeHasParent')
            ->with('default', ['default_50', 'default_51'])
            ->willReturn(false);

        $this->innerProvider->expects(self::once())
            ->method('getConfiguration')
            ->willReturn(['oro_conversation_add_new_conversation' => []]);

        $this->storefrontConversationProvider->expects(self::once())
            ->method('getAllowedRoutes')
            ->willReturn(['route1', 'route2']);

        self::assertEquals(
            ['oro_conversation_add_new_conversation' => ['routes' => ['route1', 'route2']]],
            $this->decorator->getConfiguration()
        );
    }
}
