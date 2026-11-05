<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Enums\StoreStatus;
use App\Exceptions\StockConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectStoreRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\CategoryService;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private StoreService $storeService,
        private CategoryService $categoryService,
    ) {}

    public function index(): View
    {
        $registrations = Store::where('created_at', '>=', now()->subWeeks(5)->startOfWeek())
            ->get(['created_at'])
            ->groupBy(fn ($s) => $s->created_at->format('Y-\WW'))
            ->map->count();

        $platformOrders = Order::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['created_at'])
            ->groupBy(fn ($o) => $o->created_at->format('Y-m-d'))
            ->map->count();

        return view('dashboard.superadmin.index', [
            'stats' => [
                'active_stores' => Store::where('status', StoreStatus::Approved)->count(),
                'pending_stores' => Store::where('status', StoreStatus::Pending)->count(),
                'products' => Product::count(),
                'orders' => Order::count(),
                'users' => User::count(),
            ],
            'registrations' => $registrations,
            'platformOrders' => $platformOrders,
            'latestApplications' => Store::where('status', StoreStatus::Pending)->with(['owner', 'category'])->latest('submitted_at')->take(5)->get(),
        ]);
    }

    public function applications(Request $request): View
    {
        return view('dashboard.superadmin.applications', [
            'stores' => $this->storeService->listApplications(['status' => $request->query('status', 'pending')]),
        ]);
    }

    public function approve(Request $request, Store $store): RedirectResponse
    {
        $this->storeService->approve($store, $request->user());

        return redirect()->route('admin.applications')->with('status', 'Toko disetujui.');
    }

    public function reject(RejectStoreRequest $request, Store $store): RedirectResponse
    {
        $this->storeService->reject($store, $request->user(), $request->validated()['rejection_reason']);

        return redirect()->route('admin.applications')->with('status', 'Pendaftaran ditolak.');
    }

    public function stores(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        return view('dashboard.superadmin.stores', [
            'stores' => $this->storeService->listApplications($filters),
            'filters' => $filters,
        ]);
    }

    public function suspend(Store $store): RedirectResponse
    {
        $this->storeService->setStatus($store, StoreStatus::Suspended);

        return redirect()->back()->with('status', 'Toko disuspend.');
    }

    public function activate(Store $store): RedirectResponse
    {
        $this->storeService->setStatus($store, StoreStatus::Approved);

        return redirect()->back()->with('status', 'Toko diaktifkan.');
    }

    public function users(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string'],
        ]);

        return view('dashboard.superadmin.users', [
            'users' => User::query()
                ->withCount('stores')
                ->when($filters['search'] ?? null, fn ($q, $term) => $q->where(fn ($w) => $w
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")))
                ->when($filters['role'] ?? null, fn ($q, $role) => $q->where('role', $role))
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function categories(): View
    {
        return view('dashboard.superadmin.categories', [
            'categories' => Category::withCount(['products', 'stores'])->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $this->categoryService->create($validated);

        return redirect()->route('admin.categories')->with('status', 'Kategori ditambahkan.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $this->categoryService->update($category, $validated);

        return redirect()->route('admin.categories')->with('status', 'Kategori diperbarui.');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        try {
            $this->categoryService->delete($category);

            return redirect()->route('admin.categories')->with('status', 'Kategori dihapus.');
        } catch (StockConflictException $e) {
            return redirect()->route('admin.categories')->with('error', $e->getMessage());
        }
    }

    public function orders(Request $request): View
    {
        $status = $request->query('status');

        return view('dashboard.superadmin.orders', [
            'orders' => Order::query()
                ->with(['store', 'user', 'items'])
                ->when($status, fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
            'status' => $status,
        ]);
    }
}
