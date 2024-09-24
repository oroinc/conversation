<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Helper;

use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class EntityConfigHelperTest extends TestCase
{
    private ConfigManager|MockObject $configManager;

    private EntityConfigHelper $entityConfigHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($string) {
                return $string . '_translated';
            });

        $this->entityConfigHelper = new EntityConfigHelper($this->configManager, $translator);
    }

    public function testGetLabelForObject(): void
    {
        $entity = new \stdClass();
        $config = new Config(new EntityConfigId('entity', \stdClass::class), ['label' => 'oro_test_entity_label']);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('entity', $entity::class)
            ->willReturn($config);

        self::assertEquals('oro_test_entity_label_translated', $this->entityConfigHelper->getLabel($entity));
    }

    public function testGetLabelForObjectWhenLabelIsAbsent(): void
    {
        $entity = new \stdClass();
        $config = new Config(new EntityConfigId('entity', \stdClass::class), []);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('entity', $entity::class)
            ->willReturn($config);

        self::assertEquals('', $this->entityConfigHelper->getLabel($entity));
    }

    public function testGetIconForObject(): void
    {
        $entity = new \stdClass();
        $config = new Config(new EntityConfigId('entity', \stdClass::class), ['icon' => 'fa-test']);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('entity', $entity::class)
            ->willReturn($config);

        self::assertEquals('fa-test', $this->entityConfigHelper->getIcon($entity));
    }

    public function testGetIconForObjectWhenLabelIsAbsent(): void
    {
        $entity = new \stdClass();
        $config = new Config(new EntityConfigId('entity', \stdClass::class), []);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('entity', $entity::class)
            ->willReturn($config);

        self::assertEquals('', $this->entityConfigHelper->getIcon($entity));
    }
}
