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
     * @var Tree[]
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

    }

    public function populate($type, array $data)
    {
        $requiredFields = $this->config->getRequiredFields();

        foreach ($data as $key => $value) {
            if (!in_array($type.'.'.$key, $requiredFields)) {
                continue;
            }
        }
    }



    /**
     * @param Tree $element
     * @throws AlreadyExistsException
     */
    private function addElement(Tree $element)
    {
        if (isset($this->elements[$element->getName()])) {
            throw new AlreadyExistsException();
        }

        $this->elements[$element->getName()] = $element;
    }
}