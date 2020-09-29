<?php

namespace Oro\Bundle\RedirectBundle\Api\Repository;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\RedirectBundle\Api\Model\Route;
use Oro\Bundle\RedirectBundle\Routing\SlugRedirectMatcher;
use Oro\Component\Routing\UrlMatcherUtil;
use Oro\Component\Routing\UrlUtil;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * The repository to get a storefront route.
 */
class RouteRepository
{
    private const ATTR_ROUTE_NAME           = '_route';
    private const ATTR_ROUTE_PARAMS         = '_route_params';
    private const ATTR_PATH                 = 'path';
    private const ATTR_URL                  = '_url';
    private const ATTR_REDIRECT_URL         = '_redirect_url';
    private const ATTR_REDIRECT_STATUS_CODE = '_redirect_status_code';
    private const ATTR_USED_SLUG            = '_used_slug';

    private const SYSTEM_ATTRIBUTES = ['path', 'permanent', 'scheme', 'httpPort', 'httpsPort'];

    /** @var FrontendHelper */
    private $frontendHelper;

    /** @var UrlMatcherInterface */
    private $urlMatcher;

    /** @var SlugRedirectMatcher */
    private $redirectMatcher;

    /**
     * @param FrontendHelper      $frontendHelper
     * @param UrlMatcherInterface $urlMatcher
     * @param SlugRedirectMatcher $redirectMatcher
     */
    public function __construct(
        FrontendHelper $frontendHelper,
        UrlMatcherInterface $urlMatcher,
        SlugRedirectMatcher $redirectMatcher
    ) {
        $this->urlMatcher = $urlMatcher;
        $this->frontendHelper = $frontendHelper;
        $this->redirectMatcher = $redirectMatcher;
    }

    /**
     * Gets a route by its relative path.
     *
     * @param string $id
     *
     * @return Route|null
     */
    public function findRoute(string $id): ?Route
    {
        $url = str_replace(':', '/', $id);
        if (strpos($url, '/') !== 0) {
            return null;
        }

        $baseUrl = $this->urlMatcher->getContext()->getBaseUrl();
        if (UrlUtil::getAbsolutePath($url, $baseUrl) !== $url) {
            return null;
        }

        $pathInfo = UrlUtil::getPathInfo($url, $baseUrl);
        if (!$this->frontendHelper->isFrontendUrl($pathInfo)) {
            return null;
        }

        try {
            return $this->createRoute($id, $url, $this->matchUrl($pathInfo, $baseUrl));
        } catch (ResourceNotFoundException $e) {
            $attributes = $this->matchRedirect($pathInfo, $baseUrl);

            return $attributes
                ? $this->createRoute($id, $url, $attributes)
                : null;
        } catch (RoutingException $e) {
            // nothing to do here
        }

        return null;
    }

    /**
     * @param string $pathInfo
     * @param string $baseUrl
     *
     * @return array
     *
     * @throws RoutingException if the given path cannot be matched
     */
    private function matchUrl(string $pathInfo, string $baseUrl): array
    {
        $attributes = UrlMatcherUtil::matchForGetMethod($pathInfo, $this->urlMatcher);
        if (isset($attributes[self::ATTR_PATH])) {
            $attributes[self::ATTR_URL] = UrlUtil::getAbsolutePath($attributes[self::ATTR_PATH], $baseUrl);
        }
        if (!isset($attributes[self::ATTR_ROUTE_PARAMS])) {
            $params = $attributes;
            foreach (self::SYSTEM_ATTRIBUTES as $name) {
                unset($params[$name]);
            }
            foreach (array_keys($attributes) as $name) {
                if (strpos($name, '_') === 0) {
                    unset($params[$name]);
                }
            }
            $attributes[self::ATTR_ROUTE_PARAMS] = $params;
        }

        return $attributes;
    }

    /**
     * @param string $pathInfo
     * @param string $baseUrl
     *
     * @return array|null
     */
    private function matchRedirect(string $pathInfo, string $baseUrl): ?array
    {
        $attributes = null;
        $redirectAttributes = $this->redirectMatcher->match($pathInfo);
        if ($redirectAttributes) {
            $targetPathInfo = $redirectAttributes['pathInfo'];
            try {
                $attributes = $this->matchUrl($targetPathInfo, $baseUrl);
                if ($attributes) {
                    $attributes[self::ATTR_REDIRECT_URL] = UrlUtil::getAbsolutePath($targetPathInfo, $baseUrl);
                    $attributes[self::ATTR_REDIRECT_STATUS_CODE] = $redirectAttributes['statusCode'];
                }
            } catch (RoutingException $e) {
                // nothing to do here
            }
        }

        return $attributes;
    }

    /**
     * @param string $id
     * @param string $url
     * @param array  $attributes
     *
     * @return Route
     */
    private function createRoute(string $id, string $url, array $attributes): Route
    {
        $route = new Route(
            $id,
            $attributes[self::ATTR_URL] ?? $url,
            $attributes[self::ATTR_ROUTE_NAME],
            $attributes[self::ATTR_ROUTE_PARAMS] ?? [],
            isset($attributes[self::ATTR_USED_SLUG])
        );
        if (isset($attributes[self::ATTR_REDIRECT_URL])) {
            $route->setRedirect(
                $attributes[self::ATTR_REDIRECT_URL],
                $attributes[self::ATTR_REDIRECT_STATUS_CODE]
            );
        }

        return $route;
    }
}
