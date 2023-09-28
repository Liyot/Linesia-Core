<?php

namespace UnknowL\commands\dual;

final class DualParameters
{
    public const TYPE_1VS1 = 0;
    public const TYPE_2VS2 = 1

    public function __construct(private int $type, private int $gain, privat) {}
}