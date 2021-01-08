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
    // TODO: this needs to be converted to examine if the internal route is a designated internal Drupal route.
    $invalid_starting_paths = array(
      // These paths have pages that exist without the trailing slash.
      '/admin',
      '/batch',
      '/filter/tips',
      '/imce',
      '/node',
      '/user',
      // These system paths only exist with a trailing slash and additional
      // params. Add a trailing slash to these so that a user can create content
      // that lives at a path such as '/history' for a webpage about history.
      '/group/',
      '/cron/',
      '/system/',
      '/devel/',
      '/tips/',
      '/tree/',
      '/token/',
      '/editor/',
      '/themes/',
      '/quickedit/',
      '/contextual/',
      '/history/',
      '/core/',
      '/toolbar/',
      '/entity_reference_autocomplete/',
      '/sites/default/files/',
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
    global $base_url;
    
    // strip the subdomain from the front of the path, if it is there.
    if ($this->isPathSubbable($path)) {
      // remove the subdomain from the front of the path, if it matches a known subdomain
      $array = explode('/', $path);

      $group_path = \Drupal::service('path.alias_manager')->getPathByAlias('/' . $array[1]);
      if (preg_match('/group\/(\d+)/', $group_path, $matches)) {
        $group = Group::load($matches[1]);
      }

      //Check if we need to do special sub-domain magic
      if (count($array) >= 2 && isset($group) && $group->get('field_use_subdomain')->getValue()[0]['value']) {
        //Trim group path from normal path, because it will be used as the subdomain instead
        array_shift($array);
        array_shift($array);
        $path = '/' . implode('/', $array);

        //force absolute because this is a subdomain
        $options['absolute'] = true;

        // replace the subdomain in the base URL with this new one
        $subdomain = \Drupal::service('path.alias_manager')->getAliasByPath('/group/' . $group->id());
        $split_base_url = explode('.', $base_url);
        $split_start = explode('/', $split_base_url[0]);
        $split_start[count($split_start) - 1] = ltrim($subdomain, '/');
        $split_base_url[0] = implode('/', $split_start);
        $new_base_url = implode('.', $split_base_url);
        
        //Set the new base url in the options
        $options['base_url'] = $new_base_url;
      }
    } else {
      // Use Subdomain is off - Need to switch domain to the main one
      // replace the subdomain in the base URL with the main subdomain
      $config = \Drupal::config('group_subdomain.settings');
      $parts = parse_url($base_url);
      $default_base_url = $parts['scheme'] . '://' . $config->get('base_host') . $parts['path'];
      $options['base_url'] = $default_base_url;
      $options['absolute'] = true;
    }
    
    return $path;
  }

}
