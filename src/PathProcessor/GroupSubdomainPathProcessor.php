<?php

namespace Drupal\group_subdomain\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\group\Entity\Group;

use Symfony\Component\HttpFoundation\Request;

/**
 * Processes the path using by using subdomains attached to groups.
 */
class GroupSubdomainPathProcessor implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  private function isPathSubbable($path) {
    $invalid_starting_paths = array(
      '/system',
      '/admin',
      '/node',
      '/user',
      '/devel',
      '/tips',
      '/tree',
      '/token',
      '/editor',
      '/themes',
      '/quickedit',
      '/contextual',
      '/history',
      '/core',
      '/toolbar',
      '/entity_reference_autocomplete'
    );

    foreach ($invalid_starting_paths as $invalid_path) {
      if (strpos($path, $invalid_path) === 0) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * If the request includes a subdomain, change the path
   */
  public function processInbound($path, Request $request) {
    if ($this->isPathSubbable($path)) {
      $host = $request->getHttpHost();
      $subdomain = explode('.', $host)[0];

      $config = \Drupal::config('group_subdomain.settings');

      // if there is a subdomain
      if (!empty($subdomain) && $host !== $config->get('base_host')) {
        $group_path = \Drupal::service('path.alias_manager')->getPathByAlias('/' . $subdomain);
        if (preg_match('/group\/(\d+)/', $group_path, $matches)) {
          $group = Group::load($matches[1]);
        }

        if (isset($group)) {
          // stick the subdomain in front of the path
          $path = '/' . $subdomain . $path;
          $path = rtrim($path, '/');
        }
      }
    }
    return $path;
  }

  public function processOutbound($path, &$options = array(), Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    // strip the subdomain from the front of the path, if it is there.
    if ($this->isPathSubbable($path)) {
      // remove the subdomain from the front of the path, if it matches a known subdomain
      $array = explode('/', $path);

      $group_path = \Drupal::service('path.alias_manager')->getPathByAlias('/' . $array[1]);
      if (preg_match('/group\/(\d+)/', $group_path, $matches)) {
        $group = Group::load($matches[1]);
      }

      if (count($array) >= 2 && isset($group) && $group->get('field_use_subdomain')->getValue()[0]['value']) {
        array_shift($array);
        array_shift($array);
        $path = '/' . implode('/', $array);
      }
    }
    return $path;
  }

}
