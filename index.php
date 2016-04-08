<?php

include_once('src/Mapper.php');
include_once('src/Tree.php');
include_once('src/AlreadyExistsException.php');
include_once('src/Config/Config.php');
include_once('src/Config/InvalidException.php');
include_once('src/Config/MissingEntryException.php');
include_once('src/Config/EmptyException.php');

use Mapping\Mapper;



function populateNeededFields(array $neededFields, $tree)
{
    if (!is_array($tree)) {
        return $tree;
    }

    foreach ($tree as $key => $value) {
        if (!isset($neededFields[$key])) {
            $neededFields[$key] = [];
        }
        $neededFields[$key][] = populateNeededFields($neededFields[$key], $value);
    }

    return $neededFields;
}

function recursiveArray(array $elements) {
    if (empty($elements)) {
        return '';
    }

    if (count($elements) === 1) {
        return array_shift($elements);
    }

    $element = array_shift($elements);
    return [$element => recursiveArray($elements)];
}


$output = [
    'properties' => [
        'id' => [
            'required_fields' => [
                'user.user_id',
            ],
        ],
        'firstname' => [
            'required_fields' => [
                'user.firstname',
            ],
        ],
        'lastname' => [
            'required_fields' => [
                'user.lastname',
            ],
        ],
        'articles' => [
            'properties' => [
                'article_id ' => [
                    'required_fields' => [
                        'article.id'
                    ],
                ],
                'title' => [
                    'required_fields' => [
                        'article.title'
                    ],
                ],
                'user_id' => [
                    'required_fields' => [
                        'article.user_id',
                    ],
                ],
            ]
        ],
        'academics' => [
            'properties' => [
                'school' => [
                    'required_fields' => [
                        'academic.school',
                    ],
                ],
                'graduation' => [
                    'required_fields' => [
                        'academic.graduation',
                    ],
                ],
                'user_id' => [
                    'required_fields' => [
                        'academic.user_id',
                    ],
                ],
            ],
        ],
    ],
];

$mapper = new Mapper();
$mapper->addConfig('User', $output);
$fields = $mapper->getRequiredFields();

$neededFields = [];

foreach ($fields as $entityName => $entityFields) {
    foreach ($entityFields as $fieldName) {
        $treeFields = explode('.', $fieldName);
        $tree = recursiveArray($treeFields);
        $neededFields = populateNeededFields($neededFields, $tree);
    }
}

$pdo = new PDO('mysql:dbname=mevia;host=localhost', 'root', 'root');

foreach ($neededFields as $table => $fields) {
    $fields = array_unique(array_merge(['id'], $fields));
    $fields = implode(', ', $fields);
    switch ($table) {
        case 'user':
            break;
        case 'article':
            $queryArticles = $pdo->query('SELECT '.$fields.' FROM article LIMIT 1000');
            $articles = $queryArticles->fetchAll(PDO::FETCH_ASSOC);
            $mapper->populate('User', 'article', $articles);
            break;
        case 'academic':
            $queryAcademic = $pdo->query('SELECT '.$fields.' FROM academic LIMIT 1000');
            $academics = $queryAcademic->fetchAll(PDO::FETCH_ASSOC);
            $mapper->populate('User', 'academic', $academics);
            break;
        default:
            break;
    }
}

$elements = $mapper->toArray();




