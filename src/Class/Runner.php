<?php

namespace Drupal\advancedqueue_runner\Classes;

/**
 * Advanced queue runner process.
 */
class Runner {

  /**
   * Process ID.
   *
   * @var pid
   */
  private $pid;

  /**
   * Linux command to execute.
   *
   * @var command
   */
  private $command;

  /**
   * Implements constructor.
   */
  public function __construct($cl = FALSE) {
    if ($cl != FALSE) {
      $this->command = $cl;
      $this->runCom();
    }
  }

  /**
   * Run command process.
   */
  private function runCom() {
    $command = 'nohup ' . $this->command . ' > /dev/null 2>&1 & echo $!';
    /*$command = 'nohup ' . $this->command . ' > '
    . __DIR__ . '/../Scripts/nohup.out 2>&1 & echo $!';*/
    exec($command, $op);
    $this->pid = (int) $op[0];
  }

  /**
   * Set process ID.
   */
  public function setPid($pid) {
    $this->pid = $pid;
  }

  /**
   * Get process ID.
   */
  public function getPid() {
    return $this->pid;
  }

  /**
   * Get Status.
   */
  public function status() {
    $command = 'ps -p ' . $this->pid;
    exec($command, $op);
    if (!isset($op[1])) {
      return FALSE;
    }
    else {
      return TRUE;
    }
  }

  /**
   * Get Status by PID.
   */
  public static function statusByPid($pid) {
    $command = 'ps -p ' . $pid;
    exec($command, $op);
    if (!isset($op[1])) {
      return FALSE;
    }
    else {
      return TRUE;
    }
  }

  /**
   * Start Process.
   */
  public function start() {
    if ($this->command != '') {
      $this->runCom();
    }
    else {
      return TRUE;
    }
  }

  /**
   * Stop Process.
   */
  public function stop() {
    $command = 'kill ' . $this->pid;
    exec($command);
    if ($this->status() == FALSE) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
