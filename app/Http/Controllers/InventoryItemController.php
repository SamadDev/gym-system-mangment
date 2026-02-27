<?php

namespace App\Http\Controllers;

use App\Constants\DateTimeConstants;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InventoryItemController extends Controller
{
    use HttpResponses;

    /**
     * Get DataTables data for inventory items listing
     */
    public function data(Request $request)
    {
        $items = InventoryItem::tableSelect()->tableRelation()->tableFilter();

        return DataTables::eloquent($items)
            ->editColumn('cost_price', function ($data) {
                return number_format($data->cost_price, 2) . ' IQD';
            })
            ->editColumn('selling_price', function ($data) {
                return number_format($data->selling_price, 2) . ' IQD';
            })
            ->editColumn('stock_value', function ($data) {
                return number_format($data->stock_value, 2) . ' IQD';
            })
            ->editColumn('profit_margin', function ($data) {
                return number_format($data->profit_margin, 2) . '%';
            })
            ->editColumn('stock_status', function ($data) {
                if ($data->isOutOfStock()) {
                    return 'Out of Stock';
                } elseif ($data->isLowStock()) {
                    return 'Low Stock';
                } else {
                    return 'In Stock';
                }
            })
            ->editColumn('image', function ($data) {
                return $data->image ? asset('storage/' . $data->image) : null;
            })
            ->editColumn('created_at', function ($data) {
                return $data->created_at?->timezone(DateTimeConstants::TIMEZONE)
                    ->format(DateTimeConstants::DISPLAY_DATETIME_FORMAT);
            })
            ->only(['id', 'name', 'category', 'sku', 'quantity', 'unit', 'cost_price', 'selling_price', 'stock_value', 'profit_margin', 'stock_status', 'image', 'created_at'])
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('view_inventory')) {
            abort(403, 'Unauthorized action.');
        }

        return view('app');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventoryItemRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('inventory/images', 'public');
            }

            $item = InventoryItem::create($data);

            DB::commit();

            return $this->success($item, 'Inventory item created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($inventoryItem->image) {
                    Storage::disk('public')->delete($inventoryItem->image);
                }
                $data['image'] = $request->file('image')->store('inventory/images', 'public');
            }

            $inventoryItem->update($data);

            DB::commit();

            return $this->success($inventoryItem, 'Inventory item updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryItem $inventoryItem)
    {
        if (!auth()->user()->can('delete_inventory')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            // Delete image if exists
            if ($inventoryItem->image) {
                Storage::disk('public')->delete($inventoryItem->image);
            }

            $inventoryItem->delete();

            DB::commit();

            return $this->success(null, 'Inventory item deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Get inventory statistics
     */
    public function stats(Request $request)
    {
        try {
            $stats = [
                'total_items' => InventoryItem::count(),
                'total_stock_value' => InventoryItem::get()->sum('stock_value'),
                'low_stock_items' => InventoryItem::lowStock()->count(),
                'out_of_stock_items' => InventoryItem::outOfStock()->count(),
                'items_by_category' => InventoryItem::selectRaw('category, COUNT(*) as count, SUM(quantity) as total_quantity')
                    ->groupBy('category')
                    ->get(),
                'low_stock_list' => InventoryItem::lowStock()
                    ->select('id', 'name', 'quantity', 'reorder_level')
                    ->limit(10)
                    ->get(),
            ];

            return $this->success($stats, 'Inventory statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    /**
     * Adjust stock quantity
     */
    public function adjustStock(Request $request, InventoryItem $inventoryItem)
    {
        if (!auth()->user()->can('edit_inventory')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'adjustment_type' => 'required|in:increase,decrease',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            if ($request->adjustment_type === 'increase') {
                $inventoryItem->increaseStock($request->quantity);
                $message = 'Stock increased successfully';
            } else {
                $success = $inventoryItem->decreaseStock($request->quantity);
                if (!$success) {
                    return $this->error('Insufficient stock quantity', 400);
                }
                $message = 'Stock decreased successfully';
            }

            DB::commit();

            return $this->success($inventoryItem, $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }
}
