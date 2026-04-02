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
    // phpcs:ignore -- \Drupal calls should be avoided in classes, use dependency injection instead
    $entity = \Drupal::entityTypeManager()->getStorage('advancedqueue_queue')->load($queue);
    $jobs = $entity->getBackend()->countJobs()['queued'];
    return $jobs;
  }

}
