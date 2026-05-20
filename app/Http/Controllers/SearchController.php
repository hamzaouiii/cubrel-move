<?php

namespace App\Http\Controllers;

use App\Search\GlobalSearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        private readonly GlobalSearchService $searchService
    ) {}

    public function __invoke(Request $request)
    {
        $query = $request->validate(['q' => 'required|string|min:2|max:100'])['q'];
        $results = $this->searchService->search($query);

        return response()->json(['results' => $results]);
    }
}
