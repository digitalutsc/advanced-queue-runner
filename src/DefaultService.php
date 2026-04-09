<?php

namespace Drupal\advancedqueue_runner;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class DefaultService definition.
 */
class DefaultService implements DefaultServiceInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new DefaultService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Implements countJob().
   */
  public function countJob(String $queue) {
    $entity = $this->entityTypeManager->getStorage('advancedqueue_queue')->load($queue);
    $jobs = $entity->getBackend()->countJobs()['queued'];
    return $jobs;
  }

}
