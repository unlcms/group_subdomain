<?php

namespace Drupal\group_subdomain;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Service Provider for Group Subdomain.
 */
class GroupSubdomainServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('router.route_provider');
    $definition->setClass('Drupal\group_subdomain\GroupSubdomainRouteProvider');
  }
}
