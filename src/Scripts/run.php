<?php


// Then you can start/stop/ check status of the job.
$process = new Process('php ./jobs.php');
$my_pid = $process->getPid();
error_log(print_r($my_pid, true), 0);
echo $my_pid;
if ($process->status()) {
  echo "The process is currently running";
} else {
  echo "The process is not running.";
}

class Process
{
  private $pid;
  private $command;

  public function __construct($cl = false)
  {
    if ($cl != false) {
      $this->command = $cl;
      $this->runCom();
    }
  }

  private function runCom()
  {
    //$command = 'nohup ' . $this->command . ' > /dev/null 2>&1 & echo $!';
    $command = 'nohup ' . $this->command . ' > ./nohup.out 2>&1 & echo $!';
    exec($command, $op);
    $this->pid = (int)$op[0];
  }

  public function setPid($pid)
  {
    $this->pid = $pid;
  }

  public function getPid()
  {
    return $this->pid;
  }

  public function status()
  {
    $command = 'ps -p ' . $this->pid;
    exec($command, $op);
    if (!isset($op[1])) return false;
    else return true;
  }

  public function start()
  {
    if ($this->command != '')
      $this->runCom();
    else return true;
  }

  public function stop()
  {
    $command = 'kill ' . $this->pid;
    exec($command);
    if ($this->status() == false) return true;
    else return false;
  }
}
