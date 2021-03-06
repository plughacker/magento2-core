<?php

namespace PlugHacker\PlugCore\Recurrence\Services;

use PlugHacker\PlugCore\Kernel\Services\LocalizationService;
use PlugHacker\PlugCore\Kernel\Services\MoneyService;
use PlugHacker\PlugCore\Recurrence\Repositories\RepetitionRepository;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Recurrence\Aggregates\Repetition;

class RepetitionService
{
    /**
     * @var RepetitionRepository
     */
    private $repetitionRepository;
    protected $i18n;
    protected $moneyService;

    /**
     * RepetitionService constructor.
     */
    public function __construct()
    {
        $this->repetitionRepository = new RepetitionRepository();
        $this->i18n = new LocalizationService();
        $this->moneyService = new MoneyService();
    }

    /**
     * @param $subscriptionRepetitionsId
     * @return AbstractEntity|Repetition|null
     */
    public function getRepetitionById($subscriptionRepetitionsId)
    {
        return $this->repetitionRepository->find($subscriptionRepetitionsId);
    }

    /**
     * @param Repetition $repetition
     * @return string
     * @throws \PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException
     */
    public function getCycleTitle(Repetition $repetition)
    {
        $intervalLabel = $this->tryFindDictionaryEventCustomOptionsProductSubscription(
            $repetition
        );

        if ($repetition->getRecurrencePrice() <= 0) {
            return $intervalLabel;
        }

        $totalAmount = $this->moneyService->centsToFloat(
            $repetition->getRecurrencePrice()
        );

        $numberFormatter = new \NumberFormatter(
            'pt-BR',
            \NumberFormatter::CURRENCY
        );

        $totalAmount = $numberFormatter->format($totalAmount);

        return $intervalLabel . " - ({$totalAmount})";
    }

    /**
     * @param Repetition $repetition
     * @return string
     */
    public function tryFindDictionaryEventCustomOptionsProductSubscription(
        Repetition $repetition
    ) {
        $dictionary = [
            'month' => [
                1 => 'monthly',
                2 => 'bimonthly',
                3 => 'quarterly',
                6 => 'semiannual'
            ],
            'year' => [
                1 => 'yearly',
                2 => 'biennial'
            ],
            'week' => [
                1 => 'weekly'
            ]
        ];

        $intervalType = $repetition->getInterval();
        $intervalCount = $repetition->getIntervalCount();

        if (isset($dictionary[$intervalType][$intervalCount])) {
            return $this->i18n->getDashboard($dictionary[$intervalType][$intervalCount]);
        }

        $intervalType = $this->i18n->getDashboard($repetition->getIntervalTypeLabel());
        return "De {$intervalCount} em {$intervalCount} {$intervalType}";
    }
}
