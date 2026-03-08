<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Product::query()->with('category');  // eager loading,N+1 problem ကိုဖြေရှင်းပေးတယ် => "with" use relationships to avoid N+1 problem

            // $products = Product::all();  lazy loading
            // foreach ($products as $product) {
            // $product->category->name;
            // }
            // Product ၁၀၀ ခုရှိရင် Category query ကို ၁၀၀ ကြိမ် ထပ်ခေါ်ရမယ်
            // Database ပိုနှေး, Performance ဆိုး ⚠️ ဒါကို N+1 Problem လို့ခေါ်ပါတယ်။
            // dd($products);

            if ($request->has('search')) {
                $searchTerm = $request['search'];

                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%')
                        ->orWhere('description', 'like', '%'.$searchTerm.'%')
                        ->orWhere('id', $searchTerm)
                        ->orWhere('price', $searchTerm)
                        ->orWhere('category_id', $searchTerm);
                });
            }

            $perPage = $request->get('per_page', 10); // call pagination method
            $products = $query->paginate($perPage); // paginate results perPage

            return response()->json([
                'message' => 'Products retrieved successfully',
                'data' => ProductResource::collection($products),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        try {
            // $product = Product::create($request->validated());
            $data = $request->validated();
            if ($request->hasFile('image_url')) {
                // php artisan storage:link => public/storage ကို storage/app/public နဲ့ link လုပ်ပေးတယ်
                // storeage/app/public/products/filename.jpg
                $path = $request->file('image_url')->store('products', 'public');
                // if want to save under private
                // $path = $request->file('image_url')->store('products'); // default is private
                $data['image_url'] = $path; // save path to database
            }
            $product = Product::create($data);
            $product->load('category'); // safe for N+1 query problem

            return response()->json([
                'message' => 'Product created successfully',
                'data' => ProductResource::make($product),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Product creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        try {
            $product->load('category');

            return response()->json([
                'message' => 'Product retrieved successfully',
                'data' => ProductResource::make($product),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Product $product)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image_url')) {
                $newImagePath = $request->file('image_url')->store('products', 'public');

                if ($product->image_url) {
                    $oldImagePath = $this->normalizePublicImagePath($product->image_url);
                    if ($oldImagePath) {
                        Storage::disk('public')->delete($oldImagePath);
                    }
                }

                $data['image_url'] = $newImagePath;
            }

            $product->update($data);
            $product->load('category');

            return response()->json([
                'message' => 'Product updated successfully',
                'data' => ProductResource::make($product),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            if ($product->image_url) {
                // Normalize DB value to public disk path (e.g. products/xxx.jpg)
                $imagePath = $this->normalizePublicImagePath($product->image_url);
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath); // for public disk
                }

                // Storage::delete($product->image_url); // for private disk
            }
            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function normalizePublicImagePath(?string $imageUrl): ?string
    {
        if (! $imageUrl) {
            return null;
        }

        // Supports:
        // - products/file.jpg
        // - /storage/products/file.jpg
        // - http://domain/storage/products/file.jpg
        $path = parse_url($imageUrl, PHP_URL_PATH) ?? $imageUrl;
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8); // remove `storage/`
        }

        return str_starts_with($path, 'products/') ? $path : null;
    }
}
