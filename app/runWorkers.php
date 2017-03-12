<?php

require '/var/www/html/vendor/autoload.php';
$app = require '/var/www/html/app/bootstrap.php';
$class = $argv[1];

$class = sprintf('\Bookmarker\Jobs\Workers\%s', $class);
if (!class_exists($class)) {
    throw new \InvalidArgumentException('Requested worker not found');
}
$worker = new $class($app);
$status = 0;

$config = \Bookmarker\Registry::get('app')['config'][APP_ENV]['beanstalkd'];

$pheanstalk = new \Pheanstalk\Pheanstalk($config['host']);
$pheanstalk->watch($config['convert_tube'])
    ->ignore('default');
try {
    while ($job = $pheanstalk->reserve()) {
        $worker->accept($job);
        $pheanstalk->delete($job);
    }
} catch (\Exception $e) {
    $status = 1;
    $app['logger']->addError($e);
    $pheanstalk->bury($job);
}

exit($status);


