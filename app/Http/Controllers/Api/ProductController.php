<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // CREATE: Add a new product
    public function store(Request $request)
    {
        // Validation of the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
        ]);
    
        // If validation fails, return the error messages
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
    
        // Product creation
        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
        ]);
    
        // Return success response with the created product data
        return response()->json([
            'result' => true,
            'message' => 'Product Created successful.',
            'data' => $product,
        ]);
    }
    

    // READ: Get all products
    public function index()
    {
        $products = Product::all();
        return response()->json([
            'result' => true,
            'message' => 'Product List Data',
            'data' => $products
        ]);
    }
 
    // READ: Get a product by ID
   // READ: Get a product by ID
public function show($id)
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json([
            'result' => false,
            'message' => 'Product not found',
        ], 401);
    }

    return response()->json([
        'result' => true,
        'message' => 'Product data show',
        'data' => $product,
    ]);
}


    // UPDATE: Update an existing product
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->registrationFailed('product not found');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
        ]);
        return response()->json([
            'result' => false,
            'message' => 'Product update successfully',
            'data' =>$product
            
        ]);
        // return response()->json($product);
    }

    // DELETE: Delete a product by ID
    public function destroy($id)
    {
        $product = Product::find($id); 

        if (!$product) {
            return $this->registrationFailed('Product not found');
        }

        $product->delete();
        return response()->json([
            'result' => false,
            'message' => 'Product Deleted successfully',
            'data' =>$product
            
        ]);
        // return response()->json(['message' => 'Product deleted successfully']);
    }

    protected function registrationFailed($message)
    {
       return response()->json([
            'result' => false,
            'message' => $message,
           
        ]);
    }
}