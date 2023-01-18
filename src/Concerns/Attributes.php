<?php

declare(strict_types=1);

namespace TiMacDonald\JsonApi\Concerns;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\PotentiallyMissing;
use Illuminate\Support\Collection;
use TiMacDonald\JsonApi\Support\Fields;

trait Attributes
{
    /**
     * @internal
     */
    private static bool $minimalAttributes = false;

    /**
     * @api
     *
     * @param  (callable(): void)|null  $callback
     * @return void
     */
    public static function minimalAttributes(callable|null $callback = null)
    {
        self::$minimalAttributes = true;

        if ($callback === null) {
            return;
        }

        try {
            $callback();
        } finally {
            self::$minimalAttributes = false;
        }
    }

    /**
     * @api
     * @infection-ignore-all
     *
     * @return void
     */
    public static function maximalAttributes()
    {
        self::$minimalAttributes = false;
    }

    /**
     * @internal
     *
     * @return Collection<string, mixed>
     */
    private function requestedAttributes(Request $request)
    {
        return Collection::make($this->toAttributes($request))
            ->only($this->requestedFields($request))
            ->map(static fn (mixed $value): mixed => value($value))
            ->reject(static fn (mixed $value): bool => $value instanceof PotentiallyMissing && $value->isMissing());
    }

    /**
     * @internal
     *
     * @return array<int, string>|null
     */
    private function requestedFields(Request $request)
    {
        return Fields::getInstance()->parse($request, $this->toType($request), self::$minimalAttributes);
    }
}
