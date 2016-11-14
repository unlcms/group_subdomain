<?php

namespace Drupal\og_subdomain\PathProcessor;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;

/**
 * Processes the inbound path using path alias lookups.
 */
class OgSubdomainPathProcessor implements OutboundPathProcessorInterface, InboundPathProcessorInterface {

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

  /**
   * Constructs a PathProcessorAlias object.
   *
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   An alias manager for looking up the system path.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *    Entity storage for looking up the subdomain records on particular entities.
   */
  public function __construct(Settings $settings, EntityTypeManagerInterface $entity_type_manager) {
    $this->settings = $settings;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * If the request includes a subdomain, set the path accordingly.
   */
  public function processInbound($path, Request $request) {
    $base_host = $this->settings->get('subdomain_base_url');
    $host = $request->getHost();
    $subdomain = substr($host, 0, ((strlen($base_host) + 1) * -1));

    // If we have a subdomain, find the entity with which it is associated and update path.
    if ($subdomain) {
      // Get all entity types
      $entity_types = $this->entityTypeManager->getDefinitions();
      // For each content entity type...
//      foreach ($this->subdomainInfo->selectContentEntities($entity_types) as $type_name => $type) {
//        // If it has a subdomain handler...
//        if ($type->hasHandlerClass('subdomain')) {
//          // Get the handler and pass the path and request to it.
//          $subdomain_handler = $this->entityTypeManager->getHandler($type->id(), 'subdomain');
//          $subdomain_handler->processInbound($path, $subdomain);
//        }
//      }
    }
    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = array(), Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    /*if (empty($options['alias'])) {
      $langcode = isset($options['language']) ? $options['language']->getId() : NULL;
      $path = $this->aliasManager->getAliasByPath($path, $langcode);
    }*/
    return $path;
  }

}
