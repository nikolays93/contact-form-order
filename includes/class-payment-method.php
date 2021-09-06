<?php

namespace NikolayS93\PluginName;

interface Payment_Method
{
    public function request(Order $order): array;

    public function validateReturn(array $requestBody): bool;
}
