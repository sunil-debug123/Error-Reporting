<?php

namespace Drupal\Tests\error_reporting\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the module installs correctly.
 *
 * @group error_reporting
 */
class ModuleInstallationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['error_reporting'];

  /**
   * Make sure the site still works. For now just check the front page.
   */
  public function testTheSiteStillWorks() {
    // Load the front page.
    $this->drupalGet('<front>');

    // Confirm that the site didn't throw a server error or something else.
    $this->assertSession()->statusCodeEquals(200);
  }

}
