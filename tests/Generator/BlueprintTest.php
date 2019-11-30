<?php

namespace Tests\Generator;

use SuperV\Tools\Generator\Domains\Migration;
use const PHP_EOL;

class BlueprintTest extends TestCase
{
    function test__config_blueprint()
    {
        $blueprint = new Migration($this->makeBlueprint());

        $this->assertEquals([
            'identifier' => 'app.accounts',
            'nav'        => 'acp.app',
            'label'      => 'Accounts',
        ], $blueprint->getConfig());

        $config = $blueprint->renderConfig();

        $this->assertEquals('$config->identifier(\'app.accounts\');', $config[0]);
        $this->assertEquals('$config->label(\'Accounts\');', $config[1]);
        $this->assertEquals('$config->nav(\'acp.app\');', $config[2]);
    }

    function test__table_blueprint()
    {
        $blueprint = new Migration($this->makeBlueprint());

        $this->assertEquals([
            ['method' => 'id', 'args' => ['id'], 'decorators' => []],
            ['method' => 'number', 'args' => ['company_id'], 'decorators' => ['label' => ['Company']]],
            ['method' => 'text', 'args' => ['name'], 'decorators' => ['entryLabel']],
            ['method' => 'belongsTo', 'args' => ['app.currencies', 'currency', 'currency_code'], 'decorators' => []],
            [
                'method'     => 'belongsToMany',
                'args'       => ['app.payments', 'payments'],
                'decorators' => [
                    'pivotTable'      => ['account_payments'],
                    'pivotForeignKey' => ['account_id'],
                    'pivotRelatedKey' => ['id'],
                ],
            ],
        ], $blueprint->getTable());

        $table = $blueprint->renderTable();

        $this->assertEquals('$table->id(\'id\');', $table[0]);
        $this->assertEquals('$table->number(\'company_id\')'.PHP_EOL.str_pad('', 4 * 4 + 6, ' ').'->label(\'Company\');', $table[1]);
        $this->assertEquals('$table->text(\'name\')'.PHP_EOL.str_pad('', 4 * 4 + 6, ' ').'->entryLabel();', $table[2]);
    }

    protected function makeBlueprint()
    {
        return [
            'addon'      => 'admin',
            'identifier' => 'app.accounts',
            'label'      => 'Accounts',
            'nav'        => 'acp.app',
            'table'      => '95a_accounts',
            'fields'     => [
                [
                    'name'  => 'id',
                    'label' => 'Id',
                    'type'  => 'id',
                ],
                [
                    'name'  => 'company_id',
                    'label' => 'Company',
                    'type'  => 'number',
                ],
                [
                    'name'        => 'name',
                    'label'       => 'Name',
                    'type'        => 'text',
                    'entry_label' => true,
                ],
            ],
            'relations'  =>
                [
                    'currency' =>
                        [
                            'related_model' => 'App\\Akaunting\\Models\\Setting\\Currency',
                            'name'          => 'currency',
                            'type'          => 'belongs_to',
                            'related'       => 'app.currencies',
                            'config'        => [
                                'foreign_key' => 'currency_code',
                            ],
                        ],
                    'payments' =>
                        [
                            'related_model' => 'App\\Akaunting\\Models\\Expense\\Payment',
                            'name'          => 'payments',
                            'type'          => 'belongs_to_many',
                            'related'       => 'app.payments',
                            'config'        => [
                                'pivot_table'       => 'account_payments',
                                'pivot_foreign_key' => 'account_id',
                                'pivot_related_key' => 'id',
                            ],
                        ],
                ],
        ];
    }
}