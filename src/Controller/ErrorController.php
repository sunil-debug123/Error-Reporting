<?php

namespace Drupal\error_reporting\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\error_reporting\Utility\ExceptionFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defines a controller for handling custom errors.
 */
class ErrorController extends ControllerBase {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs an ErrorController object.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function handler(\Throwable $exception) {
    if (!empty($exception)) {
      $errorData = ExceptionFormatter::formatException($exception);

      $build = [
        '#theme' => 'custom_error_display',
        '#cache' => ['max-age' => 0],
        '#data' => $errorData,
      ];

      // Render the output using the renderer service.
      $output = $this->renderer->renderRoot($build);

      /* Create a Symfony Response object and set the status code to Internal Server Error. */
      $response = new Response($output, Response::HTTP_INTERNAL_SERVER_ERROR);
      $response->send();
    }
  }

}
