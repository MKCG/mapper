<?php

namespace Mapping;

use Mapping\Config\Config;

class Tree
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var array[]
     */
    private $elements;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->config->getName();
    }

    public function toArray()
    {
        return $this->elements;
    }

    public function populate($type, array $data)
    {
        $requiredFields = $this->config->getRequiredFields();

        foreach ($data as $key => $value) {
            if (!in_array($type.'.'.$key, $requiredFields)) {
                continue;
            }

            $hasElement = $this->hasElement($type, $data);

            if (!$hasElement) {
                $this->createElement($type, $data);
            }
        }
    }

    private function hasElement($type, array $data)
    {
        if (empty($data['id'])) {
            throw new \InvalidArgumentException();
        }

        return isset($this->elements[$type][$data['id']]);
    }

    private function createElement($type, $data)
    {
        if (!isset($this->elements[$type])) {
            $this->elements[$type] = [];
        }

        $this->elements[$type][$data['id']] = $data;
    }
}