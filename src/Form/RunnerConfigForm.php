<?php

namespace Drupal\advancedqueue_runner\Form;
include __DIR__ . "/../Class/Runner.php";

use Drupal\advancedqueue_runner\Classes\Runner;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Class RunnerConfigForm.
 */
class RunnerConfigForm extends ConfigFormBase
{

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return [
      'advancedqueue_runner.runnerconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'runner_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('advancedqueue_runner.runnerconfig');

    $form = [];//parent::buildForm($form, $form_state);

    $runnerID = $config->get('runner-pid');

    $default = "Run";
    if (isset($runnerID)) {
      $default = "Stop";

      $form['runner-id'] = [
        '#markup' => $this->t('<p>Status: </p>' . $runnerID)
      ];
    }

    $form['example_select'] = [
      '#type' => 'submit',
      '#value' => $this->t($default),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    parent::submitForm($form, $form_state);

    /*$configFactory = $this->configFactory->getEditable('advancedqueue_runner.runnerconfig');

    // Then you can start/stop/ check status of the job.
    $process = new Runner('php ' . __DIR__ . '/../Scripts/jobs.php');
    $my_pid = $process->getPid();
    print_log($my_pid);
    $status = $process->status();
    print_log($status);
    if ($status) {
      $configFactory->set('runner-pid', $my_pid);
      print_log($my_pid);
      $configFactory->save();
    } else {
      print_log("The process is not running");;
    }
    */
    exec("disown php /Applications/XAMPP/xamppfiles/htdocs/demo9/web/modules/custom/advancedqueue_runner/src/Form/../Scripts/jobs.php 2>&1", $output);

    print_log($output);

  }


}
