<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'description',
        'price',
        'image_path',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
                return $this->image_path;
            }

            if (file_exists(public_path($this->image_path))) {
                return asset($this->image_path);
            }

            if (file_exists(public_path('images/'.$this->image_path))) {
                return asset('images/'.$this->image_path);
            }

            if (file_exists(public_path('storage/'.$this->image_path))) {
                return asset('storage/'.$this->image_path);
            }

            return Storage::disk('public')->url($this->image_path);
        }

        $lowerName = strtolower($this->name ?? '');
        if (str_contains($lowerName, 'drip') && file_exists(public_path('images/products/drip-coffee.jpg'))) {
            return asset('images/products/drip-coffee.jpg');
        }
        if (str_contains($lowerName, 'arabika') && file_exists(public_path('images/products/kopi-arabika.jpg'))) {
            return asset('images/products/kopi-arabika.jpg');
        }
        if (str_contains($lowerName, 'robusta') && file_exists(public_path('images/products/kopi-robusta.jpg'))) {
            return asset('images/products/kopi-robusta.jpg');
        }
        if (str_contains($lowerName, 'singkong') && file_exists(public_path('images/products/keripik-singkong.jpg'))) {
            return asset('images/products/keripik-singkong.jpg');
        }
        if (str_contains($lowerName, 'tempe') && file_exists(public_path('images/products/keripik-tempe.jpg'))) {
            return asset('images/products/keripik-tempe.jpg');
        }

        return null;
    }

    /** Scope: hanya produk aktif dari toko yang approved dan tidak suspend. */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereHas('store', fn ($q) => $q->where('status', 'approved'));
    }
}
