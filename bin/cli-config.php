<?php
// retrieve EntityManager
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

$app = require_once __DIR__ . '/../app/bootstrap.php';
$app->boot();

$isDevMode = $app['debug'];
$paths = $app['db.orm.entities']['path'];
$proxyPath = $app['orm.proxies_dir'];
$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, $proxyPath, null, false);
$entityManager = EntityManager::create($app['db.options'], $config);

return ConsoleRunner::createHelperSet($entityManager);