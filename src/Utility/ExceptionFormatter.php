<?php

namespace Drupal\error_reporting\Utility;

/**
 * Provides a utility for formatting exceptions.
 */
class ExceptionFormatter {

  /**
   * Check the type of exception.
   *
   * @param \Throwable $exception
   *   The exception.
   *
   * @return string
   *   The type of exception.
   */
  private static function getExceptionType(\Throwable $exception) {
    $errorCode = get_class($exception);

    switch ($errorCode) {
      case E_PARSE:
      case E_ERROR:
      case E_CORE_ERROR:
      case E_COMPILE_ERROR:
      case E_USER_ERROR:
        return "Fatal Error";

      case E_WARNING:
      case E_USER_WARNING:
      case E_COMPILE_WARNING:
      case E_RECOVERABLE_ERROR:
        return "Warning";

      case E_NOTICE:
      case E_USER_NOTICE:
      case E_STRICT:
        return "Notice";

      case E_DEPRECATED:
      case E_USER_DEPRECATED:
        return "Deprecated";

      default:
        return "ERROR";
    }
  }

  /**
   * Format the exception to include line and file contents.
   *
   * @param \Throwable $exception
   *   The exception.
   *
   * @return array
   *   An array containing formatted error data.
   */
  public static function formatException(\Throwable $exception) {
    $message = $exception->getMessage() ?? "";
    $line = $exception->getLine() ?? "";
    $file = $exception->getFile() ?? "";
    $trace = $exception->getTrace() ?? [];
    $exceptionType = self::getExceptionType($exception);

    $fileContents = [];
    $fileLines = [];

    if (is_readable($file) && file_exists($file)) {
      // Read the contents of the main file.
      $fileContents = file_get_contents($file);
      // Split the file contents into an array of lines.
      $fileLines = explode("\n", $fileContents);
    }

    // Extract the lines around the error line.
    $content = self::getFormattedLines($fileLines, $line);

    $formattedTrace = [];

    // Add the main error to the same array.
    $formattedTrace[] = [
      'file' => $file,
      'line' => $line,
      'function' => $exceptionType,
      'content' => $content,
    ];

    foreach ($trace as $traceItem) {
      // Check if the trace item has a 'file' key.
      if (isset($traceItem['file']) && is_readable($traceItem['file']) && file_exists($traceItem['file'])) {
        // Read the contents of the file for this trace.
        $traceFileContents = file_get_contents($traceItem['file']);

        // Split the file contents into an array of lines.
        $traceFileLines = explode("\n", $traceFileContents);

        // Extract the lines around the line number in this trace.
        $traceContent = self::getFormattedLines($traceFileLines, $traceItem['line']);

        // Add the formatted trace item to the array.
        $formattedTrace[] = [
          'file' => $traceItem['file'],
          'line' => $traceItem['line'],
          'function' => $traceItem['function'],
          'content' => $traceContent,
        ];
      }
    }

    $requestStack = \Drupal::service('request_stack');
    $currentRequest = $requestStack->getCurrentRequest();
    $extensionPath = \Drupal::service('extension.list.module')->getPath('error_reporting');
    $cssPath = '/' . $extensionPath . '/libraries/css/error_reporting.css';
    $jsPath = '/' . $extensionPath . '/libraries/js/error_reporting.js';

    $errorData = [
      'message' => $message,
      'exception_type' => $exceptionType,
      'trace' => $formattedTrace,
      'server' => $currentRequest->server->all(),
      'session' => $currentRequest->hasSession() ? $currentRequest->getSession()->all() : [],
      'request' => $currentRequest->request->all(),
      'cookie' => $currentRequest->cookies->all(),
      'css' => $cssPath,
      'js' => $jsPath,
    ];

    return $errorData;
  }

  /**
   * Get formatted lines with line numbers.
   *
   * @param array $lines
   *   The array of lines.
   * @param int $errorLine
   *   The line number of the error.
   *
   * @return string
   *   The formatted lines.
   */
  private static function getFormattedLines(array $lines, $errorLine) {
    $visibleLinesStart = 17;
    $visibleLinesEnd = 17;

    // Calculate the starting index based on the error line.
    $start = max(0, $errorLine - $visibleLinesStart - 1);

    // Calculate the number of lines to include.
    $count = $visibleLinesStart + $visibleLinesEnd;

    $formattedLines = [];

    for ($i = $start; $i < min(count($lines), $start + $count); $i++) {
      $encode_text = str_replace("\n", "", $lines[$i]);
      $encode_text = str_replace("<", "&lt;", $encode_text);
      // Format line with line number and HTML entities.
      $formattedLines[] = ($i + 1) . '   ' . $encode_text;
    }

    return implode("\n", $formattedLines);
  }

}
