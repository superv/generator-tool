<?php

namespace SuperV\Tools\Generator\Domains;

use Illuminate\Database\Eloquent\Model;
use LogicException;
use ReflectionClass;
use SuperV\Platform\Domains\Addon\AddonCollection;
use SuperV\Platform\Domains\Resource\ColumnFieldMapper;
use SuperV\Platform\Domains\Resource\Generator\RelationGenerator;
use SuperV\Platform\Domains\Resource\Jobs\GetTableResource;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Analyzer
{
    /**
     * @var \SuperV\Platform\Domains\Addon\AddonCollection
     */
    protected $addons;

    protected $config;

    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

    protected $connectionName = 'koel';

//    protected $tables = ['95a_accounts', '95a_bills', '95a_bill_items'];

    protected $excluded = ['migrations', 'jobs', 'users', 'password_resets', 'failed_jobs'];

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $models;

    protected $modelsPath = 'app';

    protected $primaryColumns;

    public function __construct(AddonCollection $addons)
    {
        $this->addons = $addons;
    }

    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function analyze()
    {
        $this->prepareConnection();

        $this->makeModelsMap();

        return [
            'addons'              => $this->addons->enabled()->identifiers()->push('apps')->all(),
            'resources'           => $this->getResources()->values()->all(),
            'available_resources' => ResourceModel::all()->pluck('identifier')->all(),
        ];
    }

    private function getSchema()
    {
        return $this->connection->getSchemaManager();
    }

    protected function getResources()
    {
        return $this->getTables()->map(function ($table) {
            $tableWithoutPrefix = $this->getTableNameWithoutPrefix($table);

            $this->getIndexes($table);

            $fields = $this->getFields($table);
            $relations = $this->getRelations($tableWithoutPrefix);

            return [
                'addon'           => 'app',
                'identifier'      => 'app.'.$tableWithoutPrefix,
                'label'           => str_unslug($tableWithoutPrefix),
                'nav'             => 'acp.app',
                'fields'          => $fields,
                'relations'       => $relations,
                'table'           => $table,
                'primary_columns' => $this->primaryColumns,
                'connection'      => $this->connection,
            ];
        });
    }

    protected function getTableNameWithoutPrefix($table)
    {
        if (! $connection = $this->getConfigValue('connection')) {
            $connection = config('database.default');
        }

        $dbConfig = config('database.connections.'.$connection);

        if ($prefix = array_get($dbConfig, 'prefix')) {
            return str_replace_first($prefix.'_', '', $table);
        }

        return $table;
    }

    protected function getConfigValue($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    protected function getRelations($table)
    {
        if (! $model = $this->models->get($table)) {
            return [];
        }

        $generator = new RelationGenerator($model);

        return $generator->make()->map(function (RelationConfig $config) {
            return [
                'name'    => $config->getName(),
                'type'    => $config->getType(),
                'related' => 'app.'.$config->getRelatedResource(),
                'config'  => $config->toArray(),
            ];
        });
    }

    protected function getIndexes($table)
    {
        $indexes = $this->getSchema()->listTableIndexes($table);

        $parser = new IndexParser($table, $indexes);
        if ($primary = $parser->getIndex('primary')) {


            $this->primaryColumns = $primary->getColumns();
        }
//
//        if ($table === 'settings') {
//            dd( $this->primaryColumns);
//        }
//
//        foreach ($indexes as $index) {
//        }
    }

    protected function isPrimary($column)
    {
        return in_array($column, $this->primaryColumns);
    }

    protected function getFields($table)
    {
        $columns = $this->getSchema()->listTableColumns($table);
        $fields = [];
        /** @var \Doctrine\DBAL\Schema\Column $column */
        foreach ($columns as $column) {
            $config = [];
//            if ($column->getType()->getName() == 'integer' && $column->getUnsigned() && $column->getAutoincrement()) {
            if ($this->isPrimary($column->getName())) {
                $config['primary'] = true;
                $config['increments'] = $column->getAutoincrement();
            }
//                $type = ColumnFieldMapper::for($column->getType())->map()->getFieldType();
//                if ($type === 'text') {
//                    $type = 'string';
//                }
            $fields[] = [
                'name'  => $column->getName(),
                'label' => str_unslug($column->getName()),
                'type'  => ColumnFieldMapper::for($column->getType()->getName())->map()->getFieldType(),
                'column_type'  => $column->getType()->getName(),
                'config' => $config
            ];
        }

        return $fields;
    }

    protected function makeModelsMap()
    {
        if (! $modelsPath = $this->getConfigValue('models_path', $this->modelsPath)) {
            return;
        }

        $classList = [];
        /** @var SplFileInfo $file */
        foreach ((new Finder)->in(base_path($modelsPath))->files() as $file) {
            if (! $namespace = get_ns_from_file($file->getPathname())) {
                continue;
            }

            $className = str_replace('.php', '', $file->getFilename());
            $class = $namespace.'\\'.$className;

            $classList[] = $class;
        }

        $this->models = collect($classList)
            ->filter(function ($class) {
                if (ends_with($class, ['ServiceProvider', 'Repository'])) {
                    return false;
                }

                try {
                    if (! class_exists($class)) {
                        return false;
                    }
                } catch (LogicException $e) {
                    return false;
                }

                $reflection = new ReflectionClass($class);
                if (! $reflection->isUserDefined()) {
                    return false;
                }

                if ($reflection->isAbstract()) {
                    return false;
                }

                return $reflection->isSubclassOf(Model::class);
            })
            ->keyBy(function ($model) {
                return (new $model)->getTable();
            });
    }

    protected function getTables()
    {
        $tables = $this->tables ?? $this->getSchema()->listTableNames();

        return collect($tables)
            ->filter(function ($table) {
                return ! in_array($table, $this->excluded);
            })->filter(function ($table) {
                return ! starts_with($table, 'sv_');
            })->filter(function ($table) {
                return ! GetTableResource::dispatch($table, 'default');
            });
    }

    protected function prepareConnection()
    {
        $connectionName = $this->getConfigValue('connection');
        $connection = \DB::connection($connectionName)->getDoctrineConnection();;

        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('json', 'text');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('jsonb', 'text');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');

        // Postgres types
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_text', 'text');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_int4', 'integer');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_numeric', 'float');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('cidr', 'string');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('inet', 'string');

        $this->connection = $connection;
    }
}