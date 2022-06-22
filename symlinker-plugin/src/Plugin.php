<?php

namespace Dkan\Composer\Plugin\Symlinker;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * Composer plugin for handling drupal scaffold.
 *
 * @internal
 */
class Plugin implements PluginInterface, EventSubscriberInterface, Capable {

  /**
   * The Composer service.
   *
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * Composer's I/O service.
   *
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * The Composer Scaffold handler.
   *
   * @var \Drupal\Composer\Plugin\Scaffold\Handler
   */
  protected $handler;

  protected $hasPerformedSymlink;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io) {
    $this->composer = $composer;
    $this->io = $io;
    $this->hasPerformedSymlink = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function deactivate(Composer $composer, IOInterface $io) {
  }

  /**
   * {@inheritdoc}
   */
  public function uninstall(Composer $composer, IOInterface $io) {
  }

  /**
   * {@inheritdoc}
   */
  public function getCapabilities() {
    return [CommandProvider::class => SymlinkerCommandProvider::class];
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // @todo: Check whether the symlinking happened on status.
    return [
      ScriptEvents::PRE_UPDATE_CMD => 'prePostCmd',
      ScriptEvents::PRE_INSTALL_CMD => 'prePostCmd',
      ScriptEvents::POST_INSTALL_CMD => 'prePostCmd',
      ScriptEvents::POST_UPDATE_CMD => 'prePostCmd',
    ];
  }

  /**
   * Post command event callback.
   *
   * @param \Composer\Script\Event $event
   *   The Composer event.
   */
  public function prePostCmd(Event $event) {
    $this->handler()->makesymlinks();
  }

  /**
   * Lazy-instantiate the handler object. It is dangerous to update a Composer
   * plugin if it loads any classes prior to the `composer update` operation,
   * and later tries to use them in a post-update hook.
   */
  protected function handler() {
    if (!$this->handler) {
      $this->handler = new Handler($this->composer, $this->io);
    }
    return $this->handler;
  }

}
