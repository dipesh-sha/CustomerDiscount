<?php
namespace CustomerDiscount\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class DiscountService
{
    public function getCustomerDiscount(SalesChannelContext $context): float
    {
        $customer = $context->getCustomer();
        if (!$customer) {
            return 0;
        }

        return (float) ($customer->getCustomFields()['custom_discount'] ?? 0);
    }
}
