<?php

namespace Drupal\Tests\error_reporting\Functional;

use Drupal\error_reporting\Utility\ExceptionFormatter;
use Drupal\Tests\BrowserTestBase;

/**
 * Test the Error get Formatted Correctly.
 *
 * @group error_reporting
 */
class FormatterTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['error_reporting'];

  /**
   * Test exception message formatting.
   *
   * Verifies that the formatted output contains the original exception message
   * and includes the 'Stacktrace' label.
   *
   * @throws \Exception
   *   If an error occurs during the test.
   */
  public function testExceptionFormatting() {
    // Sample exception message.
    $msg = 'Sample exception message';

    // Format the exception using the `Formatter` class.
    $exception = new \Exception($msg);
    $output = ExceptionFormatter::formatException($exception);

    // Assert that the formatted output contains the original exception message.
    $this->assertContains($msg, $output['message']);

    // Assert that the Exception is get Properly Formatted.
    $this->assertArrayHasKey('exception_type', $output);
    $this->assertArrayHasKey('trace', $output);
    $this->assertArrayHasKey('server', $output);
    $this->assertArrayHasKey('session', $output);
    $this->assertArrayHasKey('request', $output);
    $this->assertArrayHasKey('cookie', $output);
  }

}
