<?php

namespace SuperV\Tools\Generator\Domains;

use Doctrine\DBAL\Schema\Index;

class IndexParser
{
    /**
     * @var array
     */
    protected $indexes;

    /**
     * @var array
     */
    protected $multiFieldIndexes = [];

    /**
     * @var bool
     */
    protected $ignoreIndexNames = [];

    /**
     * @var string
     */
    protected $table;

    public function __construct(string $table, array $indexes = [], array $ignoreIndexNames = [])
    {
        $this->table = $table;
        $this->indexes = $indexes;
        $this->multiFieldIndexes = [];
        $this->ignoreIndexNames = $ignoreIndexNames;


        foreach ($indexes as $index) {
            $indexArray = $this->indexToArray($index);
            if (count($indexArray['columns']) == 1) {
                $columnName = $indexArray['columns'][0];
                $this->indexes[$columnName] = (object)$indexArray;
            } else {
                $this->multiFieldIndexes[] = (object)$indexArray;
            }
        }

    }

    public function getIndex($name): ?Index
    {
        if (isset($this->indexes[$name])) {
            return $this->indexes[$name];
        }

        return null;
    }

    public function getMultiFieldIndexes()
    {
        return $this->multiFieldIndexes;
    }

    protected function indexToArray(Index $index)
    {
        if ($index->isPrimary()) {
            $type = 'primary';
        } elseif ($index->isUnique()) {
            $type = 'unique';
        } else {
            $type = 'index';
        }
        $array = ['type' => $type, 'name' => null, 'columns' => $index->getColumns()];

        if (! $this->ignoreIndexNames and ! $this->isDefaultIndexName($index->getName(), $type, $index->getColumns())) {
            // Sent Index name to exclude spaces
            $array['name'] = str_replace(' ', '', $index->getName());
        }

        return $array;
    }

    /**
     * @param string       $table   Table Name
     * @param string       $type    Index Type
     * @param string|array $columns Column Names
     * @return string
     */
    protected function getDefaultIndexName($type, $columns)
    {
        if ($type == 'primary') {
            return 'PRIMARY';
        }
        if (is_array($columns)) {
            $columns = implode('_', $columns);
        }

        return $this->table.'_'.$columns.'_'.$type;
    }

    protected function isDefaultIndexName($name, $type, $columns)
    {
        return $name == $this->getDefaultIndexName( $type, $columns);
    }
}