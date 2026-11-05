<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'condition' => ['nullable', Rule::in(['new', 'preloved'])],
            'sort' => ['nullable', Rule::in(['latest', 'price_asc', 'price_desc'])],
        ]);

        $products = Product::query()
            ->with(['category', 'seller', 'images'])
            ->where('status', 'active')
            ->when($filters['q'] ?? null, fn (Builder $query, string $search) => $query
                ->where(function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                }))
            ->when($filters['category'] ?? null, fn (Builder $query, string $slug) => $query
                ->whereHas('category', fn (Builder $query) => $query->where('slug', $slug)))
            ->when($filters['condition'] ?? null, fn (Builder $query, string $condition) => $query
                ->where('condition', $condition));

        match ($filters['sort'] ?? 'latest') {
            'price_asc' => $products->orderBy('price')->orderByDesc('created_at')->orderByDesc('id'),
            'price_desc' => $products->orderByDesc('price')->orderByDesc('created_at')->orderByDesc('id'),
            default => $products->latest()->orderByDesc('id'),
        };

        return view('products.index', [
            'products' => $products->paginate(12)->withQueryString(),
            'categories' => Category::query()->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->status === 'active', 404);

        return view('products.show', [
            'product' => $product->load(['category', 'seller', 'images']),
        ]);
    }

    public function sellerIndex(Request $request): View
    {
        $products = $request->user()
            ->products()
            ->with(['category', 'images'])
            ->latest()
            ->paginate(12);

        return view('seller.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('seller.products.form', [
            'product' => new Product,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $images = $data['images'] ?? [];
        unset($data['images']);
        $data['slug'] = $this->uniqueSlug($data['name']);

        $storedPaths = [];

        try {
            $product = DB::transaction(function () use ($request, $data, $images, &$storedPaths): Product {
                /** @var User $seller */
                $seller = $request->user();
                $product = $seller->products()->create($data);
                $product->forceFill([
                    'status' => 'pending',
                    'rejection_reason' => null,
                    'is_featured' => false,
                ])->save();

                $this->storeImages($product, $images, $storedPaths);

                return $product;
            });
        } catch (Throwable $exception) {
            $this->cleanUpUploadedImages($storedPaths, $exception);
            throw $exception;
        }

        return redirect()
            ->route('seller.products.index')
            ->with('status', 'Produk berhasil dikirim dan menunggu moderasi admin.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('seller.products.form', [
            'product' => $product->load('images'),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $images = $data['images'] ?? [];
        unset($data['images']);

        if ($data['name'] !== $product->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product);
        }

        $storedPaths = [];

        try {
            DB::transaction(function () use ($data, $images, $product, &$storedPaths): void {
                $product->fill($data);
                $product->forceFill([
                    'status' => 'pending',
                    'rejection_reason' => null,
                    'is_featured' => false,
                ])->save();

                $this->storeImages($product, $images, $storedPaths);
            });
        } catch (Throwable $exception) {
            $this->cleanUpUploadedImages($storedPaths, $exception);
            throw $exception;
        }

        return redirect()
            ->route('seller.products.index')
            ->with('status', 'Perubahan disimpan. Produk kembali menunggu moderasi admin.');
    }

    public function archive(Product $product): RedirectResponse
    {
        $this->authorize('archive', $product);

        $product->forceFill([
            'status' => 'archived',
            'is_featured' => false,
        ])->save();

        return redirect()
            ->route('seller.products.index')
            ->with('status', 'Produk berhasil diarsipkan dan tidak lagi tampil di katalog.');
    }

    private function uniqueSlug(string $name, ?Product $except = null): string
    {
        $base = Str::slug($name) ?: 'produk';
        $slug = $base;
        $suffix = 2;

        while (Product::withTrashed()
            ->where('slug', $slug)
            ->when($except, fn (Builder $query) => $query->where('id', '!=', $except->id))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<int, UploadedFile>  $images
     * @param  array<int, string>  $storedPaths
     */
    private function storeImages(Product $product, array $images, array &$storedPaths): void
    {
        foreach ($images as $image) {
            $path = $image->storePublicly("products/{$product->id}", 'public');

            if (! is_string($path)) {
                throw new RuntimeException('The product image could not be stored.');
            }

            $storedPaths[] = $path;
            $product->images()->create(['image_path' => $path]);
        }
    }

    /**
     * @param  array<int, string>  $storedPaths
     */
    private function cleanUpUploadedImages(array $storedPaths, Throwable $cause): void
    {
        if ($storedPaths !== [] && ! Storage::disk('public')->delete($storedPaths)) {
            throw new RuntimeException('Uploaded product images could not be cleaned up after a failed save.', 0, $cause);
        }
    }
}
