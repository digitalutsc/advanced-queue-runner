<?php

namespace Drupal\advancedqueue_runner\Form;

use Drupal\advancedqueue_runner\Classes\Runner;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class RunnerConfigForm definition.
 */
class RunnerConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'advancedqueue_runner.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'runner_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    $config = $this->config('advancedqueue_runner.settings');

    $form = [];
    $form['base_url'] = [
      '#type' => 'hidden',
      '#value' => $base_url,
    ];

    $runnerID = $config->get('runner-pid');
    $default = "Run";

    $modes = [
      'limit' => $this
        ->t('Only run queue(s) if there is queued job(s)'),
      'full' => $this
        ->t('Always run the queue(s) no matter what.'),
    ];
    /** @var string $queue_str */
    $queue_str = '';
    if ($config->get('queues') !== NULL) {
      foreach ($config->get('queues') as $queue) {
        $queue_str .= "$queue<br />";
      }
      $queue_str .= '';
    }
    if (isset($runnerID)) {
      $runner = new Runner();
      $runner->setPid($runnerID);
      if ($runner->status()) {
        // If the runner is running, show stop button.
        $default = "Stop";
        $form['runner'] = [
          '#type' => 'table',
          '#header' => [
            $this
              ->t('PID'),
            $this
              ->t('Advanced Queue(s)'),
            $this
              ->t('Interval'),
            $this
              ->t('Environment variables (for Drush)'),
            $this
              ->t('Started since'),
            $this
              ->t('Status'),
          ],
          '#rows' => [
            'data' => [
              $config->get('runner-pid'),
              new FormattableMarkup($queue_str, []),
              $config->get('interval') . " second(s)",
              new FormattableMarkup("<ul>
                <li>Drush path: <code>" . $config->get('drush_path') . "</code></li>
                <li>Site path: <code>" . $config->get('root_path') . "</code></li>
                <li>HOME: <code>" . getenv("HOME") . "</code></li>
              </ul>", []),
              new FormattableMarkup(date("F j, Y, g:i a", $config->get('started_at')) . (($config->get("auto-restart-in-cron") == 1) ? "<br />(Re-run automatically when cron runs, if interupted)" : ""), []),
              $this->t('<p>Active</p>'),
            ],
          ],
        ];
      }
      else {
        // If not running, remove the PID.
        $config->set('runner-pid', NULL);
        $config->save();
        \Drupal::messenger()->addMessage(t('Sorry, the Advanced Queue Runner is not currently running. Please refresh the page to start it again.'), 'error');
        return $form;
      }
    }
    else {
      $queues = \Drupal::entityQuery('advancedqueue_queue')->execute();
      foreach ($queues as $key => $value) {
        $queues[$key] = $value . " <a href='/admin/config/system/queues/jobs/$key' target='_blank'>&#9432;</a>";
      }
      $form['drush-path'] = [
        '#type' => 'textfield',
        '#title' => $this
          ->t('Drush Path:'),
        '#required' => TRUE,
        '#default_value' => ($config->get("drush_path") !== NULL) ? $config->get("drush_path") : "",
        '#description' => $this->t('For example: <code>/var/www/html/drupal/vendor/drush/drush/drush</code>'),
      ];
      $form['root-path'] = [
        '#type' => 'textfield',
        '#title' => $this
          ->t('Root Path:'),
        '#required' => TRUE,
        '#default_value' => ($config->get("root_path") !== NULL) ? $config->get("root_path") : "",
        '#description' => $this->t('For example: <code>/var/www/html/drupal</code>'),
      ];

      $form['included-queues'] = [
        '#type' => 'checkboxes',
        '#name' => 'queues',
        '#title' => $this->t('Select which queue(s) to run:'),
        '#required' => TRUE,
        '#options' => $queues,
        '#default_value' => ($config->get("advancedqueue-id") !== NULL) ? $config->get("queues") : ["default"],
      ];
      $form['interval'] = [
        '#type' => 'number',
        '#title' => $this
          ->t('Interval:'),
        '#description' => new FormattableMarkup('In second(s). ', []),
        '#default_value' => ($config->get("interval") !== NULL) ? $config->get("interval") : 5,
        '#required' => TRUE,
      ];
      $form['auto-restart-in-cron'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable re-run automatically the runner when cron runs if the runner were interupted (ie. server reboot).'),
        '#default_value' => ($config->get("auto-restart-in-cron") !== NULL) ? $config->get("auto-restart-in-cron") : 0,
      ];
    }
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => new FormattableMarkup($default, []),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // Ensure the HOME enviroment variable is set.
    set_environment_home();

    // Get existing config.
    $configFactory = $this->configFactory->getEditable('advancedqueue_runner.settings');
    if (!empty($form_state->getValues()['base_url'])) {
      $configFactory->set('base_url', $form_state->getValues()['base_url']);
    }

    if (!empty($form_state->getValues()['drush-path'])) {
      $configFactory->set('drush_path', $form_state->getValues()['drush-path']);
    }

    if (!empty($form_state->getValues()['root-path'])) {
      $configFactory->set('root_path', $form_state->getValues()['root-path']);
    }

    if (!empty($form_state->getValues()['auto-restart-in-cron'])) {
      $configFactory->set('auto-restart-in-cron', $form_state->getValues()['auto-restart-in-cron']);
    }

    $runnerID = $configFactory->get('runner-pid');

    if (!isset($runnerID)) {
      // Start the runner.
      $configFactory->set('queues', array_filter($form_state->getValues()['included-queues']));
      $configFactory->set('interval', $form_state->getValues()['interval']);
      $configFactory->set('started_at', time());
      // Save the config.
      $configFactory->save();

      $process = new Runner('php ' . __DIR__ . '/../Scripts/jobs.php');
      $my_pid = $process->getPid();
      $status = $process->status();

      if ($status) {
        \Drupal::messenger()->addMessage(t('The Runner is now active.'));
        $configFactory->set('runner-pid', $my_pid);
      }
    }
    else {
      // Stop runner.
      $runner = new Runner();
      $runner->setPid($runnerID);
      $runner->stop();
    }

    // Save the config.
    $configFactory->save();
  }

}
