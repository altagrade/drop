<?php

/*
 * @file
 *   Tests for enable, disable, uninstall, pm-list commands.
 */
class VariableCase extends Drop_TestCase {

  function testVariable() {
    $env = 'dev';
    $this->setUpBackdrop($env, TRUE);
    $options = array(
      'yes' => NULL,
      'pipe' => NULL,
      'root' => $this->sites[$env]['root'],
      'uri' => $env,
    );

    $this->drop('variable-set', array('date_default_timezone', 'US/Mountain'), $options);
    $this->drop('variable-get', array('date_default_timezone'), $options); // Wildcard get.
    $var_export = $this->getOutput();
    eval($var_export);
    $this->assertEquals('US/Mountain', $variables['date_default_timezone'], 'Variable was successfully set and get.');
    
    $this->drop('variable-set', array('site_name', 'unish'), $options + array('always-set' => NULL));
    $this->drop('variable-get', array('site_name'), $options);
    $var_export = $this->getOutput();
    eval($var_export);
    $this->assertEquals('unish', $variables['site_name'], '--always-set option works as expected.');

    $this->drop('variable-delete', array('site_name'), $options);
    $output = $this->getOutput();
    $this->assertEmpty($output, 'Variable was successfully deleted.');
  }
}