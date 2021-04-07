<?php

namespace Drupal\advancedqueue_runner\Classes;


class Runner
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
    $command = 'nohup ' . $this->command . ' > /dev/null 2>&1 & echo $!';
    //$command = 'nohup ' . $this->command . ' > '.__DIR__.'/../Scripts/nohup.out 2>&1 & echo $!';
    print_log($command);
    exec($command, $op);
    print_log($op);
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
    print_log($op);
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
