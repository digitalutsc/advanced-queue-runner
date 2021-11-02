<?php

namespace Drupal\advancedqueue_runner;

/**
 * Class DefaultService definition.
 */
class DefaultService implements DefaultServiceInterface {

  /**
   * Implements countJob().
   */
  public function countJob(String $queue) {
    $entity = \Drupal::entityTypeManager()->getStorage('advancedqueue_queue')->load($queue);
    $jobs = $entity->getBackend()->countJobs()['queued'];
    return $jobs;
  }

}
