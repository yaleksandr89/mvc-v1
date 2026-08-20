<?php
declare(strict_types=1);

namespace Yaa\Framework;

class Router
{
    /**
     * @param list<Route> $routes
     */
    public function getTrack(array $routes, string $uri): Track
    {
        $path = explode('?', $uri, 2)[0];

        foreach ($routes as $route) {
            $pattern = $this->createPattern($route->getPath());
            if (preg_match($pattern, $path, $params) === 1) {
                $params = $this->clearParams($params);

                return new Track($route->getController(), $route->getAction(), $params);
            }
        }

        return new Track('error', 'notFound');
    }

    private function createPattern(string $path): string
    {
        if ($path === '/') {
            return '#^/$#';
        }

        $segments = explode('/', $path);
        foreach ($segments as &$segment) {
            if (preg_match('/^:([A-Za-z_][A-Za-z0-9_]*)$/D', $segment, $matches) === 1) {
                $segment = '(?<' . $matches[1] . '>[^/]+)';
                continue;
            }

            $segment = preg_quote($segment, '#');
        }
        unset($segment);

        return '#^' . implode('/', $segments) . '/?$#';
    }

    /**
     * @param array<array-key, string> $params
     *
     * @return array<string, string>
     */
    private function clearParams(array $params): array
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
