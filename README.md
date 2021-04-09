# Advanced Queue Runner

## Introduction

This module provides a way to run Advanced queue (by Drush command) automatically as daemon without manually using a Drush command or running a Cron job.

## Requirements:

* Advanced Queue module (https://www.drupal.org/project/advancedqueue)
* ReactPHP components: Event Loop (https://reactphp.org/event-loop/) and Child Process (https://github.com/reactphp/child-process)

## Installation:

* In Drupal.org: https://www.drupal.org/project/advancedqueue_runner
* Highly recommend to install the module by using composer: `composer require 'drupal/advancedqueue_runner:^1.0@alpha'`. It helps to install its dependencies and required above ReactPHP components automatically.
* If you choose to download the module files, please manually run the bellow commands to install its dependencies:
  * `composer require 'drupal/advancedqueue:^1.0@RC'`
  * `composer require "react/child-process": "^1.0"`
  * `composer require "react/event-loop": "^1.0"`


## Usage:

* Please visit `/admin/config/advancedqueue/runner` to configure and start the runner.

