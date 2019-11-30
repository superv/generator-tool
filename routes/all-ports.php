<?php

use SuperV\Tools\Generator\Http\Controllers\GeneratorController;

return [
    'ANY@sv/tools/generator/analyze' => GeneratorController::at('analyze'),
    'ANY@sv/tools/generator/write' => GeneratorController::at('write')
];