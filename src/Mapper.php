<?php

namespace Mapping;

use Mapping\Config\Config;

class Mapper {
    /**
     * @var Config[]
     */
    private $configs = [];

    /**
     * @var Tree[]
     */
    private $trees = [];

	public function __construct()
	{
	}

    public function addConfig($name, array $outputConfig)
    {
        if (isset($this->configs[$name])) {
            throw new AlreadyExistsException();
        }

        $this->configs[$name] = new Config($name, $outputConfig);
    }

    public function getRequiredFields()
    {
        $fields = [];

        foreach ($this->configs as $key => $value) {
            $fields[$key] = $value->getRequiredFields();
        }

        return $fields;
    }

    public function populate($name, $type, array $data)
    {

        if (!isset($this->configs[$name])) {
        }

        $ownerColumn = $this->getOwnerColumn($type);

        foreach ($data as $values) {
            if (!isset($values[$ownerColumn])) {
                continue;
            }

            $ownerId = $values[$ownerColumn];
            if (!isset($this->trees[$ownerId])) {
                $this->trees[$ownerId] = new Tree($this->configs[$name]);
            }

            $this->trees[$ownerId]->populate($type, $values);
        }
    }

    private function getOwnerColumn($type)
    {
        $column = null;

        switch ($type) {
            case 'article':
            case 'academic':
                $column = 'user_id';
                break;
            default:
                break;
        }

        return $column;
    }
}
