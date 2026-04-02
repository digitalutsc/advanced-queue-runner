<?php

namespace Drupal\Tests\advancedqueue_runner\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Simple test to ensure that main page loads with module enabled.
 *
 * @group advancedqueue_runner
 */
#[RunTestsInSeparateProcesses]
class LoadTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['advancedqueue_runner'];

  /**
   * {@inheritdoc}
   */
  // phpcs:ignore -- Do not disable strict config schema checking in tests.
  protected $strictConfigSchema = FALSE;

  /**
   * A user with permissions required to access the configuration form.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->user = $this->drupalCreateUser([
      'access administration pages',
      'administer site configuration',
    ]);
    $this->drupalLogin($this->user);
  }

  /**
   * Tests that the home page loads with a 200 response.
   */
  public function testLoad(): void {
    $this->drupalGet(Url::fromRoute('advancedqueue_runner.runner_config_form'));
    $this->assertSession()->statusCodeEquals(200);
  }

}
