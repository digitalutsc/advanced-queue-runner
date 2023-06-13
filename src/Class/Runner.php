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
    return self::statusByPid($this->pid);
  }

  /**
   * Get Status by PID.
   */
  public static function statusByPid($pid) {
    // Check if the ps command is provided by BusyBox. BusyBox's ps does not
    // have a -p option so check if the process exists using procfs instead.
    $check_ps = 'exec 2>/dev/null; readlink "/bin/ps"';
    $retval = exec($check_ps);
    if ($retval && preg_match('/^.*\/busybox$/', $retval)) {
      $command = 'test -h /proc/' . $pid . '/exe';
      exec($command, $op, $rstat);
      return $rstat === 0;
    }
    $command = 'ps -p ' . $pid;
    exec($command, $op);
    return isset($op[1]);
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
