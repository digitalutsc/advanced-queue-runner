<?php

namespace Drupal\advancedqueue_runner;

/**
 * Class DefaultService.
 */
class DefaultService implements DefaultServiceInterface {

  /**
   * Constructs a new DefaultService object.
   */
  public function __construct() {

  }

  public function countJob(String $queue) {
      $entity = \Drupal::entityTypeManager()->getStorage('advancedqueue_queue')->load($queue);
      $jobs = $entity->getBackend()->countJobs()['queued'];
      return $jobs;
  }

}
