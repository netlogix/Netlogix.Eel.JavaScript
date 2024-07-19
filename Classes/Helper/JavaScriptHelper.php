<?php

declare(strict_types=1);

namespace Netlogix\Eel\JavaScript\Helper;

use MatthiasMullie\Minify\JS as JSMinify;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\ResourceManagement\Streams\ResourceStreamWrapper;
use ReflectionClass;
use function array_keys;
use function array_reduce;
use function json_encode;
use function sprintf;
use const JSON_PRETTY_PRINT;

/**
 * @Flow\Scope("singleton")
 */
final class JavaScriptHelper implements ProtectedContextAwareInterface
{
    public function embed(string $path): string
    {
        return $this->embedWithVariables($path, []);
    }

    public function embedWithVariables(string $path, array $variables): string
    {
        $script = self::minify($path);
        if (count($variables) === 0) {
            return $script;
        }

        $declaredVariables = array_reduce(array_keys($variables),
            fn ($vars, $key) => $vars . PHP_EOL . sprintf('var %s = %s;', $key,
                    json_encode($variables[$key], JSON_PRETTY_PRINT)), '');

        return trim($declaredVariables . PHP_EOL . $script);
    }

    private static function minify(string ...$paths): string
    {
        $minifier = new JSMinify(array_map([self::class, 'resolvePath'], $paths));

        return $minifier->minify();
    }

    /**
     * Resolve resource:// to absolute uris as the minifier does not "uri like" paths
     */
    private static function resolvePath(string $path): string
    {
        if (strpos($path, 'resource://') !== 0) {
            return $path;
        }

        $streamWrapper = Bootstrap::$staticObjectManager->get(ResourceStreamWrapper::class);
        $reflectionClass = new ReflectionClass($streamWrapper);

        $method = $reflectionClass->getMethod('evaluateResourcePath');
        $method->setAccessible(true);

        return $method->invoke($streamWrapper, $path);
    }

    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
