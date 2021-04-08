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
            $runner = new Runner();
            $runner->setPid($runnerID);
            if ($runner->status()) {
                // if the runner is running, show stop button
                $default = "Stop";
                $form['runner-id'] = [
                    '#markup' => $this->t('<p>Status (ID: '.$runnerID.'): Running</p>')
                ];
            }
            else {
                // if not running, remove the PID
                $config->set('runner-pid', null);
                $config->save();
            }
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

        // get existing config
        $configFactory = $this->configFactory->getEditable('advancedqueue_runner.runnerconfig');
        $runnerID = $configFactory->get('runner-pid');

        if (!isset($runnerID)) {
            // Start the runner
            $process = new Runner('php ' . __DIR__ . '/../Scripts/jobs.php');
            $my_pid = $process->getPid();
            $status = $process->status();

            if ($status) {
                \Drupal::messenger()->addMessage(t('The process is now running.'), 'success');
                $configFactory->set('runner-pid', $my_pid);
                $configFactory->save();
            }
        } else {
            // stop runner
            $runner = new Runner();
            $runner->setPid($runnerID);
            $runner->stop();

            if (!$runner->status()) {
                \Drupal::messenger()->addMessage(t('The runner has been stopped.'), 'success');
                $configFactory->set('runner-pid', null);
                $configFactory->save();
            }

        }


    }


}
