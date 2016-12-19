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

  protected $known_subdomains;

  public function __construct(Settings $settings, EntityTypeManagerInterface $entity_type_manager) {
    $this->settings = $settings;
    $this->entityTypeManager = $entity_type_manager;

    // get the known subdomains
    $query = \Drupal::database()->select('node_field_data', 'nfd');
    $query->addField('nfd', 'og_subdomain');
    $query->distinct();
    $rows = $query->execute()->fetchAll();
    $this->known_subdomains = array();
    foreach ($rows as $row) {
        if (!empty($row->og_subdomain)) {
            $this->known_subdomains[] = $row->og_subdomain;
        }
    }
  }

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
              if (in_array($subdomain, $this->known_subdomains)) {
                  // stick the subdomain in front of the path
                  $path = '/' . $subdomain . $path;
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
          if (count($array) >= 2 && in_array($array[1], $this->known_subdomains)) {
              array_shift($array);
              array_shift($array);
              $path = '/' . implode('/', $array);
          }
      }
      return $path;
  }

}
