<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'sku',
        'barcode',
        'quantity',
        'unit',
        'cost_price',
        'selling_price',
        'reorder_level',
        'supplier',
        'image',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'reorder_level' => 'integer',
    ];

    // Relationships
    // public function sales(): HasMany
    // {
    //     return $this->hasMany(InventorySale::class);
    // }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', 0);
    }

    public function scopeCategory($query, $category)
    {
        if (empty($category)) {
            return $query;
        }

        return $query->where('category', $category);
    }

    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%");
        });
    }

    /**
     * For DataTables: Select columns for table view
     */
    public function scopeTableSelect($query)
    {
        return $query->select([
            'id',
            'name',
            'category',
            'sku',
            'barcode',
            'quantity',
            'unit',
            'cost_price',
            'selling_price',
            'reorder_level',
            'supplier',
            'image',
            'created_at',
        ]);
    }

    /**
     * For DataTables: Load relationships
     */
    public function scopeTableRelation($query)
    {
        // return $query->withCount('sales')
        //     ->withSum('sales as total_sales_amount', 'total_price');
        return $query;
    }

    /**
     * For DataTables: Apply filters
     */
    public function scopeTableFilter($query)
    {
        if (request()->has('category') && request('category') !== '') {
            $query->where('category', request('category'));
        }

        if (request()->has('stock_status')) {
            $status = request('stock_status');
            if ($status === 'low') {
                $query->whereColumn('quantity', '<=', 'reorder_level');
            } elseif ($status === 'out') {
                $query->where('quantity', 0);
            } elseif ($status === 'in_stock') {
                $query->where('quantity', '>', 0);
            }
        }

        return $query;
    }

    // Methods
    public function increaseStock(int $quantity): void
    {
        $this->quantity += $quantity;
        $this->save();
    }

    public function decreaseStock(int $quantity): bool
    {
        if ($this->quantity < $quantity) {
            return false;
        }

        $this->quantity -= $quantity;
        $this->save();
        
        return true;
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity === 0;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price == 0) {
            return 0;
        }

        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    public function getStockValueAttribute(): float
    {
        return $this->quantity * $this->cost_price;
    }
}
