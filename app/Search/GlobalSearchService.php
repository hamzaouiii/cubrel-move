<?php

namespace App\Search;

class GlobalSearchService
{
    public function __construct(
        private readonly array $searchers
    ) {}

    public function search(string $query): array
    {
        if (\strlen(\trim($query)) < 2) {
            return [];
        }

        $results = [];

        foreach ($this->searchers as $searcher) {
            $hits = $searcher->search($query);

            if ($hits->isNotEmpty()) {
                $results[] = $hits;
            }
        }

        return $results;
    }
}
