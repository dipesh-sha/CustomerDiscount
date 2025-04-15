<?php

namespace CustomerDiscount\Struct;

use Shopware\Core\Framework\Struct\Struct;

class CustomerDiscountStruct extends Struct
{
    protected float $discount;

    public function __construct(float $discount)
    {
        $this->discount = $discount;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }
}
