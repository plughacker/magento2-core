<?php

namespace PlugHacker\PlugCore\Recurrence\Services\CartRules;

use PlugHacker\PlugCore\Kernel\Services\LocalizationService;
use PlugHacker\PlugCore\Recurrence\Services\CartRules\CurrentProduct;

class JustOneProductPlanInCart implements RuleInterface
{
    /**
     * @var string
     */
    private $error;

    /**
     * @var LocalizationService
     */
    private $i18n;

    /**
     * JustOneProductPlanInCart constructor.
     */
    public function __construct()
    {
        $this->i18n = new LocalizationService();
    }

    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        if (
            $currentProduct->getQuantity() > 1 &&
            $currentProduct->getProductPlanSelected() !== null
        ) {
            $this->error = $this->i18n->getDashboard(
                'You must have only one product plan in the cart'
            );
        }
    }

    public function getError()
    {
        return $this->error;
    }
}
