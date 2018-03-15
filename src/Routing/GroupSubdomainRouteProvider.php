<?php

namespace Drupal\group_subdomain\Routing;

use Drupal\Core\Routing\RouteProvider;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Cmf\Component\Routing\PagedRouteCollection;
use Symfony\Cmf\Component\Routing\PagedRouteProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouteCollection;
use \Drupal\Core\Database\Connection;

class GroupSubdomainRouteProvider extends RouteProvider {

  /**
   * Parent creates the $cid cache key before the path has had the group alias
   * prepended with GroupSubdomainPathProcessor:processInbound(). This leads to
   * cache collisions if two subdomains/sites share a page with the same path
   * alias.
   */
  public function getRouteCollectionForRequest(Request $request) {
    // Cache both the system path as well as route parameters and matching
    // routes.

    $host = $request->getHttpHost();
    $subdomain = explode('.', $host)[0];
    $group_alias = !empty($subdomain) ? '/' . $subdomain : '';

    $cid = 'route:' . $group_alias . $request->getPathInfo() . ':' . $request->getQueryString();
    if ($cached = $this->cache->get($cid)) {
      $this->currentPath->setPath($cached->data['path'], $request);
      $request->query->replace($cached->data['query']);
      return $cached->data['routes'];
    }
    else {
      // Just trim on the right side.
      $path = $request->getPathInfo();
      $path = $path === '/' ? $path : rtrim($request->getPathInfo(), '/');
      $path = $this->pathProcessor->processInbound($path, $request);
      $this->currentPath->setPath($path, $request);
      // Incoming path processors may also set query parameters.
      $query_parameters = $request->query->all();
      $routes = $this->getRoutesByPath(rtrim($path, '/'));
      $cache_value = [
        'path' => $path,
        'query' => $query_parameters,
        'routes' => $routes,
      ];
      $this->cache->set($cid, $cache_value, CacheBackendInterface::CACHE_PERMANENT, ['route_match']);
      return $routes;
    }
  }

}
