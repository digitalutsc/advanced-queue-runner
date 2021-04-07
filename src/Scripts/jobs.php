<?php

$autoloader = require __DIR__ . '/../../../../../../vendor/autoload.php';

function run_command(string $command): void {
  $loop = React\EventLoop\Factory::create();
  $process = new React\ChildProcess\Process($command);
  $process->start($loop);
  $process->on('exit', function ($exitCode) use ($command) {
    // Trigger alerts that the command finished.
  });
  $process->stdout->on('data', function ($chunk) {
    // Optinally log the output.
  });
  $process->stdout->on('error', function (Exception $e) use ($command) {
    // Log an error.
  });
  $process->stderr->on('data', function ($chunk) use ($command) {
    if (!empty(trim($chunk))) {
      // Log output from stderr
    }
  });
  $process->stderr->on('error', function (Exception $e) use ($command) {
    // Log an error.
  });
  $loop->run();
}

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
$request->attributes->set(
    Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT,
    new Symfony\Component\Routing\Route('<none>')
);
$request->attributes->set(
    Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_NAME,
     '<none>'
);

$kernel = new Drupal\Core\DrupalKernel('prod', $autoloader);
$kernel::bootEnvironment();
$kernel->setSitePath('sites/default');
Drupal\Core\Site\Settings::initialize($kernel->getAppRoot(), $kernel->getSitePath(), $autoloader);
$kernel->boot();
$kernel->preHandle($request);

$loop = React\EventLoop\Factory::create();
$loop->addPeriodicTimer(1, function () {
    //$cron = \Drupal::service('cron');
    //$cron->run();
    print "Loop running";
});
$loop->run();


