<?php

namespace Oro\Bundle\ConversationBundle\Helper;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Returns the next config data from the entity configuration:
 * - label
 * - icon
 */
class EntityConfigHelper
{
    private ConfigManager $configManager;
    private TranslatorInterface $translator;

    public function __construct(ConfigManager $configManager, TranslatorInterface $translator)
    {
        $this->configManager = $configManager;
        $this->translator = $translator;
    }

    public function getLabel(object $entity): ?string
    {
        $label = $this->configManager->getEntityConfig('entity', ClassUtils::getClass($entity))->get('label');

        return $label ? $this->translator->trans($label) : '';
    }

    public function getIcon(object $entity): ?string
    {
        return $this->configManager->getEntityConfig('entity', ClassUtils::getClass($entity))->get('icon');
    }
}
