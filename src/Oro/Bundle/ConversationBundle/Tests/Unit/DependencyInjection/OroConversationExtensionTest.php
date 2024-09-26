<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\ConversationBundle\DependencyInjection\OroConversationExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroConversationExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $extension = new OroConversationExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
        self::assertSame(
            [
                [
                    'settings' => [
                        'resolved' => true,
                        'enable_conversation' => [
                            'value' => true,
                            'scope' => 'app'
                        ]
                    ]
                ]
            ],
            $container->getExtensionConfig('oro_conversation')
        );
    }
}
