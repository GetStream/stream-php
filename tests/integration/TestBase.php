<?php

namespace GetStream\Integration;

use PHPUnit\Framework\TestCase;

class TestBase extends TestCase
{
    protected function generateGuid()
    {
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
    }
}
