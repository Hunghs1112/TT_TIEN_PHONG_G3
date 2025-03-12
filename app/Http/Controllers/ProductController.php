<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductImage;

class ProductController extends Controller
{
    // Lấy danh sách sản phẩm có phân trang và lọc
    public $timestamps = true;
    public function index(Request $request)
    {
        $products = Product::query();
    
        // Lọc theo danh mục
        if ($request->filled('category_id')) {
            $products->where('category_id', $request->category_id);
        }
    
        // Lọc theo khoảng giá
        if ($request->filled('price_range')) {
            $range = explode('-', $request->price_range);
            if (count($range) === 2) {
                $products->whereBetween('price', [(float)$range[0], (float)$range[1]]);
            }
        }
    
        // Lọc theo từ khóa
        if ($request->filled('keyword')) {
            $products->where('name', 'like', '%' . $request->keyword . '%');
        }
    
        // Sắp xếp theo order_by (mặc định là created_at)
        $validColumns = ['name', 'price', 'created_at']; // Danh sách cột hợp lệ
        $order_by = in_array($request->order_by, $validColumns) ? $request->order_by : 'created_at';
        $order = $request->order === 'desc' ? 'desc' : 'asc';
    
        $products->orderBy($order_by, $order);
    
        return response()->json($products->paginate(10));
    }
    

    // Tạo sản phẩm
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public'); // Lưu vào storage/app/public/products
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path
                ]);
            }
        }

        return response()->json($product->load('images'), 201);

        return response()->json($product, 201);
    }

    // Lấy chi tiết sản phẩm
    public function show($id)
    {
        $product = Product::with('images')->findOrFail($id);
        return response()->json($product);
    }

    // Cập nhật sản phẩm
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $product->update($request->only(['name', 'price', 'stock']));

        // Xóa ảnh cũ nếu có yêu cầu
        if ($request->remove_images) {
            ProductImage::whereIn('id', $request->remove_images)->delete();
        }

        // Upload ảnh mới
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path
                ]);
            }
        }

        return response()->json($product->load('images'));
    }


    // Xóa sản phẩm
    public function destroy($id)
    {
        // Kiểm tra sản phẩm có tồn tại không
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Sản phẩm không tồn tại.'], 404);
        }
    
        // Kiểm tra nếu sản phẩm đã có trong đơn hàng
        $existsInOrders = OrderItem::where('product_id', $id)->exists();
        if ($existsInOrders) {
            return response()->json(['message' => 'Không thể xóa sản phẩm vì đã có trong đơn hàng.'], 400);
        }
    
        // Nếu sản phẩm không có trong đơn hàng, tiến hành xóa
        $product->delete();
    
        return response()->json(['message' => 'Sản phẩm đã được xóa thành công.'], 200);
    }
    
}
