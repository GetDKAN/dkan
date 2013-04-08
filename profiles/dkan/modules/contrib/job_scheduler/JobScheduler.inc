<?php

/**
 * @file
 * JobScheduler class.
 */

/**
 * Use to make Job Scheduler exceptions identifiable by type.
 */
class JobSchedulerException extends Exception {}

/**
 * Manage scheduled jobs.
 */
class JobScheduler {
  /**
   * The name of this scheduler.
   */
  protected $name;

  /**
   * Produces a single instance of JobScheduler for a schedule name.
   */
  public static function get($name) {
    static $schedulers;
    // Instantiante a new scheduler for $name if we haven't done so yet.
    if (!isset($schedulers[$name])) {
      $class = variable_get('job_scheduler_class_'. $name, 'JobScheduler');
      $schedulers[$name] = new $class($name);
    }
    return $schedulers[$name];
  }

  /**
   * Creates a JobScheduler object.
   */
  protected function __construct($name) {
    $this->name = $name;
  }

  /**
   * Returns scheduler info.
   *
   * @see hook_cron_job_scheduler_info().
   *
   * @throws JobSchedulerException.
   */
  public function info() {
    // Collect info for all schedules once.
    static $info;
    if (!$info) {
      $info = module_invoke_all('cron_job_scheduler_info');
      drupal_alter('cron_job_scheduler_info', $info);
    }
    if (isset($info[$this->name])) {
      return $info[$this->name];
    }
    throw new JobSchedulerException('Could not find Job Scheduler cron information for '. check_plain($this->name));
  }

  /**
   * Add a job to the schedule, replace any existing job.
   *
   * A job is uniquely identified by $job = array(type, id).
   *
   * @param $job
   *   An array that must contain the following keys:
   *   'type'     - A string identifier of the type of job.
   *   'id'       - A numeric identifier of the job.
   *   'period'   - The time when the task should be executed.
   *   'periodic' - True if the task should be repeated periodically.
   *
   * @code
   * function worker_callback($job) {
   *   // Work off job.
   *   // Set next time to be called. If this portion of the code is not
   *   // reached for some reason, the scheduler will keep periodically invoking
   *   // the callback() with the period value initially specified.
   *   $scheduler->set($job);
   * }
   * @endcode
   */
  public function set($job) {
    $job['name'] = $this->name;
    $job['last'] = REQUEST_TIME;
    $job['next'] = REQUEST_TIME + $job['period'];
    $job['scheduled'] = 0;
    $this->remove($job);
    drupal_write_record('job_schedule', $job);
  }

  /**
   * Reserve a job.
   */
  protected function reserve($job) {
    $job['name'] = $this->name;
    $job['scheduled'] =
    $job['last'] = REQUEST_TIME;
    $job['next'] = $job['period'] + REQUEST_TIME;
    drupal_write_record('job_schedule', $job, array('name', 'type', 'id'));
  }

  /**
   * Remove a job from the schedule, replace any existing job.
   *
   * A job is uniquely identified by $job = array(type, id).
   */
  public function remove($job) {
    db_delete('job_schedule')
      ->condition('name', $this->name)
      ->condition('type', $job['type'])
      ->condition('id', isset($job['id']) ? $job['id'] : 0)
      ->execute();
  }

  /**
   * Remove all jobs for a given type.
   */
  public function removeAll($type) {
    db_delete('job_schedule')
      ->condition('name', $this->name)
      ->condition('type', $type)
      ->execute();
  }

  /**
   * Dispatches a job.
   *
   * Executes a worker callback or if schedule declares a queue name, queues a
   * job for execution.
   *
   * @param $job
   *   A $job array as passed into set() or read from job_schedule table.
   *
   * @throws Exception
   *   Exceptions thrown by code called by this method are passed on.
   */
  public function dispatch($job) {
    $info = $this->info();
    if (!$job['periodic']) {
      $this->remove($job);
    }
    if ($info['queue name']) {
      if (DrupalQueue::get($info['queue name'])->createItem($job)) {
        $this->reserve($job);
      }
    }
    elseif (function_exists($info['worker callback'])) {
      $info['worker callback']($job);
    }
  }
}
