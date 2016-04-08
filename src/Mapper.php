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
            return;
        }

        $ownerColumn = $this->getOwnerColumn($name, $type);

        foreach ($data as $values) {
            if (!isset($values[$ownerColumn])) {
                continue;
            }

            $ownerId = $values[$ownerColumn];
            if (!isset($this->trees[$name])) {
                $this->trees[$name] = [];
            }

            if (!isset($this->trees[$name][$ownerId])) {
                $this->trees[$name][$ownerId] = new Tree($this->configs[$name]);
            }

            $this->trees[$name][$ownerId]->populate($type, $values);
        }
    }

    public function toArray()
    {
        $array = [];

        foreach ($this->trees as $type => $elements) {
            if (empty($elements)) {
                continue;
            }

            if (!isset($array[$type])) {
                $array[$type] = [];
            }

            foreach ($elements as $key => $value) {
                $array[$type][$key] = $value->toArray();
            }
        }

        return $array;
    }

    private function getOwnerColumn($name, $type)
    {
        $column = null;

        switch ($type) {
            case 'article':
                $column = $this->getArticleOwnerColumn($name);
                break;
            case 'academic':
                $column = $this->getAcademicOwnerColumn($name);
                break;
            default:
                break;
        }

        return $column;
    }

    private function getAcademicOwnerColumn($name)
    {
        switch ($name) {
            case 'User':
                $column = 'user_id';
                break;
            default:
                $column = null;
        }

        return $column;
    }

    private function getArticleOwnerColumn($name)
    {
        switch ($name) {
            case 'User':
                $column = 'user_id';
                break;
            default:
                $column = null;
        }

        return $column;
    }
}
