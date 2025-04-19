<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return view('product.index', compact('products'));
    }

    public function home()
    {
        \Log::info('Masuk ke ProductController@home');
        $products = Product::all();
        return view('cashier.product.home', compact('products'));
    
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('product.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge(['price'=> str_replace(['Rp', '.', '',], '', $request->price)]);

        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        $imageName = time() . '.' . $request->image->extension(); // Nama unik
        $request->image->move(public_path('images'), $imageName); // Simpan ke folder public/images

        Product::create([
            'name' => $request->name,
            'image' => $imageName,
            'price' => $request->price,
            'stock' => $request->stock,
        ]);

        return redirect()->route('product.index')->with('success', 'Success create product!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }
    

public function updateStock(Request $request)
{   
    
    $productId = $request->input('product_id');
    $newStock = $request->input('stock');

    $product = Product::findOrFail($productId);
    $product->stock = $newStock;
    $product->save();

    return response()->json(['success' => true, 'message' => 'Stock updated successfully']);
}
    


public function adjustStock(Request $request, $id)
{
    $product = Product::find($id);
    if (!$product) {
        return response()->json(['success' => false, 'message' => 'Product not found.']);
    }

    $product->stock = $request->stock;
    $product->save();

    return response()->json(['success' => true, 'message' => 'Stock updated successfully.']);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $product = Product::findorFail($id);
        return view('product.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    
        public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Bisa nullable agar tidak wajib diubah
        'price' => 'required|numeric',
        
    ]);

    $product = Product::findOrFail($id);

    if ($request->hasFile('image')) { // Hanya update gambar jika ada
        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('images'), $imageName);
        $product->image = $imageName;
    }

    $product->update([
        'name' => $request->name,
        'price' => $request->price,
        
    ]);
    

    return redirect()->route('product.index')->with('success', 'Success update product!');
}





    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
    
        return redirect()->route('product.index')->with('deleted', 'Product deleted successfully!');
    }
    
}
