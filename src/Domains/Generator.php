<?php

namespace SuperV\Tools\Generator\Domains;

use SuperV\Platform\Platform;
use SuperV\Platform\Support\Parser;
use const PHP_EOL;

class Generator
{
    protected $targetPath;

    /**
     * @var \SuperV\Tools\Generator\Domains\MigrationV2
     */
    protected $blueprint;

    /**
     * @var \SuperV\Platform\Platform
     */
    protected $platform;

    /**
     * @var \SuperV\Platform\Support\Parser
     */
    protected $parser;

    protected $config;

    public function __construct(Platform $platform, Parser $parser)
    {
        $this->platform = $platform;
        $this->parser = $parser;
    }

    public function setTargetPath($targetPath)
    {
        $this->targetPath = $targetPath;

        return $this;
    }

    public function create($resource)
    {
        $this->blueprint = new MigrationV2($resource);

        $table = $this->blueprint->getTableName();
        $template = file_get_contents($this->platform->resourcePath('stubs/generator/migrationv2.stub'));

        $data = [
            'up_method'           => 'run',
            'class_name'          => studly_case("create_{$table}_resource"),
            'table_name'          => $table,
            'migration_namespace' => $this->blueprint->getData('addon', 'app'),
            'resource'            => [
                'identifier' => $this->blueprint->getData('identifier'),
            ],
            'config'              => implode(PHP_EOL.str_pad('', 4 * 4, ' '), $this->blueprint->renderConfig()),
            'blueprint'           => implode(PHP_EOL.str_pad('', 4 * 4, ' '), $this->blueprint->render()),
        ];
        $templateData = $this->parser->parse($template, $data);

        file_put_contents($this->targetPath.'/'.$this->makeMigrationName(), $templateData);

        return $templateData;
    }

    protected function makeMigrationName()
    {
        $datePrefix = date('Y_m_d_His');

        $datePrefix = '2020_01_01_000000';

        return $datePrefix.'_create_'.$this->blueprint->getTableName().'_resource.php';
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}