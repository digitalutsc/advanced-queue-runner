<?php

$autoloader = require __DIR__ . '/../../../../../../vendor/autoload.php';

function run_command(string $command): void
{
    //error_log(print_r(">>>>>>>>>>>>>>>>>>>>>>", true), 0);
    //error_log(print_r($command, true), 0);
    $loop = React\EventLoop\Factory::create();
    $process = new React\ChildProcess\Process($command);
    $process->start($loop);
    $process->on('exit', function ($exitCode) use ($command) {
        // Trigger alerts that the command finished.
        //error_log(print_r("Exit....", true), 0);
  });
    $process->stdout->on('data', function ($chunk) {
        // Optinally log the output.
        //error_log(print_r("Data....", true), 0);
    });
    $process->stdout->on('error', function (Exception $e) use ($command) {
        // Log an error.
        //error_log(print_r("Error....", true), 0);
    });
    $process->stderr->on('data', function ($chunk) use ($command) {
        //error_log(print_r("data....", true), 0);
        if (!empty(trim($chunk))) {
            // Log output from stderr
        }
    });
    $process->stderr->on('error', function (Exception $e) use ($command) {
        // Log an error.
    });
    $loop->run();
    //error_log(print_r(">>>>>>>>>>>>>>>>>>>>>>", true), 0);
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

$config = \Drupal::config('advancedqueue_runner.runnerconfig');
$queues = $config->get('queues');
$interval = $config->get('interval');
$mode = $config->get('mode');
$base_url = $config->get('base_url');

$loop = React\EventLoop\Factory::create();
$loop->addPeriodicTimer($interval, function () use ($queues, $mode, $base_url, $kernel) {
    foreach ($queues as $queue) {
        $command = sprintf(__DIR__ . '/../../../../../../vendor/drush/drush/drush --uri=' . $base_url . ' advancedqueue:queue:process ' . $queue);
        if ($mode === 'limit') {
            $connection = $kernel->getContainer()->get('database');
            $jobs = $connection->query("SELECT count(job_id) FROM advancedqueue where queue_id = '$queue' and state = 'queued'")->fetchCol()[0];

            // only run queue if there is queued job in it.
            if ($jobs > 0) {
                run_command(sprintf($command));
            }
        }
        else {
            run_command(sprintf($command));
        }

    }

});
$loop->run();

