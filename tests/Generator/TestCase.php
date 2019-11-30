<?php

namespace Tests\Generator;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Testing\PlatformTestCase;

abstract class TestCase extends PlatformTestCase
{
    use RefreshDatabase;

    protected $shouldBootPlatform = true;

    protected $installs = ['addons/superv/panels/admin'];

}
