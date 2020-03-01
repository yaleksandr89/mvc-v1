<?php declare(strict_types=1);

namespace Core;

/**
 * Class Router
 *
 * @package Core
 */
class Router
{
    /**
     * @param array $routes
     * @param string $uri
     * @return Track
     */
    public function getTrack(array $routes, string $uri): Track
    {
        foreach ($routes as $route) {
            $pattern = $this->createPattern($route->getPath());
            if (preg_match($pattern, $uri, $params)) {
                $params = $this->clearParams($params);
                return new Track($route->getController(), $route->getAction(), $params);
            }
        }

        return new Track('error', 'notFound');
    }

    /**
     * @param string $path
     * @return string
     */
    private function createPattern(string $path): string
    {
        return '#^' . preg_replace('#/:([^/]+)#', '/(?<$1>[^/]+)', $path) . '/?$#';
    }

    /**
     * @param array|null $params
     * @return array
     */
    private function clearParams(?array $params): array
    {
        $result = [];

        foreach ($params as $key => $param) {
            if (!is_int($key)) {
                $result[$key] = $param;
            }
        }

        return $result;
    }
}