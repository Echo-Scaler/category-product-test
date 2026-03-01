<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Exception;
use Illuminate\Database\QueryException;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categories = Category::all();

            return response()->json([
                'message' => 'Categories retrieved successfully',
                'data' => CategoryResource::collection($categories),
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
            $category = Category::create($request->validated());

            return response()->json([
                'message' => 'Category created successfully',
                'data' => CategoryResource::make($category),
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Database error occurred',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $category = Category::find($id);
            if (! $category) {
                return response()->json([
                    'message' => 'Category not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Category retrieved successfully',
                'data' => CategoryResource::make($category),
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
    public function update(StoreRequest $request, $id)
    {
        try {
            $category = Category::find($id);
            if (! $category) {
                return response()->json([
                    'message' => 'Category not found',
                ], 404);
            }
            $category->update($request->validated());

            return response()->json([
                'message' => 'Category updated successfully',
                'data' => CategoryResource::make($category),
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
    public function destroy($id)
    {
        try {
            $category = Category::find($id);
            if (! $category) {
                return response()->json([
                    'message' => 'Category not found',
                ], 404);
            }
            $category->delete();

            return response()->json([
                'message' => 'Category deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
