<?php

/**
  * @file
  *   Prepare a codebase and upgrade it in several stages, exercising
  *   updatecode's filters.
  *   @todo test security-only once one of these modules or core gets a security release.
  */

class pmUpdateCode extends Drop_TestCase {

  /*
   * Download old core and older contrib releases which will always need updating.
   */
  public function setUp() {
    $this->setUpBackdrop('dev', TRUE, '1.x');
    $options = array(
      'root' => $this->sites['dev']['root'],
      'uri' => 'dev',
      'yes' => NULL,
      'quiet' => NULL,
    );
    $this->brush('pm-download', array('devel-1.x-1.0,webform-1.x-1.x'), $options);
    $this->brush('pm-enable', array('menu', 'devel', 'webform'), $options);
  }

  function testUpdateCode() {
    $options = array(
      'root' => $this->sites['dev']['root'],
      'uri' => 'dev',
      'yes' => NULL,
      'backup-dir' => UNISH_SANDBOX . '/backups',
      'self-update' => 0, // Don't try update Brush.
    );

    // Try to upgrade a specific module.
    $this->brush('pm-updatecode', array('devel'), $options + array());
    // Assure that devel was upgraded and webform was not.
    $this->brush('pm-updatecode', array(), $options + array('pipe' => NULL));
    $all = $this->getOutput();
    $this->assertNotContains('devel', $all);
    $this->assertContains('webform', $all);

    // Lock webform, and update core.
    $this->brush('pm-updatecode', array(), $options + array('lock' => 'webform'));
    $list = $this->getOutputAsList(); // For debugging.
    $this->brush('pm-updatecode', array(), $options + array('pipe' => NULL));
    $all = $this->getOutput();
    $this->assertNotContains('backdrop', $all, 'Core was updated');
    $this->assertContains('webform', $all, 'Webform was skipped.');

    // Unlock webform, update, and check.
    $this->brush('pm-updatecode', array(), $options + array('unlock' => 'webform', 'no-backup' => NULL));
    $list = $this->getOutputAsList();
    $this->brush('pm-updatecode', array(), $options + array('pipe' => NULL));
    $all = $this->getOutput();
    $this->assertNotContains('webform', $all, 'Webform was updated');

    // Verify that we keep backups as instructed.
    $pattern = 'find %s -iname %s';
    $backup_dir = UNISH_SANDBOX . '/backups';
    $cmd = sprintf($pattern, self::escapeshellarg($backup_dir), escapeshellarg('devel.module'));
    $this->execute($cmd);
    $output = $this->getOutput();
    $this->assertNotEmpty($output);

    $cmd = sprintf($pattern, self::escapeshellarg($backup_dir), escapeshellarg('webform.module'));
    $this->execute($cmd);
    $output = $this->getOutput();
    $this->assertEmpty($output);
  }
}
