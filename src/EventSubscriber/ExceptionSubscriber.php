<?php

namespace Drupal\error_reporting\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\error_reporting\Utility\ExceptionFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Exception subscriber for catching errors.
 */
class ExceptionSubscriber implements EventSubscriberInterface {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Whether to stop the propagation of the exception event.
   *
   * @var bool
   */
  protected $stopPropagation = FALSE;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs an ExceptionSubscriber object.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config service.
   */
  public function __construct(RendererInterface $renderer, ConfigFactoryInterface $configFactory) {
    $this->renderer = $renderer;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Use a high priority to run before Symfony's default exception listener.
    $events[KernelEvents::EXCEPTION][] = ['onKernelException', 100];
    return $events;
  }

  /**
   * Handles exceptions.
   *
   * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent|\Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
   *   The exception event.
   */
  public function onKernelException($event) {
    // Get the exception based on the event type.
    $exception = $this->getExceptionFromEvent($event);
    $config = $this->configFactory->get('error_reporting.settings')->get('enable_error_reporting');
    $response = new Response();
    if (!$config) {
      // If it's a 404 (NotFoundHttpException), stop the event propagation.
      if ($exception instanceof NotFoundHttpException) {
        // Stop the event propagation.
        $this->stopPropagation = TRUE;
        return;
      }
      // Create a Symfony Response object with a 500 status code.
      $response = new Response('The website encountered an unexpected error. Please try again later.', Response::HTTP_INTERNAL_SERVER_ERROR);

      // Set the response object in the event.
      $event->setResponse($response);

      // Stop the event propagation.
      $this->stopPropagation = TRUE;
      return;
    }

    $errorData = [];
    if (!empty($exception)) {
      $errorData = ExceptionFormatter::formatException($exception);
    }

    $build = [
      '#theme' => 'custom_error_display',
      '#cache' => ['max-age' => 0],
      '#data' => $errorData,
    ];

    // Render the template directly.
    $template = $this->renderer->renderRoot($build);

    // Set the response content with the rendered template.
    $response->setContent($template);

    // Set the HTTP status code to 500.
    $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);

    // Send the response.
    $response->send();

    // Set the stopPropagation property to true.
    $this->stopPropagation = TRUE;
  }

  /**
   * Gets the exception based on the event type.
   *
   * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent|\Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
   *   The exception event.
   *
   * @return \Throwable
   *   The exception.
   */
  protected function getExceptionFromEvent($event) {
    if ($event instanceof GetResponseForExceptionEvent) {
      return $event->getException();
    }
    elseif ($event instanceof ExceptionEvent) {
      return $event->getThrowable();
    }
    else {
      throw new \InvalidArgumentException('Unsupported event type.');
    }
  }

  /**
   * Creates an instance of the class.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   *
   * @return static
   *   The instance of this class.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('config.factory')
    );
  }

}
