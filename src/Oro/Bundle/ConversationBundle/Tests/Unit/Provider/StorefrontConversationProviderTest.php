<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Provider;

use Oro\Bundle\ConversationBundle\Provider\StorefrontConversationProvider;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\RFPBundle\Entity\Request;
use Oro\Bundle\SaleBundle\Entity\Quote;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StorefrontConversationProviderTest extends TestCase
{
    private UrlGeneratorInterface $urlGenerator;

    private StorefrontConversationProvider $provider;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->provider = new StorefrontConversationProvider($this->urlGenerator);
    }

    public function testGetAllowedRoutes(): void
    {
        self::assertEquals(
            ['oro_order_frontend_view', 'oro_rfp_frontend_request_view', 'oro_sale_quote_frontend_view'],
            $this->provider->getAllowedRoutes()
        );
    }

    public function testGetSourceUrlOnNonSupportClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->urlGenerator->expects(self::never())
            ->method('generate');

        $this->provider->getSourceUrl(\stdClass::class, 23);
    }

    public function testGetSourceUrlOnOrderClass(): void
    {
        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with('oro_order_frontend_view', ['id' => 22])
            ->willReturn('/some/route/order/22');

        self::assertEquals('/some/route/order/22', $this->provider->getSourceUrl(Order::class, 22));
    }

    public function testGetSourceUrlOnRequestClass(): void
    {
        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with('oro_rfp_frontend_request_view', ['id' => 21])
            ->willReturn('/some/route/order/21');

        self::assertEquals('/some/route/order/21', $this->provider->getSourceUrl(Request::class, 21));
    }

    public function testGetSourceChoices(): void
    {
        self::assertEquals(
            [
                'oro.conversation.source_entity_class.null_value' => '_empty_',
                'oro.order.entity_label' => Order::class,
                'oro.rfp.request.entity_label' => Request::class,
                'oro.sale.quote.entity_label' => Quote::class
            ],
            $this->provider->getSourceChoices()
        );
    }
}
