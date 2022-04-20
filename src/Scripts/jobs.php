<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Description of Advanced Queue Runner Job.
 */

use React\EventLoop\Loop;
use React\ChildProcess\Process;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;

$autoloader = require $_SERVER['PWD'] . '/../vendor/autoload.php';

/**
 * Run drush command with ReactPHP components.
 *
 * @param string $command
 *   The linux command to execute.
 */
function drush_advancedqueue(string $command): void {
  // https://mglaman.dev/blog/using-reactphp-run-drupal-tasks.
  $loop = Loop::create();
  $process = new Process($command);
  $process->start($loop);

  $process->on('exit', function ($exitCode) use ($command) {

  });
  $process->stdout->on('data', function ($chunk) use ($command) {

  });
  $process->stderr->on('data', function ($chunk) use ($command) {

  });
  // Logging error message if there is.
  $process->stdout->on('error', function (\Exception $e) use ($command) {
    // Log an error.
    $msg = "Error with" . $e->getMessage();

    // Log the message to Recent Log Message console.
    drupal_log($msg);
  });
  $process->stderr->on('error', function (\Exception $e) use ($command) {
    // Log an error.
    $msg = "ReactPHP Eventloop - stderr Error with" . $e->getMessage();

    // Log the message to Recent Log Message console.
    drupal_log($msg);
  });
  $loop->run();
}

$request = Request::createFromGlobals();
$request->attributes->set(
  RouteObjectInterface::ROUTE_OBJECT,
  new Route('<none>')
);
$request->attributes->set(
  RouteObjectInterface::ROUTE_NAME,
  '<none>'
);

// Load Drupal kernel.
$kernel = new DrupalKernel('prod', $autoloader);
$kernel::bootEnvironment();
$kernel->setSitePath('sites/default');
Settings::initialize($kernel->getAppRoot(), $kernel->getSitePath(), $autoloader);
$kernel->boot();
$kernel->preHandle($request);

// Get configuration setup in /admin/config/advancedqueue/runner.
$config = \Drupal::config('advancedqueue_runner.settings');
$queues = $config->get('queues');
$interval = $config->get('interval');
$mode = $config->get('mode');
$base_url = $config->get('base_url');
$drush_path = $config->get('drush_path');
$root_path = $config->get('root_path');

// Run EventLoop.
$loop = Loop::get();
$loop->addPeriodicTimer($interval, function () use ($queues, $mode, $base_url, $kernel, $drush_path, $root_path) {
  try {
    foreach ($queues as $queue) {
      // @codingStandardsIgnoreLine
      $command = sprintf($drush_path . ' --root=' . $root_path . ' --uri=' . $base_url . ' advancedqueue:queue:process ' . $queue);

      // run the queued jobs
      $connection = $kernel->getContainer()->get('database');
      $jobs = $connection->query("SELECT count(job_id) FROM advancedqueue where queue_id = '$queue' and state = 'queued'")->fetchCol()[0];

      // Only run queue if there is queued job in it.
      if ($jobs > 0) {
        drush_advancedqueue($command);
      }
    }
  }
  catch (\Exception $e) {
    drupal_log($e->getMessage());
  }
});
$loop->run();
