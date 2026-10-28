<?php

namespace App\Services;

use App\Exceptions\StockConflictException;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function list(): Collection
    {
        return Category::all();
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }

    public function delete(Category $category): void
    {
        if ($category->products()->exists()) {
            throw new StockConflictException('Kategori tidak dapat dihapus karena masih memuat produk aktif.', 'CATEGORY_IN_USE');
        }

        $category->delete();
    }
}
