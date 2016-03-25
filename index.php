<?php

include_once('src/Mapper.php');
include_once('src/Tree.php');
include_once('src/AlreadyExistsException.php');
include_once('src/Config/Config.php');
include_once('src/Config/InvalidException.php');
include_once('src/Config/MissingEntryException.php');
include_once('src/Config/EmptyException.php');

use Mapping\Mapper;

$output = [
    'properties' => [
        'id' => [
            'required_fields' => [
                'user_id',
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
        'fullname' => [
            'callback' => 'user_fullname',
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
            ],
        ],
    ],
];

$mapper = new Mapper();
$mapper->addConfig('User', $output);
$fields = $mapper->getRequiredFields();
var_dump($fields);

var_dump($mapper);

$pdo = new PDO('mysql:dbname=mevia;host=localhost', 'root', 'root');

$queryArticles = $pdo->query('SELECT * FROM article LIMIT 1000');
$articles = $queryArticles->fetchAll(PDO::FETCH_ASSOC);
$mapper->populate('User', 'article', $articles);

$usersIds = array_values(array_unique(array_column($articles, 'user_id')));
$queryAcademic = $pdo->prepare('SELECT * FROM academic WHERE user_id IN (:user_id) LIMIT 100');
$queryAcademic->bindValue(':user_id', implode(',',$usersIds), PDO::PARAM_STR);
$queryAcademic->execute();
$academics = $queryAcademic->fetchAll(PDO::FETCH_ASSOC);

$mapper->populate('User', 'academic', $academics);
