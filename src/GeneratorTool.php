<?php namespace SuperV\Tools\Generator;

use SuperV\Platform\Domains\Addon\Types\Tool;
use SuperV\Platform\Domains\Resource\Nav\Nav;

class GeneratorTool extends Tool
{
    /**
     * Run after the tool is installed
     */
    public function onInstalled()
    {

    }

    /**
     * Run after the tool is booted
     */
    public function onBooted()
    {
        Nav::building('acp.platform', 'Generator', 'sv/tools/generator');
    }


}
