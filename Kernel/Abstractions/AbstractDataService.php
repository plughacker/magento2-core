<?php

namespace PlugHacker\PlugCore\Kernel\Abstractions;

use PlugHacker\PlugCore\Kernel\Aggregates\Order;

abstract class AbstractDataService
{

    const TRANSACTION_TYPE_AUTHORIZATION = 'authorization';
    const TRANSACTION_TYPE_CAPTURE = 'capture';

    abstract public function updateAcquirerData(Order $order);
    abstract public function createCaptureTransaction(Order $order);
    abstract public function createAuthorizationTransaction(Order $order);
}
