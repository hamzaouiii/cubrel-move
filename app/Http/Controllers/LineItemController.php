<?php

namespace App\Http\Controllers;

use App\Models\Modules\LineItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LineItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'parent_type' => ['required', 'string'],
            'parent_id'   => ['required', 'string'],
        ]);

        $items = LineItem::query()
            ->where('parent_type', $request->parent_type)
            ->where('parent_id', $request->parent_id)
            ->orderBy('sort_order')
            ->get();

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'parent_type'          => ['required', 'string'],
            'parent_id'            => ['required', 'uuid'],
            'product_id'           => ['nullable', 'uuid'],
            'name'                 => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'unit'                 => ['nullable', 'string', 'max:255'],
            'unit_price'  => ['required', 'numeric', 'min:0'],
            'quantity'             => ['required', 'numeric', 'min:0'],
            'discount'             => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_rate'             => ['nullable', 'numeric', 'min:0'],
            'note'                 => ['nullable', 'string', 'max:1000'],
            'sort_order'           => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $item = new LineItem($data);
        $item->id = Str::uuid();
        $item->calculateTotals()->save();

        return response()->json($item, 201);
    }

    public function update(Request $request, LineItem $lineItem): JsonResponse
    {
        $data = $request->validate([
            'product_id'           => ['nullable', 'uuid'],
            'name'                 => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'unit'                 => ['nullable', 'string', 'max:255'],
            'unit_price'  => ['required', 'numeric', 'min:0'],
            'quantity'             => ['required', 'numeric', 'min:0'],
            'discount'             => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_rate'             => ['nullable', 'numeric', 'min:0'],
            'note'                 => ['nullable', 'string', 'max:1000'],
            'sort_order'           => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $lineItem->fill($data);
        $lineItem->calculateTotals()->save();

        return response()->json($lineItem);
    }

    public function destroy(LineItem $lineItem): JsonResponse
    {
        $lineItem->delete();

        return response()->json(null, 204);
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'parent_type' => ['required', 'string'],
            'parent_id'   => ['required', 'uuid'],
            'order'       => ['required', 'array'],
            'order.*'     => ['required', 'uuid'],
        ]);

        LineItem::query()
            ->where('parent_type', $request->parent_type)
            ->where('parent_id', $request->parent_id)
            ->whereIn('id', $request->order)
            ->get()
            ->each(function (LineItem $item) use ($request) {
                $item->sort_order = array_search($item->id, $request->order);
                $item->saveQuietly();
            });

        return response()->json(null, 204);
    }
}