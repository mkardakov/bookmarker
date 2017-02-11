<?php
define('APP_ENV', 'local');
define('APP_FILE_STORAGE', 'local');
define('ROOT', realpath(__DIR__ . '/../') . '/');
define('RESPONSE_FORMAT', 'json');

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Silex\Application;

$app = new Application();
$app->register(new \DerAlex\Pimple\YamlConfigServiceProvider(ROOT . '/app/config/settings.yml'));

$app['debug'] = $app['config'][APP_ENV]['debug'];

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    // prevent using of session cookies
    'security.firewalls' => array(
        // allow to register only
        'sign_up' => array(
            'pattern' =>  new \Symfony\Component\HttpFoundation\RequestMatcher('^/user$', null, ['POST']),
            'stateless' => true,
            'anonymous' => true
        ),
        // other routes secured by auth
        'api' => array(
            'pattern' => '.*',
            'http' => true,
            'stateless' => true,
            'anonymous' => false,
            'users' => function($app) {
                return new \Bookmarker\Providers\UserProvider($app['orm.em']);
            },
        ),
        'default' => array(
            'stateless' => true
        )
    ),
));

$app['routes'] = $app->extend('routes', function (RouteCollection $routes, Application $app) {
    $loader = new YamlFileLoader(new FileLocator(ROOT . '/app/config'));
    $collection = $loader->load('routes.yml');
    $routes->addCollection($collection);

    return $routes;
});

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => ROOT.'/logs/monolog.log',
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => $app['config'][APP_ENV]['database']
));

$app->register(new DoctrineOrmServiceProvider(), array(
    'orm.proxies_dir' => ROOT . $app['config'][APP_ENV]['database']['proxy_path'],
    'orm.proxies_namespace' => 'DoctrineProxies',
    'orm.auto_generate_proxies' => false,
    'db.orm.entities' => array(
        'type' => 'annotation',
        'path' => array(ROOT . $app['config'][APP_ENV]['database']['entities_path']),
        'namespace' => 'Bookmarker\Db\Entities',
    ),
    'orm.em.options' => array(
        'mappings' => array(
            array(
                'type' => 'annotation',
                'namespace' => 'Bookmarker\Db\Entities',
                'alias' => 'doctrine',
                'use_simple_annotation_reader' => false,
                'path' => ROOT . $app['config'][APP_ENV]['database']['entities_path']
            )
        )
    )
));
$app->register(new JDesrosiers\Silex\Provider\JmsSerializerServiceProvider(), array(
    "serializer.srcDir" => ROOT . "/vendor/jms/serializer/src"
));

/**
 * Basic support of OPTIONS method for CORS feature at documentation
 */
$app->before(function(\Symfony\Component\HttpFoundation\Request $request) use($app) {
    if ($request->getMethod() === 'OPTIONS') {
        header('Allow: GET,POST,OPTIONS,DELETE,PUT,PATCH');
        exit();
    }
}, Application::EARLY_EVENT);

$loader = require ROOT . 'vendor/autoload.php';
Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, "loadClass"));

return $app;