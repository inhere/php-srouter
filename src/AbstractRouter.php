<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/10/17
 * Time: 下午11:37
 */

namespace Inhere\Route;

/**
 * Class AbstractRouter
 * @package Inhere\Route
 */
abstract class AbstractRouter implements RouterInterface
{
    /**
     * validate and format arguments
     * @param string|array $methods
     * @param mixed $handler
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function validateArguments($methods, $handler)
    {
        if (!$methods || !$handler) {
            throw new \InvalidArgumentException('The method and route handler is not allow empty.');
        }

        $allow = implode(',', self::SUPPORTED_METHODS) . ',';

        $methods = array_map(function ($m) use($allow) {
            $m = strtoupper(trim($m));

            if (!$m || false === strpos($allow, $m . ',')) {
                throw new \InvalidArgumentException("The method [$m] is not supported, Allow: $allow");
            }

            return $m;
        }, (array)$methods);

        if (!is_string($handler) && !is_object($handler)) {
            throw new \InvalidArgumentException('The route handler is not empty and type only allow: string,object');
        }

        if (is_object($handler) && !is_callable($handler)) {
            throw new \InvalidArgumentException('The route object handler must be is callable');
        }

        $methods = implode(',', $methods) . ',';

        if (false !== strpos($methods, self::ANY)) {
            return $allow;
        }

        return $methods;
    }

    /**
     * has dynamic param
     * @param string $route
     * @return bool
     */
    protected static function isNoDynamicParam($route)
    {
        return strpos($route, '{') === false && strpos($route, '[') === false;
    }

    /**
     * @param array $matches
     * @param array $conf
     * @return array
     */
    protected static function filterMatches(array $matches, array $conf)
    {
        // clear all int key
        $matches = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        // apply some default param value
        if (isset($conf['option']['default'])) {
            $matches = array_merge($matches, $conf['option']['default']);
        }

        return $matches;
    }

    /**
     * @param string $route
     * @param array $params
     * @param array $conf
     * @return array
     * @throws \LogicException
     */
    public static function parseParamRoute($route, array $params, array $conf)
    {
        $tmp = $route;

        // 解析可选参数位
        // '/hello[/{name}]'      match: /hello/tom   /hello
        // '/my[/{name}[/{age}]]' match: /my/tom/78  /my/tom
        if (false !== strpos($route, ']')) {
            $withoutClosingOptionals = rtrim($route, ']');
            $optionalNum = strlen($route) - strlen($withoutClosingOptionals);

            if ($optionalNum !== substr_count($withoutClosingOptionals, '[')) {
                throw new \LogicException('Optional segments can only occur at the end of a route');
            }

            // '/hello[/{name}]' -> '/hello(?:/{name})?'
            $route = str_replace(['[', ']'], ['(?:', ')?'], $route);
        }

        // 解析参数，替换为对应的 正则
        if (preg_match_all('#\{([a-zA-Z_][a-zA-Z0-9_-]*)\}#', $route, $m)) {
            /** @var array[] $m */
            $replacePairs = [];

            foreach ($m[1] as $name) {
                $key = '{' . $name . '}';
                // 匹配定义的 param  , 未匹配到的使用默认 self::DEFAULT_REGEX
                $regex = isset($params[$name]) ? $params[$name] : self::DEFAULT_REGEX;

                // 将匹配结果命名 (?P<arg1>[^/]+)
                $replacePairs[$key] = '(?P<' . $name . '>' . $regex . ')';
                // $replacePairs[$key] = '(' . $regex . ')';
            }

            $route = strtr($route, $replacePairs);
        }

        // 分析路由字符串是否是有规律的
        $first = null;
        $regex = '#^' . $route . '$#';

        // e.g '/hello[/{name}]' first: 'hello', '/user/{id}' first: 'user', '/a/{post}' first: 'a'
        // first node is a normal string
        // if (preg_match('#^/([\w-]+)#', $tmp, $m)) {
        if (preg_match('#^/([\w-]+)/?[\w-]*#', $tmp, $m)) {
            $first = $m[1];
            $info = [
                'regex'  => $regex,
                'start' => $m[0],
            ];
            // first node contain regex param '/{some}/{some2}/xyz'
        } else {
            $include = null;

            if (preg_match('#/([\w-]+)/?[\w-]*#', $tmp, $m)) {
                $include = $m[0];
            }

            $info = [
                'regex' => $regex,
                'include' => $include,
            ];
        }

        return [$first, array_merge($info, $conf)];
    }

    /**
     * @param array $routes
     * @param string $path
     * @param string $method
     * @return array
     */
    public static function findInStaticRoutes(array $routes, $path, $method)
    {
        $methods = null;

        foreach ($routes as $conf) {
            if (false !== strpos($conf['methods'], $method . ',')) {
                return [self::FOUND, $path, $conf];
            }

            $methods .= $conf['methods'];
        }

        // method not allowed
        return [self::METHOD_NOT_ALLOWED, $path, array_unique(explode(',', trim($methods, ',')))];
    }

    /**
     * @param array $params
     * @param array $tmpParams
     * @return array
     */
    public static function getAvailableParams(array $params, $tmpParams)
    {
        if ($tmpParams) {
            foreach ($tmpParams as $name => $pattern) {
                $key = trim($name, '{}');
                $params[$key] = $pattern;
            }
        }

        return $params;
    }

    /**
     * convert 'first-second' to 'firstSecond'
     * @param $str
     * @return mixed|string
     */
    public static function convertNodeStr($str)
    {
        $str = trim($str, '-');

        // convert 'first-second' to 'firstSecond'
        if (strpos($str, '-')) {
            $str = preg_replace_callback('/-+([a-z])/', function ($c) {
                return strtoupper($c[1]);
            }, trim($str, '- '));
        }

        return $str;
    }

    /**
     * @return array
     */
    public static function getSupportedMethods()
    {
        return self::SUPPORTED_METHODS;
    }

}
