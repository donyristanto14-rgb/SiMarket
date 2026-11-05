<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductModerationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'active'])],
        ]);
        $status = $filters['status'] ?? 'pending';
        $products = Product::query()
            ->with(['seller', 'category', 'images'])
            ->where('status', $status)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'status'));
    }

    public function approve(Product $product): RedirectResponse
    {
        $this->authorize('moderate', $product);
        abort_unless($product->status === 'pending', 409, 'Only pending products can be approved.');

        $product->forceFill([
            'status' => 'active',
            'rejection_reason' => null,
        ])->save();

        return back()->with('status', 'Produk disetujui dan sudah tampil di katalog.');
    }

    public function reject(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('moderate', $product);
        abort_unless($product->status === 'pending', 409, 'Only pending products can be rejected.');

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.',
            'reason.max' => 'Alasan penolakan maksimal 2000 karakter.',
        ]);

        $product->forceFill([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'],
            'is_featured' => false,
        ])->save();

        return back()->with('status', 'Produk ditolak. Penjual dapat melihat alasan dan mengirim ulang setelah memperbaiki listing.');
    }

    public function suspend(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('moderate', $product);
        abort_unless($product->status === 'active', 409, 'Only active products can be suspended.');

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ], [
            'reason.required' => 'Alasan penangguhan wajib diisi.',
            'reason.max' => 'Alasan penangguhan maksimal 2000 karakter.',
        ]);

        $product->forceFill([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'],
            'is_featured' => false,
        ])->save();

        return back()->with('status', 'Listing ditangguhkan dan tidak lagi tampil di katalog.');
    }

    public function toggleFeatured(Product $product): RedirectResponse
    {
        $this->authorize('moderate', $product);
        abort_unless($product->status === 'active', 409, 'Only active products can be featured.');

        $product->forceFill([
            'is_featured' => ! $product->is_featured,
        ])->save();

        return back()->with(
            'status',
            $product->is_featured ? 'Produk ditambahkan ke pilihan.' : 'Produk dihapus dari pilihan.'
        );
    }
}
