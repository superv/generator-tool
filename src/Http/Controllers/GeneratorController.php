<?php

namespace SuperV\Tools\Generator\Http\Controllers;

use Platform;
use SuperV\Platform\Http\Controllers\BaseApiController;
use SuperV\Platform\Support\Parser;
use SuperV\Tools\Generator\Domains\Analyzer;
use SuperV\Tools\Generator\Domains\Generator;
use const PHP_EOL;

class GeneratorController extends BaseApiController
{
    public function analyze(Analyzer $analyzer)
    {
        return [
            'data' => $analyzer->setConfig($this->request->all())->analyze(),
        ];
    }

    public function write()
    {
        $resources = $this->request->get('resources');

        $seeder = [];
        foreach ($resources as $resource) {
            if ($resource['enabled'] !== true) continue;
            $creator = Generator::resolve();
            $creator->setTargetPath(base_path('database/migrations'));
            $creator->create($resource);

            $seeder[] = 'sv_resource(\''.$resource['identifier'].'\')->fake([], rand(3,10));';
        }

//        $this->createSeederMigration($parser, $seeder);

        return response(['status' => 'ok']);
    }

    /**
     * @param \SuperV\Platform\Support\Parser $parser
     * @param array                           $seeder
     */
    protected function createSeederMigration(Parser $parser, array $seeder): void
    {
        $template = file_get_contents(Platform::resourcePath('stubs/generator/migration_ex.stub'));
        $data = [
            'up_method'           => 'create',
            'class_name'          => studly_case("seed_resources"),
            'migration_namespace' => 'app',
            'up'                  => implode(PHP_EOL.str_pad('', 4 * 4, ' '), $seeder),
            'down'                => '',
        ];

        $templateData = $parser->parse($template, $data);
        $datePrefix = '2020_00_00_000000';
        file_put_contents(base_path('database/migrations').'/'.$datePrefix.'_seed_resources.php', $templateData);
    }
}