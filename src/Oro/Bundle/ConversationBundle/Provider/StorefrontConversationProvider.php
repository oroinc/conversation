<?php

namespace Oro\Bundle\ConversationBundle\Provider;

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

        throw new \InvalidArgumentException('Unknown source class "' . $sourceClassName . '"');
    }
}
