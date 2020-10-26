<?php

/*
 * @file
 *   Tests for enable, disable, uninstall, pm-list commands.
 */

class EnDisUnListCase extends Drop_TestCase {

  public function testEnDisUnList() {
    $this->setUpBackdrop('dev', TRUE);
    $options = array(
      'yes' => NULL,
      'pipe' => NULL,
      'root' => $this->sites['dev']['root'],
      'uri' => 'dev',
    );
    $this->brush('pm-download', array('devel-1.x-1.0'), $options);
    $this->brush('pm-list', array(), $options + array('no-core' => NULL, 'status' => 'not installed'));
    $list = $this->getOutputAsList();
    $this->assertTrue(in_array('devel', $list));

    $this->brush('pm-enable', array('menu', 'devel'), $options);
    $this->brush('pm-list', array(), $options + array('status' => 'enabled'));
    $list = $this->getOutputAsList();
    $this->assertTrue(in_array('devel', $list));
    $this->assertTrue(in_array('bartik', $list), 'Themes are in the pm-list');

    $this->brush('pm-list', array(), $options + array('package' => 'Core'));
    $list = $this->getOutputAsList();
    $this->assertFalse(in_array('devel', $list), 'Devel is not part of core package');

    // For testing uninstall later.
    $this->brush('variable-set', array('devel_query_display', 1), $options);

    $this->brush('pm-disable', array('devel'), $options);
    $this->brush('pm-list', array(), $options + array('status' => 'disabled'));
    $list = $this->getOutputAsList();
    $this->assertTrue(in_array('devel', $list));

    $this->brush('pm-uninstall', array('devel'), $options);
    $this->brush('pm-list', array(), $options + array('status' => 'not installed', 'type' => 'module'));
    $list = $this->getOutputAsList();
    $this->assertTrue(in_array('devel', $list));


    // We expect an exit code of 1 so just call execute() directly.
    $exec = sprintf('%s variable-get %s --pipe --root=%s --uri=%s', UNISH_BRUSH, 'devel_query_display', $options['root'], $options['uri']);
    $this->execute($exec, self::EXIT_ERROR);
    $output = $this->getOutput();
    $this->assertEmpty($output, 'Devel variable was uninstalled.');
  }
}
