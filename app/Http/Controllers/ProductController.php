<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Exception;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $products = Product::with('category')->get();
            // $products = Product::all();
            // foreach ($products as $product) {
            // $product->category->name;
            // }
            // Product ၁၀၀ ခုရှိရင် Category query ကို ၁၀၀ ကြိမ် ထပ်ခေါ်ရမယ်
            // Database ပိုနှေး, Performance ဆိုး ⚠️ ဒါကို N+1 Problem လို့ခေါ်ပါတယ်။

            // dd($products);

            return response()->json([
                'message' => 'Products retrieved successfully',
                'data' => ProductResource::collection($products),
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
            $product = Product::create($request->validated());
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
            $product->update($request->validated());
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
}
