<?php

namespace DrupalCI\Plugin\BuildTask\BuildStep\CodebaseAssemble;


use DrupalCI\Build\BuildInterface;
use DrupalCI\Console\Output;
use DrupalCI\Injectable;
use DrupalCI\Plugin\BuildTask\BuildStep\BuildStepInterface;
use DrupalCI\Plugin\BuildTask\BuildTaskTrait;
use DrupalCI\Plugin\BuildTask\FileHandlerTrait;
use DrupalCI\Plugin\PluginBase;
use DrupalCI\Plugin\BuildTask\BuildTaskInterface;
use DrupalCI\Build\Codebase\PatchInterface;
use DrupalCI\Build\Codebase\Patch as PatchFile;
use Pimple\Container;

/**
 * @PluginID("patch")
 */
class Patch extends PluginBase implements BuildStepInterface, BuildTaskInterface, Injectable  {

  use BuildTaskTrait;
  use FileHandlerTrait;

  /* @var \DrupalCI\Build\Codebase\CodebaseInterface */
  protected $codebase;

  /* @var \DrupalCI\Build\BuildInterface */
  protected $build;


  public function inject(Container $container) {
    parent::inject($container);
    $this->codebase = $container['codebase'];
    $this->build = $container['build'];

  }

  /**
   * @inheritDoc
   */
  public function configure() {
    // @TODO make into a test
    // $_ENV['DCI_Patch']='https://www.drupal.org/files/issues/2796581-region-136.patch,.;https://www.drupal.org/files/issues/another.patch,.';
    if (isset($_ENV['DCI_Patch'])) {
      $this->configuration['patches'] = $this->process($_ENV['DCI_Patch']);
    }
  }

  /**
   * @inheritDoc
   */
  public function run(BuildInterface $build) {

    $files = $this->configuration['patches'];


    if (empty($files)) {
      $this->io->writeln('No patches to apply.');
    }
    foreach ($files as $key => $details) {
      if (empty($details['from'])) {
        $this->io->drupalCIError("Patch error", "No valid patch file provided for the patch command.");

        return 2;
      }
      // Create a new patch object
      // CODEBASE - adding patchfiles
      $patch = new PatchFile($details, $this->codebase);
      $patch->inject($this->container);
      // Validate our patch's source file and target directory
      if (!$patch->validate()) {

        return 2;
      }

      // Apply the patch
      $result = $patch->apply();
      if ($result !== 0) {

        // Hack to create a xml file for processing by Jenkins.
        // TODO: Remove once proper build failure processing is in place

        // Save an xmlfile to the jenkins artifact directory.
        // find jenkins artifact dir
        //
        // ENVIRONMENT - JunitXML artifact Directory
        $output_directory = $this->build->getXmlDirectory();

        if (!is_dir($output_directory)) {
          mkdir($output_directory, 0777, TRUE);
        }

        $output = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '�', implode("\n", $patch->getPatchApplyResults()));

        $xml_error = '<?xml version="1.0"?>

                      <testsuite errors="1" failures="0" name="Error: Patch failed to apply" tests="1">
                        <testcase classname="Apply Patch" name="' . $patch->getLocalSource() . '">
                          <error message="Patch Failed to apply" type="PatchFailure">Patch failed to apply</error>
                        </testcase>
                        <system-out><![CDATA[' . $output . ']]></system-out>
                      </testsuite>';
        file_put_contents($output_directory . "/patchfailure.xml", $xml_error);

        return $result;
      };
      // Update our list of modified files
      // CODEBASE - Modified Files
      $this->codebase->addModifiedFiles($patch->getModifiedFiles());
    }
  }

  /**
   * @inheritDoc
   */
  public function complete() {
    // TODO: Implement complete() method.
  }

  /**
   * @inheritDoc
   */
  public function getDefaultConfiguration() {
    return [
      'patches' => [],
    ];
  }

  /**
   * @inheritDoc
   */
  public function getChildTasks() {
    // TODO: Implement getChildTasks() method.
  }

  /**
   * @inheritDoc
   */
  public function setChildTasks($buildTasks) {
    // TODO: Implement setChildTasks() method.
  }

  /**
   * @inheritDoc
   */
  public function getShortError() {
    // TODO: Implement getShortError() method.
  }

  /**
   * @inheritDoc
   */
  public function getErrorDetails() {
    // TODO: Implement getErrorDetails() method.
  }

  /**
   * @inheritDoc
   */
  public function getResultCode() {
    // TODO: Implement getResultCode() method.
  }

  /**
   * @inheritDoc
   */
  public function getArtifacts() {
    // TODO: Implement getArtifacts() method.
  }

}