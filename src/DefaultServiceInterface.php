<?php

namespace Drupal\advancedqueue_runner;

/**
 * Interface DefaultServiceInterface for services.
 */
interface DefaultServiceInterface {

  /**
   * Count number of Jobs in a queue.
   */
  public function countJob(String $queue);

}
