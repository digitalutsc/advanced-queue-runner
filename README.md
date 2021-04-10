# Advanced Queue Runner

## Introduction

This module provides a way to run Advanced queue (by Drush command) automatically as daemon without manually using a Drush command or running a Cron job.

## Requirements:

* Advanced Queue module (https://www.drupal.org/project/advancedqueue)
* ReactPHP components: Event Loop (https://reactphp.org/event-loop/) and Child Process (https://github.com/reactphp/child-process)

## Installation:

* In Drupal.org: https://www.drupal.org/project/advancedqueue_runner
* Highly recommend to install the module by using composer: `composer require 'drupal/advancedqueue_runner:^1.0@alpha'`. 
* Please run the bellow commands to install its dependencies:
  * `composer require 'drupal/advancedqueue:^1.0@RC'`
  * `composer require "react/child-process": "^1.0"`
  * `composer require "react/event-loop": "^1.0"`


## Usage:

* Please visit `/admin/config/advancedqueue/runner` to configure and start the runner.

## Maintainer

* [Kyle Huynh](https://github.com/kylehuynh205)
* [Nat Kanthan](https://github.com/Natkeeran)
