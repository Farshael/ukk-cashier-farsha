<?php
use Illuminate\Http\Request;
use App\Models\Customer;

Route::get('/get-member', function (Request $request) {
    $customer = Customer::where('phone', $request->no_telp)->first();
    return response()->json($customer);
});
