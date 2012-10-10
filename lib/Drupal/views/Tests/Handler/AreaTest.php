<?php

/**
 * @file
 * Definition of Drupal\views\Tests\Handler\AreaTest.
 */

namespace Drupal\views\Tests\Handler;

/**
 * Tests the abstract area handler.
 *
 * @see Drupal\views\Plugin\views\area\AreaPluginBase
 * @see Drupal\views_test\Plugin\views\area\TestExample
 */
class AreaTest extends HandlerTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('views_ui');

  public static function getInfo() {
    return array(
      'name' => 'Area: Base',
      'description' => 'Test the plugin base of the area handler.',
      'group' => 'Views Handlers',
    );
  }

  protected function setUp() {
    parent::setUp();

    $this->enableViewsTestModule();
  }

  protected function viewsData() {
    $data = parent::viewsData();
    $data['views']['test_example'] = array(
      'title' => 'Test Example area',
      'help' => 'A area handler which just exists for tests.',
      'area' => array(
        'id' => 'test_example'
      )
    );

    return $data;
  }


  /**
   * Tests the generic UI of a area handler.
   */
  public function testUI() {
    $admin_user = $this->drupalCreateUser(array('administer views', 'administer site configuration'));
    $this->drupalLogin($admin_user);

    $types = array('header', 'footer', 'empty');
    $labels = array();
    foreach ($types as $type) {
      $edit_path = 'admin/structure/views/nojs/config-item/test_example_area/default/' . $type .'/test_example';

      // First setup an empty label.
      $this->drupalPost($edit_path, array(), t('Apply'));
      $this->assertText('Test Example area');

      // Then setup a no empty label.
      $labels[$type] = $this->randomName();
      $this->drupalPost($edit_path, array('options[label]' => $labels[$type]), t('Apply'));
      // Make sure that the new label appears on the site.
      $this->assertText($labels[$type]);

      // Test that the settings (empty/label) are accessible.
      $this->drupalGet($edit_path);
      $this->assertField('options[label]');
      if ($type !== 'empty') {
        $this->assertField('options[empty]');
      }
    }
  }

  /**
   * Tests the rendering of an area.
   */
  public function testRenderArea() {
    $view = views_get_view('test_example_area');
    $view->initHandlers();

    // Insert a random string to the test area plugin and see whether it is
    // rendered for both header, footer and empty text.
    $header_string = $this->randomString();
    $footer_string = $this->randomString();
    $empty_string = $this->randomString();
    $view->header['test_example']->options['string'] = $header_string;
    $view->footer['test_example']->options['string'] = $footer_string;
    $view->empty['test_example']->options['string'] = $empty_string;

    // Check whether the strings exists in the output.
    $output = $view->preview();
    $this->assertTrue(strpos($output, $header_string) !== FALSE);
    $this->assertTrue(strpos($output, $footer_string) !== FALSE);
    $this->assertTrue(strpos($output, $empty_string) !== FALSE);
  }

  /**
   * Tests overriding the view title using the area title handler.
   */
  public function testTitleArea() {
    $view = views_get_view('frontpage');
    $view->initDisplay('page_1');

    // Add the title area handler to the empty area.
    $view->displayHandlers['page_1']->overrideOption('empty', array(
      'title' => array(
        'id' => 'title',
        'table' => 'views',
        'field' => 'title',
        'admin_label' => '',
        'label' => '',
        'empty' => '0',
        'title' => 'Overridden title',
      ),
    ));

    $view->storage->enable();

    $this->drupalGet('frontpage');
    $this->assertText('Overridden title', 'Overridden title found.');
  }

}
