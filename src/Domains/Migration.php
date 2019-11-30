<?php

namespace SuperV\Tools\Generator\Domains;

class Migration
{
    /**
     * @var array
     */
    protected $data;

    protected $relationFields = [];

//    protected $ignoreFields = ['deleted_at', 'updated_at', 'created_at'];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getConfig()
    {
        $config = array_only($this->data, ['identifier', 'nav', 'label', 'connection']);

        if (! empty($this->getPrimaryColumns())) {
            $primaryColumn = $this->getPrimaryColumns()[0];
            $config['keyName'] = $primaryColumn !== 'id' ? $primaryColumn : null;
        }

        return array_filter($config);
    }

    public function renderConfig()
    {
        $config = $this->getConfig();

        $configArray = [];
        foreach ($config as $key => $value) {
            $item = [
                'method' => $key,
                'args'   => [$value],
            ];
            $configArray[] = $this->getItem($item, 'config');
        }

        return $configArray;
    }

    public function getTable()
    {
        $relations = [];
        foreach ($this->getRelations() as $relation) {
            $relations[] = $this->getRelationArgs($relation);
        }

        $fields = [];
        $indexes = [];
        foreach ($this->getFields() as $field) {
            if (! $field['type']) {
                continue;
            }

            $fieldName = $field['name'];
            $fieldType = $field['type'];
            if (in_array($fieldName, $this->relationFields)) {
                continue;
            }

            $method = $field['column_type'];
//            $method = camel_case($field['type'].'_field');
//            $method = 'field';
            $config = array_get($field, 'config', []);
            $decorators = array_get($field, 'decorators', []);
//            $args = [$fieldName, $fieldType];
            $args = [$fieldName];

            if ($field['label'] !== str_unslug($fieldName)) {
                $decorators['label'] = [$field['label']];
            }

            if (array_get($field, 'entry_label') === true) {
                $decorators[] = 'entryLabel';
            }



            if (in_array($fieldName, ['deleted_at', 'updated_at', 'created_at'])) {
                $decorators = ['nullable', 'hideOnForms'];
            }



            if (array_get($config, 'primary') === true) {
                if (array_get($config, 'increments') === true) {
                    $method = 'id';
                    if ($fieldName === 'id')
                        $args = [];
                } else {
                    $indexes[] = [
                        'method' => 'primary',
                        'args'   => [$fieldName],
                    ];
                }
            }

            $fields[] = [
                'method'     => $method,
                'args'       => $args,
                'decorators' => $decorators,
            ];
        }

        return array_merge($fields, $relations, $indexes);
    }

    public function renderTable()
    {
        $table = $this->getTable();

        $tableArray = [];
        foreach ($table as $item) {
            $tableArray[] = $this->getItem($item);
        }

        return $tableArray;
    }

    public function getData($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function getTableName()
    {
        return $this->data['table'] ?? '';
    }

    protected function isPrimary($column)
    {
        return in_array($column, $this->getPrimaryColumns());
    }

    protected function getRelationArgs($relation)
    {
        $config = $relation['config'] ?? [];
        $decorators = [];
        $args = [];

        if ($relation['type'] === 'belongs_to') {
            $args = [
                $config['foreign_key'],
                $config['owner_key'] ?? null,
            ];

            $this->relationFields[] = $config['foreign_key'];
        }

        if ($relation['type'] === 'morph_one') {
            $args = [
                $config['morph_name'],
            ];
        }

        if ($relation['type'] === 'morph_many') {
            $args = [
                $config['morph_name'],
            ];
        }

        if ($relation['type'] === 'morph_to_many') {
            $args = [
                $config['morph_name'],
            ];

            $decorators = [
                'pivotTable'      => [$config['pivot_table']],
                'pivotRelatedKey' => [$config['pivot_related_key'] ?? null],
            ];
        }

        if ($relation['type'] === 'has_many') {
            $args = [
                $config['foreign_key'] ?? null,
                $config['local_key'] ?? null,
            ];
        }

        if ($relation['type'] === 'belongs_to_many') {
            $decorators = [
                'pivotTable'      => [$config['pivot_table']],
                'pivotForeignKey' => [$config['pivot_foreign_key']],
                'pivotRelatedKey' => [$config['pivot_related_key']],
            ];
        }

        return [
            'method'     => camel_case($relation['type']),
            'args'       => array_filter(array_merge([
                $relation['related'],
                $relation['name'],
            ], $args)),
            'decorators' => array_filter($decorators),
        ];
    }

    protected function parseArgs($args)
    {
        if (! $args || empty($args)) {
            return null;
        }

        return '\''.implode('\', \'', $args).'\'';
    }

    protected function getItem(array $item, $objectName = 'table')
    {
        $method = $item['method'];

        $output = sprintf("\$%s->%s(%s)", $objectName, $method, $this->parseArgs($item['args']));

        if (isset($item['decorators'])) {
            $output .= $this->addDecorators($item['decorators']);
        }

        return $output.';';
    }

    protected function addDecorators($decorators)
    {
        $output = '';
        foreach ($decorators as $key => $value) {
            if (is_numeric($key)) {
                $method = $value;
            } else {
                $method = $key;
                $decorator = '\''.implode('\', \'', $value).'\'';
            }
            $output .= PHP_EOL.str_pad('', 4 * 4 + 6, ' ').sprintf("->%s(%s)", $method, $decorator ?? '');
        }

        return $output;
    }

    protected function getFields()
    {
        return $this->data['fields'] ?? [];
    }

    protected function getRelations()
    {
        return $this->data['relations'] ?? [];
    }

    protected function getPrimaryColumns()
    {
        return $this->data['primary_columns'] ?? [];
    }
}