<?php

namespace Oro\Bundle\ConversationBundle;

use Oro\Component\DependencyInjection\Compiler\PriorityTaggedLocatorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroConversationBundle extends Bundle
{
    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new PriorityTaggedLocatorCompilerPass(
            'oro_conversation.participant_info.provider',
            'oro_conversation.participant_info_provider',
            'class'
        ));
    }
}
