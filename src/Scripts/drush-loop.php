<?php declare(strict_types=1);

require __DIR__ . '/../../../../../../vendor/autoload.php';

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

$loop = React\EventLoop\Factory::create();
// Run cron every twenty minutes.
$loop->addPeriodicTimer(1200, function () {
  run_command('drush cron');
});
// Every thirty seconds, process jobs from queue1
$loop->addPeriodicTimer(30, function () {
  run_command(sprintf('drush advancedqueue:queue:process queue1'));
});
// Every two minutes, process jobs from queue2
$loop->addPeriodicTimer(120, function () {
  run_command(sprintf('drush advancedqueue:queue:process queue2'));
});
$loop->run();
