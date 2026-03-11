<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\CloudinaryFileUploadService;
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
        // dd($request->all());
        try {
            $data = $request->validated();
            // Handle file upload to public disk , storage/app/public/products/xxx.jpg
            if ($request->hasFile('image_url')) {
                // formal way to upload file to public disk
                // $path = $request->file('image_url')->store('products', 'public');
                // $data['image_url'] = $path;

                // cloudinary upload (instance create) code refractor
                $cloudinaryService = new CloudinaryFileUploadService;
                $data['image_url'] = $cloudinaryService->upload($request->file('image_url'), 'products');
            }

            $product = Product::create($data);
            $product->load('category');

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
    public function update(UpdateRequest $request, Product $product, CloudinaryFileUploadService $cloudinaryFileUploadService)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image_url')) {
                $newImageUrl = $cloudinaryFileUploadService->upload($request->file('image_url'), 'products');

                if ($product->image_url) {
                    $cloudinaryFileUploadService->deleteByUrl($product->image_url);

                    $oldImagePath = $this->normalizePublicImagePath($product->image_url);
                    if ($oldImagePath) {
                        Storage::disk('public')->delete($oldImagePath);
                    }
                }

                $data['image_url'] = $newImageUrl;
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
    public function destroy(string $id)
    {
        try {
            $product = Product::find($id);
            if (! $product) {
                return response()->json([
                    'message' => 'Product not found',
                ], 404);
            }
            // if ($product->image_url){
            //  Storage::disk('public')->delete($product->image_url);
            //  }
            // $product->delete();

            // delete form cloudinary::
            if ($product->image_url) {
                $cloudinaryService = new CloudinaryFileUploadService;
                $cloudinaryService->deleteByUrl($product->image_url);
            }
            $product->delete();

            return response()->json([
                'message' => 'Product delete successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
            ], 500);
        }
    }
}
