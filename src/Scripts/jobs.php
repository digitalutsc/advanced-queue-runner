<?php

// Inspired from https://mglaman.dev/blog/using-reactphp-run-drupal-tasks

//$autoloader = require __DIR__ . '/../../../../../../vendor/autoload.php';
$autoloader = require $_SERVER['PWD'].'/../vendor/autoload.php';

/**
 * Run drush command
 * @param string $command
 */
function drush_advancedqueue(string $command): void
{
    $loop = React\EventLoop\Factory::create();
    $process = new React\ChildProcess\Process($command);
    $process->start($loop);

    $process->on('exit', function ($exitCode) use ($command) {

    });
    $process->stdout->on('data', function ($chunk) use ($command) {

    });
    $process->stderr->on('data', function ($chunk) use ($command) {

    });
    // logging error message if there is.
    $process->stdout->on('error', function (Exception $e) use ($command) {
        // Log an error.
        $msg = "Error with" . $e->getMessage();
        //error_log(print_r("$msg", true), 0);
        drupal_log($msg);
    });
    $process->stderr->on('error', function (Exception $e) use ($command) {
        // Log an error.
        $msg = "ReactPHP Eventloop - stderr Error with" . $e->getMessage();
        //error_log(print_r("$msg", true), 0);
        drupal_log($msg);
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

// Load Drupal kernel
$kernel = new Drupal\Core\DrupalKernel('prod', $autoloader);
$kernel::bootEnvironment();
$kernel->setSitePath('sites/default');
Drupal\Core\Site\Settings::initialize($kernel->getAppRoot(), $kernel->getSitePath(), $autoloader);
$kernel->boot();
$kernel->preHandle($request);

// get configuration setup in /admin/config/advancedqueue/runner
$config = \Drupal::config('advancedqueue_runner.runnerconfig');
$queues = $config->get('queues');
$interval = $config->get('interval');
$mode = $config->get('mode');
$base_url = $config->get('base_url');
$drush_path = $config->get('drush_path');
$root_path = $config->get('root_path');

// run EventLoop
$loop = React\EventLoop\Factory::create();
$loop->addPeriodicTimer($interval, function () use ($queues, $mode, $base_url, $kernel, $drush_path, $root_path) {
    try {
        foreach ($queues as $queue) {
            $command = sprintf($drush_path . ' --root=' . $root_path . ' --uri=' . $base_url . ' advancedqueue:queue:process ' . $queue);

            if ($mode === 'limit') {
                $connection = $kernel->getContainer()->get('database');
                $jobs = $connection->query("SELECT count(job_id) FROM advancedqueue where queue_id = '$queue' and state = 'queued'")->fetchCol()[0];

                // only run queue if there is queued job in it.
                if ($jobs > 0) {
                    drush_advancedqueue($command);
                }
            } else {
                drush_advancedqueue($command);
            }

        }
    }
    catch(\Exception $e) {
        drupal_log($e->getMessage());
    }
});
$loop->run();


