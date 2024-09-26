<?php

namespace Oro\Bundle\ConversationBundle\Action\Configuration;

use Oro\Bundle\ActionBundle\Configuration\ConfigurationProviderInterface;
use Oro\Bundle\ConversationBundle\Provider\StorefrontConversationProviderInterface;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;

/**
 * Sets the array of routes where Add conversation button should be added
 * to the oro_conversation_add_new_conversation action.
 */
class ConfigurationProviderDecorator implements ConfigurationProviderInterface
{
    private const ACTION_NAME = 'oro_conversation_add_new_conversation';

    private ConfigurationProviderInterface $innerProvider;
    private StorefrontConversationProviderInterface $storefrontConversationProvider;
    private ThemeConfigurationProvider $themeConfigurationProvider;
    private ThemeManager $themeManager;

    public function __construct(
        ConfigurationProviderInterface $innerProvider,
        StorefrontConversationProviderInterface $storefrontConversationProvider,
        ThemeConfigurationProvider $themeConfigurationProvider,
        ThemeManager $themeManager
    ) {
        $this->innerProvider = $innerProvider;
        $this->storefrontConversationProvider = $storefrontConversationProvider;
        $this->themeConfigurationProvider = $themeConfigurationProvider;
        $this->themeManager = $themeManager;
    }

    #[\Override]
    public function getConfiguration(): array
    {
        $result = $this->innerProvider->getConfiguration();

        $currentTheme = $this->themeConfigurationProvider->getThemeName();
        if ($currentTheme === null) {
            return $result;
        }

        if (array_key_exists(self::ACTION_NAME, $result)
            //conversation functionality does not support old themes
            && !$this->themeManager->themeHasParent($currentTheme, ['default_50', 'default_51'])
        ) {
            $result[self::ACTION_NAME]['routes'] = $this->storefrontConversationProvider->getAllowedRoutes();
        }

        return $result;
    }
}
