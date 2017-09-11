<?php

require '../vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Boivie\Spotify\SpotifyApi;
use Boivie\DailyDouble\Search;
use Boivie\DailyDouble\Update;
use Noodlehaus\Config;

$config = Config::load('config.yml');

$slimConfig = [
    'displayErrorDetails' => true,
    'db' => [
            'host' => $config->get('database.host'),
            'name' => $config->get('database.name'),
            'user' => $config->get('database.user'),
            'password' => $config->get('database.password'),
            'charset' => $config->get('database.charset'),
        ],
    'spotify' => $config->get('spotify'),
];

$app = new \Slim\App(['settings' => $slimConfig]);

$container = $app->getContainer();

$container['logger'] = function ($c) {
    $logger = new \Monolog\Logger('logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};
$container['db'] = function ($c) {
    $db = $c['settings']['db'];

    $dsn = "mysql:host=".$db['host'].";dbname=".$db['name'].";charset=".$db['charset'];

    $pdo = new PDO($dsn, $db['user'], $db['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$app->get('/search/{term}', function (Request $request, Response $response) {
    $spotify = $this->get('settings')['spotify'];

    $api = (new SpotifyApi(
        $spotify['client_id'],
        $spotify['client_secret'],
        $spotify['redirect_URI']
    ))->getApiWrapper();

    $search = new Search($api, $this->db);

    $results = $search->getSongs($args['term']);

    $response->write(json_encode($results));

    return $response;
});

$app->get('/update', function (Request $request, Response $response) {
    $spotify = $this->get('settings')['spotify'];

    $api = (new SpotifyApi(
        $spotify['client_id'],
        $spotify['client_secret'],
        $spotify['redirect_URI']
    ))->getApiWrapper();

    $update = new Update($api, $this->db);

    $status = $update->updatePlaylists();

    if ($status !== true) {
        $response->getBody()->write('Something went wrong');

        return $response;
    }

    $response->getBody()->write('Playlists updated');

    return $response;
});

$app->run();
