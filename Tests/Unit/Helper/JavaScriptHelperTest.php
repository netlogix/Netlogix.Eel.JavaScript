<?php

declare(strict_types=1);

namespace Netlogix\Eel\JavaScript\Tests\Unit\Helper;

use Neos\Flow\Core\Bootstrap;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\ResourceManagement\Streams\ResourceStreamWrapper;
use Neos\Flow\Tests\UnitTestCase;
use Netlogix\Eel\JavaScript\Helper\JavaScriptHelper;

class JavaScriptHelperTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testEmbed(): void
    {
        $fileName = __DIR__ . '/../../Fixtures/hello-world.js';

        $helper = new JavaScriptHelper();

        $this->assertEquals(
            <<<JS
console.log('Hello World')
JS,
            $helper->embed($fileName)
        );
    }

    /**
     * @test
     */
    public function testEmbedWithVariables(): void
    {
        $fileName = __DIR__ . '/../../Fixtures/hello-world.js';

        $helper = new JavaScriptHelper();

        $this->assertEquals(
            <<<JS
var someGreatVariable = "withSomeValue";
var andAnInt = 42;
console.log('Hello World')
JS,
            $helper->embedWithVariables($fileName, ['someGreatVariable' => 'withSomeValue', 'andAnInt' => 42])
        );
    }

    /**
     * @test
     */
    public function testCodeIsMinified(): void
    {
        $fileName = __DIR__ . '/../../Fixtures/bubblesort.js';

        $helper = new JavaScriptHelper();

        $this->assertEquals(
            <<<JS
var arr = [
    234,
    43,
    55,
    63,
    5,
    6,
    235,
    547
];
function bblSort(arr){for(var i=0;i<arr.length;i++){for(var j=0;j<(arr.length-i-1);j++){if(arr[j]>arr[j+1]){var temp=arr[j]
arr[j]=arr[j+1]
arr[j+1]=temp}}}
console.log(arr)}
bblSort(arr)
JS,
            $helper->embedWithVariables($fileName, ['arr' => [234, 43, 55, 63, 5, 6, 235, 547]])
        );
    }

    /**
     * @test
     */
    public function Flow_resource_Uris_are_resolved(): void
    {
        $fileName = 'resource://Netlogix.Eel.JavaScript/Private/JavaScript/hello-world.js';
        $previousStaticObjectManager = Bootstrap::$staticObjectManager;
        try {
            $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)->getMock();
            $streamWrapper = new class extends ResourceStreamWrapper {
                protected function evaluateResourcePath($requestedPath, $checkForExistence = true)
                {
                    return __DIR__ . '/../../Fixtures/hello-world.js';
                }
            };

            $objectManager
                ->expects(self::once())
                ->method('get')
                ->with(ResourceStreamWrapper::class)
                ->willReturn($streamWrapper);

            Bootstrap::$staticObjectManager = $objectManager;

            $helper = new JavaScriptHelper();

            $this->assertEquals(
                <<<JS
console.log('Hello World')
JS,
                $helper->embed($fileName)
            );
        } finally {
            Bootstrap::$staticObjectManager = $previousStaticObjectManager;
        }
    }
}
