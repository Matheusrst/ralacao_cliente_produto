<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    /**
     * index function
     *
     * @return void
     */
    public function index()
    {
        return Product::all();
    }

    /**
     * store function
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        $product = Product::create($validatedData);

        return response()->json($product, 201);
    }

    /**
     * show function
     *
     * @param Product $product
     * @return void
     */
    public function show(Product $product)
    {
        return $product;
    }

    /**
     * update function
     *
     * @param Request $request
     * @param Product $product
     * @return void
     */
    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric',
            'stock' => 'sometimes|required|integer',
        ]);

        $product->update($validatedData);

        return response()->json($product, 200);
    }

    /**
     * destroy function
     *
     * @param Product $product
     * @return void
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(null, 204);
    }
}
