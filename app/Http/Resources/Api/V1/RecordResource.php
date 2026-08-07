<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Generic record resource shared by every module (mirrors the app's
 * existing "one generic controller/handler" style) - strips sensitive
 * fields before a record is ever serialized for a partner API response.
 */
class RecordResource extends JsonResource
{
    
     // hardcoded deny list
     // no column with these names is ever exposed to the API
    protected const DENYLIST_PATTERNS = ['/token/i', '/secret/i', '/password/i', '/_hash$/i', '/recovery_codes/i'];

    protected string $moduleSlug;

    public function __construct($resource, string $moduleSlug)
    {
        parent::__construct($resource);
        $this->moduleSlug = $moduleSlug;
    }

    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : $this->resource->toArray();
        $data = $this->foldCustomFields($data);

        $hidden = config("api.hidden_fields.{$this->moduleSlug}", []);

        foreach (array_keys($data) as $key) {
            if (in_array($key, $hidden, true) || $this->matchesDenylistPattern($key)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * HasCustomFields::toArray() already flattens registered custom fields
     * unset it
     */
    protected function foldCustomFields(array $data): array
    {
        unset($data['custom_fields']);

        return $data;
    }

    protected function matchesDenylistPattern(string $key): bool
    {
        foreach (self::DENYLIST_PATTERNS as $pattern) {
            if (preg_match($pattern, $key) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Wrap a paginated list (as returned by BaseModuleHandler::getListData())
     * into the API's { data, meta, links } envelope.
     */
    public static function collectionFromListData(array $listData, string $moduleSlug): array
    {
        return [
            'data' => collect($listData['items'])
                ->map(fn ($item) => (new self($item, $moduleSlug))->toArray(request()))
                ->values()
                ->all(),
            'meta' => [
                'total' => $listData['meta']['total'],
                'per_page' => $listData['meta']['perPage'],
                'current_page' => $listData['meta']['currentPage'],
                'last_page' => $listData['meta']['lastPage'],
            ],
            'links' => [
                'next' => $listData['meta']['links']['next'],
                'prev' => $listData['meta']['links']['prev'],
            ],
        ];
    }
}
