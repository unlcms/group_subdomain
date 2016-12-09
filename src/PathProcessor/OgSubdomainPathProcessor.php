<?php

namespace Drupal\og_subdomain\PathProcessor;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Site\Settings;
use Drupal\og\Og;
use Drupal\Core\Entity\Entity;
use Drupal\node\Entity\Node;

use Symfony\Component\HttpFoundation\Request;

/**
 * Processes the inbound path using path alias lookups.
 */
class OgSubdomainPathProcessor implements InboundPathProcessorInterface {

  /**
   * To list all entity types.
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * To get the base url from settings.php
   * @var Settings
   */
  protected $settings;

  protected $query;

  /**
   * Constructs a PathProcessorAlias object.
   *
   * @param \Drupal\Core\Path\AliasManagerInterface $settings
   *   Settings
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *    Entity storage for looking up the subdomain records on particular entities.
   */
  public function __construct(Settings $settings, EntityTypeManagerInterface $entity_type_manager) {
    $this->settings = $settings;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * If the request includes a subdomain, change the path
   */
  public function processInbound($path, Request $request) {
      $host = $request->getHttpHost();    

      $subdomain = explode('.', $host)[0];

      // if there is a subdomain
      if (!empty($subdomain) && $subdomain != 'www') {
          // stick the subdomain in front of the path
          $path = '/' . $subdomain . $path;
      }

      return $path;
  }

}
