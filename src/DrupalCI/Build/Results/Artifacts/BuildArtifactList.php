<?php

/**
 * @file
 * Contains \DrupalCI\Build\Results\Artifacts\BuildArtifactList.
 */

namespace DrupalCI\Build\Results\Artifacts;

/**
 * Class BuildArtifactList
 * @package DrupalCI\Build\Results\Artifacts
 *
 * Contains a list of Build Artifacts relevant for a given build.
 */
class BuildArtifactList {

  /**
   * array of \DrupalCI\Build\Results\Artifacts\BuildArtifact objects
   */
  protected $artifacts = array();

  public function addArtifact($key, $artifact) {
      $this->artifacts[$key] = $artifact;
  }

  public function removeArtifact($key) {
    if (isset($this->artifacts[$key])) {
      unset($this->artifacts[$key]);
    }
  }

  public function getArtifact($key) {
    if (isset($this->artifacts[$key])) {
      return $this->artifacts[$key];
    }
    else {
      // TODO: Error Handling
    }
  }

  public function getArtifacts() {
    return $this->artifacts;
  }

  public function getKeys() {
    return array_keys($this->artifacts);
  }

  public function length() {
    return count($this->artifacts);
  }

  public function keyExists($key) {
    return isset($this->artifacts[$key]);
  }

}
