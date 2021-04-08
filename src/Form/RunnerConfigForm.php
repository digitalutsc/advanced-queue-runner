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

        $modes = array(
            'limit' => $this
                ->t('Only run queue(s) if there is queued job(s)'),
            'full' => $this
                ->t('Always run the queue(s) no matter what.'),
        );

        $queue_str = '';
        foreach (array_keys($config->get('queues')) as $queue) {
            $queue_str .= "$queue<br />";
        }
        $queue_str .= '';
        if (isset($runnerID)) {
            $runner = new Runner();
            $runner->setPid($runnerID);
            if ($runner->status()) {
                // if the runner is running, show stop button
                $default = "Stop";
                $form['runner-id'] = [
                    '#markup' => $this->t('<p>Status (ID: ' . $runnerID . '): Running</p>')
                ];
                $form['runner'] = array(
                    '#type' => 'table',
                    '#header' => array(
                        $this
                            ->t('PID'),
                        $this
                            ->t('For queue(s)'),
                        $this
                            ->t('Interval'),
                        $this
                            ->t('Running Mode'),
                        $this
                            ->t('Started at'),
                        $this
                            ->t('Status'),
                    ),
                    '#rows' => [
                        'data' => [
                            $config->get('runner-pid'),
                            $this->t($queue_str),
                            $config->get('interval'),
                            $modes[$config->get('mode')],
                            date("F j, Y", $config->get('started_at')),
                            $this->t('<p>Running (PID: ' . $runnerID . ')</p>')
                        ]
                    ]
                );
            } else {
                // if not running, remove the PID
                $config->set('runner-pid', null);
                $config->save();
            }
        } else {
            $queues = \Drupal::entityQuery('advancedqueue_queue')->execute();
            foreach ($queues as $key => $value) {
                $queues[$key] = $value . " <a href='/admin/config/system/queues/jobs/$key' target='_blank'>&#9432;</a>";
            }
            $form['included-queues'] = array(
                '#type' => 'checkboxes',
                '#name' => 'queues',
                '#title' => $this->t('Select to include queue(s) into the runner:'),
                '#required' => TRUE,
                '#default_value' => 1,
                '#options' => $queues,
                '#default_value' => ($config->get("advancedqueue-id") !== null) ? $config->get("advancedqueue-id") : "default",
            );
            $form['interval'] = array(
                '#type' => 'number',
                '#title' => $this
                    ->t('Interval:'),
            );
            $form['running-mode'] = array(
                '#type' => 'radios',
                '#default_value' => 'limit',
                '#options' => $modes,
            );
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
            $configFactory->set('queues', $form_state->getValues()['included-queues']);
            $configFactory->set('interval', $form_state->getValues()['interval']);
            $configFactory->set('mode', $form_state->getValues()['running-mode']);
            $configFactory->set('started_at', time());

            $process = new Runner('php ' . __DIR__ . '/../Scripts/jobs.php');
            $my_pid = $process->getPid();
            $status = $process->status();

            if ($status) {
                \Drupal::messenger()->addMessage(t('The Runner is now active.'));
                $configFactory->set('runner-pid', $my_pid);
            }
        } else {
            // stop runner
            $runner = new Runner();
            $runner->setPid($runnerID);
            $runner->stop();

            if (!$runner->status()) {
                \Drupal::messenger()->addMessage(t('The Runner has been stopped.'));
                $configFactory->set('runner-pid', null);
                $configFactory->set('queues', null);
                $configFactory->set('interval', null);
                $configFactory->set('mode', null);
                $configFactory->set('started_at', null);
            }
        }

        // save the config
        $configFactory->save();
    }


}
