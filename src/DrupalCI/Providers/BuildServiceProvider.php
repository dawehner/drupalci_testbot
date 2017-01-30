<?php

namespace DrupalCI\Providers;

use DrupalCI\Build\Build;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class BuildServiceProvider implements ServiceProviderInterface {
  /**
   * @inheritDoc
   */
  public function register(Container $container) {
    $container['build'] = function ($container) {
      $build = new Build();
      $build->inject($container);
      return $build;
    };
  }

}
