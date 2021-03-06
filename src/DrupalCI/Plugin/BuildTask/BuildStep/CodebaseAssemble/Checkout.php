<?php

namespace DrupalCI\Plugin\BuildTask\BuildStep\CodebaseAssemble;

use DrupalCI\Injectable;
use DrupalCI\Plugin\BuildTask\BuildStep\BuildStepInterface;
use DrupalCI\Plugin\BuildTask\FileHandlerTrait;
use DrupalCI\Plugin\BuildTaskBase;
use DrupalCI\Plugin\BuildTask\BuildTaskInterface;
use Pimple\Container;

/**
 * @PluginID("checkout")
 */
class Checkout extends BuildTaskBase implements BuildStepInterface, BuildTaskInterface, Injectable {

  use FileHandlerTrait;
  /* @var \DrupalCI\Build\Codebase\CodebaseInterface */
  protected $codebase;

  public function inject(Container $container) {
    parent::inject($container);
    $this->codebase = $container['codebase'];

  }

  /**
   * @inheritDoc
   */
  public function configure() {
    if (FALSE !== getenv('DCI_Checkout_Repo')) {
      $repo['repo'] = getenv('DCI_Checkout_Repo');

      if (FALSE !== getenv('DCI_Checkout_Branch')) {
        $repo['branch'] = getenv('DCI_Checkout_Branch');
      }
      if (FALSE !== getenv('DCI_Checkout_Hash')) {
        $repo['commit_hash'] = getenv('DCI_Checkout_Hash');
      }
      $this->configuration['repositories'][0] = $repo;
    }

  }

  /**
   * @inheritDoc
   */
  public function run() {
    $this->io->writeln("<info>Checking out git repos.</info>");
    foreach ($this->configuration['repositories'] as $repository ) {
      $this->io->writeln("<info>Entering setup_checkout_git().</info>");
      // @TODO: these should always have a default. no sense in setting them here.
      $repo = $repository['repo'];
      $git_branch = isset($repository['branch']) ? "-b " . $repository['branch'] : '';
      $git_depth = '';
      if (empty($repository['commit_hash'])) {
        $git_depth = '--depth 1';
      }
      $directory = isset($repository['checkout_dir']) ? $repository['checkout_dir'] : $this->codebase->getSourceDirectory();

      $this->io->writeln("<comment>Performing git checkout of $repo $git_branch to $directory.</comment>");

      $cmd = "git clone $git_branch $git_depth $repo '$directory'";
      $this->io->writeln("Git Command: $cmd");
      $this->execRequiredCommand($cmd, 'Checkout Error');

      if (!empty($repository['commit_hash'])) {
        $cmd = "cd " . $directory . " && git reset -q --hard " . $repository['commit_hash'] . " ";
        $this->io->writeln("Git Command: $cmd");
        $this->execRequiredCommand($cmd, 'git reset returned an error.');
      }

      $cmd = "cd '$directory' && git log --oneline -n 1 --decorate";
      $this->exec($cmd, $cmdoutput, $result);
      $this->io->writeln("<comment>Git commit info:</comment>");
      $this->io->writeln("<comment>\t" . implode("\n", $cmdoutput));

      $this->io->writeln("<comment>Checkout complete.</comment>");
    }
    return 0;
  }

  /**
   * @inheritDoc
   */
  public function getDefaultConfiguration() {
    return [
      'repositories' => [],
    ];

  }

}
