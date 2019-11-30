<?php

namespace Tests\Generator;

class GeneratorTest extends TestCase
{
    function test_addon_is_installed()
    {
        $this->assertNotNull(sv_addons('admin'));
    }
}
