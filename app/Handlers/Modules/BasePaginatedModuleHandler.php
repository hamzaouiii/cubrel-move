<?php

namespace App\Handlers\Modules;

use App\Contracts\ModuleHandler;
use Illuminate\Database\Eloquent\Builder;

abstract class BasePaginatedModuleHandler implements ModuleHandler
{

    abstract protected function query(array $params = []): Builder;


    protected function getPerPage(array $params): int
    {
        return $params['perPage'] ?? 31;
    }

    protected function transformItems(array $items, array $params): array
    {
        return $items;
    }

    public function getListData(array $params = []): array
    {
        $perPage   = $this->getPerPage($params);
        $query     = $this->query($params);

        $paginator = $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $pages = [];
        $last  = $paginator->lastPage();

        for ($p = 1; $p <= $last; $p++) {
            $pages[] = [
                'label'  => (string) $p,
                'page'   => $p,
                'url'    => $paginator->url($p),
                'active' => $p === $paginator->currentPage(),
            ];
        }

        $items = $this->transformItems($paginator->items(), $params);

        return [
            'items' => $items,
            'meta'  => [
                'total'       => $paginator->total(),
                'perPage'     => $paginator->perPage(),
                'currentPage' => $paginator->currentPage(),
                'lastPage'    => $last,
                'from'        => $paginator->firstItem(),
                'to'          => $paginator->lastItem(),
                'links'       => [
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
                'pages'       => $pages,
            ],
        ];
    }
}
