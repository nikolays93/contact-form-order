<?php

interface Payment_Method
{
    public function request(Order $order): array;

    public function validateReturn(Order $order, array $requestBody): bool;
}
