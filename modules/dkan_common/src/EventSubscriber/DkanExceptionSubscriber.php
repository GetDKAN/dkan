<?php

declare(strict_types=1);

namespace Drupal\dkan_common\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Send out reasonable responses to DB exceptions.
 */
final class DkanExceptionSubscriber implements EventSubscriberInterface {

  /**
   * Too many connections error.
   *
   * @see https://dev.mysql.com/doc/mysql-errors/8.4/en/server-error-reference.html#error_er_too_many_user_connections
   */
  protected const int ER_TOO_MANY_USER_CONNECTIONS = 1203;

  /**
   * How many seconds before the user should try again?
   */
  protected const int RETRY_AFTER = 120;

  /**
   * Current route match service, to find out if it's a DKAN API request.
   *
   * @var CurrentRouteMatch
   */
  protected CurrentRouteMatch $currentRouteMatch;

  /**
   * Logger service.
   *
   * @var LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $logger;

  /**
   * Constructor.
   *
   * @param CurrentRouteMatch $currentRouteMatch
   *   Current route match service.
   */
  public function __construct(
    LoggerChannelFactoryInterface $logger,
    CurrentRouteMatch $currentRouteMatch,
  ) {
    $this->logger = $logger;
    $this->currentRouteMatch = $currentRouteMatch;
  }

  /**
   * Handle exceptions from the kernel.
   *
   * We focus on \PDOException.
   */
  public function onKernelException(ExceptionEvent $event): void {
    // Is this a DKAN API request?
    if ($this->currentRouteMatch->getRouteObject()->getDefault('is_dkan_api_route') ?? FALSE) {
      $throwable = $event->getThrowable();
      // Is this an exception we can handle?
      if (
        $throwable instanceof \PDOException &&
        $throwable->getCode() === self::ER_TOO_MANY_USER_CONNECTIONS
      ) {
        // Log the error.
        $this->logger->get('dkan_api')->error(implode(' ', [
          $throwable::class,
          '(code: ' . $throwable->getCode() . ')',
          $throwable->getMessage(),
        ]));
        // Send a response.
        $response = new JsonResponse(
          (object) ['message' => 'Try again later after ' . self::RETRY_AFTER . ' seconds.', 'code' => 503],
          503,
          ['Retry-After' => self::RETRY_AFTER],
        );
        // Setting a response stops propagation.
        $event->setResponse($response);
      }
    }
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::EXCEPTION => ['onKernelException', 60],
    ];
  }

}
