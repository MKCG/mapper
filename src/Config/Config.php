<?php

namespace Mapping\Config;

use Mapping\AlreadyExistsException;

final class Config
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var Config[]
     */
    private $properties = [];

    public function __construct($name, array $config)
    {
        $this->name = $name;

        foreach ($config as $key => $value) {
            if (empty($value)) {
                throw new EmptyException('Config key '.(string) $key.' has no value');
            }

            $this->setParam($key, $value);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $param
     * @param $value
     * @throws AlreadyExistsException
     * @throws InvalidException
     */
    private function setParam($param, $value)
    {
        switch ($param) {
            case 'properties':
                $this->setProperties($value);
                break;
            case 'required_fields':
                $this->setRequiredFields($value);
                break;
            case 'callback':
                $this->setCallback($value);
                break;
            default:
                throw new InvalidException('Unkwnown config key ' . (string)$param);
                break;
        }
    }

    /**
     * @param array $properties
     * @throws AlreadyExistsException
     */
    private function setProperties(array $properties)
    {
        foreach ($properties as $key => $value) {
            if (isset($this->properties[$key])) {
                throw new AlreadyExistsException();
            }

            $this->properties[$key] = new Config($key, $value);
        }
    }

    /**
     * @param array $fields
     */
    private function setRequiredFields(array $fields)
    {
        $this->fields = $fields;
    }

    private function setCallback($callback)
    {

    }

    public function getRequiredFields()
    {
        $fields = $this->fields;

        foreach ($this->properties as $property) {
            $fields = array_merge($fields, $property->getRequiredFields());
        }

        return $fields;
    }

    public function toArray()
    {
        $array = ['name' => $this->getName()];

        if (!empty($this->fields)) {
            $array['fields'] = $this->fields;
        }

        if (!empty($this->properties)) {
            $array['properties'] = [];
            foreach ($this->properties as $property) {
                $array['properties'][] = $property->toArray();
            }
        }

        return $array;
    }
}