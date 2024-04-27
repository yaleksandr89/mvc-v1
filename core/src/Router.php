<?php

namespace Yaa\Framework;

class Router
{
    public function getTrack(array $routes, string $uri): Track
    {
        foreach ($routes as $route) {
            $uriParts = explode('?', $uri, 2);
            $path = $uriParts[0];

            $pattern = $this->createPattern($route->getPath());
            if (preg_match($pattern, $path, $params)) {
                $getParams = [];
                if (isset($uriParts[1])) {
                    parse_str($uriParts[1], $getParams);
                }

                $params = array_merge($params, $getParams);
                $params = $this->clearParams($params);

                return new Track($route->getController(), $route->getAction(), $params);
            }
        }

        return new Track('error', 'notFound');
    }

    private function createPattern(string $path): string
    {
        return '#^' . preg_replace('#/:([^/]+)#', '/(?<$1>[^/]+)', $path) . '/?$#';
    }

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
