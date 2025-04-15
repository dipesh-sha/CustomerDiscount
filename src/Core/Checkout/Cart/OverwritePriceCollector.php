<?php

declare(strict_types=1);

namespace CustomerDiscount\Core\Checkout\Cart;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class OverwritePriceCollector implements CartDataCollectorInterface, CartProcessorInterface
{
  private QuantityPriceCalculator $calculator;

  public function __construct(QuantityPriceCalculator $calculator)
  {
    $this->calculator = $calculator;
  }

  public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
  {
    $customer = $context->getCustomer();
    if (!$customer) {
      return;
    }

    $customFields = $customer->getCustomFields();
    $discount = isset($customFields['custom_discount']) ? (float) $customFields['custom_discount'] : 0;

    if ($discount <= 0) {
      return;
    }

    $productIds = $original->getLineItems()
      ->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE)
      ->getReferenceIds();

    foreach ($productIds as $id) {
      $key = $this->buildKey($id);
      $data->set($key, $discount); // Now storing the discount instead of a fixed price
    }
  }

  public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
  {
    $products = $toCalculate->getLineItems()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE);

    foreach ($products as $product) {
      $key = $this->buildKey($product->getReferencedId());

      if (!$data->has($key)) {
        continue;
      }

      $discount = $data->get($key);

      if (!is_numeric($discount) || $discount <= 0) {
        continue;
      }

      $originalUnitPrice = $product->getPrice()->getUnitPrice();
      $discountedUnitPrice = $originalUnitPrice * (1 - $discount / 100);

      $definition = new QuantityPriceDefinition(
        (float) $discountedUnitPrice,
        $product->getPrice()->getTaxRules(),
        $product->getQuantity()
      );

      $calculated = $this->calculator->calculate($definition, $context);

      $product->setPrice($calculated);
      $product->setPriceDefinition($definition);
    }
  }

  private function buildKey(string $id): string
  {
    return 'customer-discount-' . $id;
  }
}
