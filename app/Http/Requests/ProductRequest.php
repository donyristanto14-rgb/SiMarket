<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $product instanceof Product
            ? $this->user()?->can('update', $product) ?? false
            : $this->user()?->can('create', Product::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'condition' => ['required', 'in:new,preloved'],
            'stock' => ['required', 'integer', 'min:1', 'max:1000000'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Pilih kategori produk.',
            'category_id.exists' => 'Kategori yang dipilih tidak tersedia.',
            'name.required' => 'Nama produk wajib diisi.',
            'description.required' => 'Deskripsi produk wajib diisi.',
            'price.required' => 'Harga produk wajib diisi.',
            'price.min' => 'Harga harus lebih dari nol.',
            'condition.required' => 'Pilih kondisi produk.',
            'condition.in' => 'Kondisi produk tidak valid.',
            'stock.required' => 'Stok produk wajib diisi.',
            'stock.min' => 'Stok minimal satu.',
            'images.max' => 'Unggah maksimal lima gambar sekaligus.',
            'images.*.image' => 'Setiap file harus berupa gambar yang valid.',
            'images.*.mimes' => 'Gambar harus berformat JPG, PNG, atau WebP.',
            'images.*.max' => 'Ukuran setiap gambar maksimal 5 MB.',
        ];
    }
}
