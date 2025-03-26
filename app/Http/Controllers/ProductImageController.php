<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductImage;
use App\Models\Product;

class ProductImageController extends Controller
{
    /**
     * Upload ảnh sản phẩm
     */
    public function uploadImage(Request $request, $productId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Kiểm tra sản phẩm có tồn tại không
        $product = Product::findOrFail($productId);

        // Lưu ảnh vào storage/app/public/products
        $path = $request->file('image')->store('products', 'public');

        // Lưu vào bảng product_images
        $productImage = ProductImage::create([
            'product_id' => $productId,
            'image_url' => Storage::url($path),
        ]);

        return response()->json([
            'message' => 'Image uploaded successfully',
            'image_url' => $productImage->image_url
        ], 200);
    }

    /**
     * Lấy danh sách ảnh của sản phẩm
     */
    public function getProductImages($productId)
    {
        $images = ProductImage::where('product_id', $productId)->get(['id', 'image_url']);
        return response()->json($images);
    }

    /**
     * Xóa ảnh sản phẩm
     */
    public function deleteImage($imageId)
    {
        $image = ProductImage::findOrFail($imageId);

        // Xóa file ảnh trong storage
        Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));

        // Xóa bản ghi trong database
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully'], 200);
    }
}
