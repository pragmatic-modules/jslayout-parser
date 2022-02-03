<?php
declare(strict_types=1);

namespace Pragmatic\JsLayoutParser\Model;

use Pragmatic\JsLayoutParser\Api\ComponentInterface;

class JsLayoutParser
{
    public function parse(array $jsLayout, string $rootComponent) : ComponentInterface
    {
        return new Component($rootComponent, $jsLayout['components'][$rootComponent]);
    }
}
