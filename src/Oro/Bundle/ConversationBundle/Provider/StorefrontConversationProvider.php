<?php

namespace Oro\Bundle\ConversationBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\RFPBundle\Entity\Request;
use Oro\Bundle\SaleBundle\Entity\Quote;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Default implementation of the StorefrontConversationProviderInterface that add support of
 * order, RFQ and quotes pages.
 */
class StorefrontConversationProvider implements StorefrontConversationProviderInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function getAllowedRoutes(): array
    {
        return ['oro_order_frontend_view', 'oro_rfp_frontend_request_view', 'oro_sale_quote_frontend_view'];
    }

    public function getSourceUrl(string $sourceClassName, int $sourceId): string
    {
        if (Order::class === $sourceClassName) {
            return $this->urlGenerator->generate('oro_order_frontend_view', ['id' => $sourceId]);
        }

        if (Request::class === $sourceClassName) {
            return $this->urlGenerator->generate('oro_rfp_frontend_request_view', ['id' => $sourceId]);
        }

        if (Quote::class === $sourceClassName) {
            return $this->urlGenerator->generate('oro_sale_quote_frontend_view', ['id' => $sourceId]);
        }

        if (CustomerUser::class === $sourceClassName) {
            return $this->urlGenerator->generate('oro_customer_frontend_customer_user_view', ['id' => $sourceId]);
        }

        return '';
    }

    public function getSourceChoices(): array
    {
        return [
            'oro.conversation.source_entity_class.null_value' => '_empty_',
            'oro.order.entity_label' => Order::class,
            'oro.rfp.request.entity_label' => Request::class,
            'oro.sale.quote.entity_label' => Quote::class
        ];
    }
}
