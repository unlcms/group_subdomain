<?php

namespace Drupal\og_subdomain\PathProcessor;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Site\Settings;

use Symfony\Component\HttpFoundation\Request;

/**
 * Processes the path using by using subdomains attached to organic groups.
 */
class OgSubdomainPathProcessor implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  protected $entityTypeManager;

  protected $settings;

  public function __construct(Settings $settings, EntityTypeManagerInterface $entity_type_manager) {
    $this->settings = $settings;
    $this->entityTypeManager = $entity_type_manager;
  }

  private function isPathSubbable($path) {
      $invalid_starting_paths = array(
          '/system/404',
          '/admin',
          '/node',
          '/user',
          '/devel',
          '/tips',
          '/tree',
          '/token'
      );

      foreach($invalid_starting_paths as $invalid_path) {
          if (strpos($path, $invalid_path) === 0){
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

          // if there is a subdomain
          if (!empty($subdomain) && $subdomain != 'www') {
              // stick the subdomain in front of the path
              $path = '/' . $subdomain . $path;
          }
      }
      return $path;
  }

  public function processOutbound($path, &$options = array(), Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
      // strip the subdomain from the front of the path, if it is there.
      if ($this->isPathSubbable($path)) {
          // remove the subdomain from the front of the path
          $array = explode('/', $path);
          array_shift($array);
          array_shift($array);
          $path = '/' . implode('/', $array);
      }
      return $path;
  }

}
