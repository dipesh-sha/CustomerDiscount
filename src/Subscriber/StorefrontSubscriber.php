<?php

namespace CustomerDiscount\Subscriber;

use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use CustomerDiscount\Service\DiscountService;
use CustomerDiscount\Struct\CustomerDiscountStruct;
use Shopware\Core\Checkout\Cart\Event\CartLoadedEvent;

class StorefrontSubscriber implements EventSubscriberInterface
{
  private DiscountService $discountService;

  public function __construct(DiscountService $discountService)
  {
    $this->discountService = $discountService;
  }

  public static function getSubscribedEvents(): array
  {
    return [
      ProductPageLoadedEvent::class => 'onProductPageLoaded',
      CartLoadedEvent::class => 'onCartLoaded'
    ];
  }

  public function onProductPageLoaded(ProductPageLoadedEvent $event)
  {
    $page = $event->getPage();
    $context = $event->getSalesChannelContext();
    $discount = $this->discountService->getCustomerDiscount($context);

    $page->addExtension('customerDiscount', new CustomerDiscountStruct($discount));
  }
  public function onCartLoaded(CartLoadedEvent $event)
  {
    $cart = $event->getCart();
    $context = $event->getSalesChannelContext();
    $discount = $this->discountService->getCustomerDiscount($context);
    $cart->addExtension('customerDiscount', new CustomerDiscountStruct($discount));
  }
}
