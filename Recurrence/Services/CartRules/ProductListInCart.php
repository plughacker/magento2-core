<?php

namespace PlugHacker\PlugCore\Recurrence\Services\CartRules;

use PlugHacker\PlugCore\Recurrence\Interfaces\ProductSubscriptionInterface;
use PlugHacker\PlugCore\Recurrence\Interfaces\ProductPlanInterface;
use PlugHacker\PlugCore\Recurrence\Interfaces\RepetitionInterface;

class ProductListInCart
{
    /** @var ProductSubscriptionInterface */
    private $recurrenceProduct;

    /** @var RepetitionInterface */
    private $repetition;

    /** @var array */
    private $normalProducts = [];

    /** @var array ProductSubscriptionInterface */
    private $recurrenceProducts = [];

    /**
     * @var ProductPlanInterface[]
     */
    private $productPlan = [];

    /**
     * @return ProductSubscriptionInterface
     */
    public function getRecurrenceProduct()
    {
        return $this->recurrenceProduct;
    }

    /**
     * @param ProductSubscriptionInterface $recurrenceProduct
     */
    public function setRecurrenceProduct(ProductSubscriptionInterface $recurrenceProduct)
    {
        $this->recurrenceProduct = $recurrenceProduct;
    }

    /**
     * @return ProductSubscriptionInterface[]
     */
    public function getRecurrenceProducts()
    {
        return $this->recurrenceProducts;
    }

    /**
     * @param ProductSubscriptionInterface $recurrenceProduct
     */
    public function addRecurrenceProduct(ProductSubscriptionInterface $recurrenceProduct)
    {
        $this->recurrenceProducts[] = $recurrenceProduct;
    }

    /**
     * @param ProductPlanInterface $productPlan
     */
    public function addProductPlan(ProductPlanInterface $productPlan)
    {
        $this->productPlan[] = $productPlan;
    }

    /**
     * @return ProductPlanInterface[]
     */
    public function getProductsPlan()
    {
        return $this->productPlan;
    }

    /**
     * @return array
     */
    public function getNormalProducts()
    {
        return $this->normalProducts;
    }

    /**
     * @param array $normalProduct
     */
    public function addNormalProducts($normalProduct)
    {
        $this->normalProducts[] = $normalProduct;
    }

    /**
     * @return RepetitionInterface
     */
    public function getRepetition()
    {
        return $this->repetition;
    }

    /**
     * @param RepetitionInterface $repetition
     */
    public function setRepetition(RepetitionInterface $repetition)
    {
        $this->repetition = $repetition;
    }
}
