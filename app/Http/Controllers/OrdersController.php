<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\Detail_Orders;
use App\Models\Product;
use App\Models\Customer;
// use App\Models\User;
// use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;
use Illuminate\Support\Facades\Session;


class OrdersController extends Controller
{
    public function home(Request $request)
{
    $query = Orders::with(['customer', 'user', 'orderDetails.product']);
    $products = Product::all();

    if ($request->search) {
        $query->whereHas('customer', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%');
        });
    }

    $orders = $query->latest()->paginate(10);

    return view('cashier.order.home', compact('orders'));
}

        public function index(Request $request)
        {
            $query = Orders::with(['customer', 'user', 'orderDetails.product']);

            if ($request->search) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                });
            }

            $orders = $query->latest()->paginate(10); // tetap pakai ini!


            // Pastikan data produk dimasukkan ke dalam variabel agar diproses di view
            foreach ($orders as $order) {
                $order->product_items = $order->orderDetails->map(function ($detail) {
                    return [
                        'product' => $detail->product->name ?? 'N/A',
                        'quantity' => $detail->quantity,
                        'price' => $detail->unit_price ?? 0,
                        'subtotal' => $detail->subtotal ?? 0,
                    ];
                });
            }


            return view('orders.index', compact('orders'));
        }




    public function create()
    {
        $products = Product::all();
        return view('cashier.order.create', compact('products'));
    }

    public function checkout(Request $request)
    {
        $products = $request->input('products');

        if (!$products) {
            abort(400, 'Tidak ada produk dipilih.');
        }

        $formattedProducts = [];
        $total = 0;

        foreach ($products as $item) {
            $formattedProducts[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ];
            $total += $item['price'] * $item['quantity'];
        }

        return view('cashier.order.checkout', [
            'products' => $formattedProducts,
            'total' => $total
        ]);
    }



    public function store(Request $request)
    {
        try {
            $products = Product::all();

            // Validasi input
            $validated = $request->validate([
                'status' => 'required|in:non-member,member',
                'phone' => 'required_if:status,member|nullable',
                'total_bayar' => 'required',
                'total_price' => 'required|numeric|min:0',
                'products' => 'required|array',
                'products.*.id' => 'required|integer|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
            ]);

            // Konversi total_bayar ke angka (karena dia 'Rp 150.000' dari form)
            $amountPaid = (int) preg_replace('/[^\d]/', '', $request->total_bayar);

            $isMemberInput = !empty($request->phone);

            if (!$isMemberInput) {
                // Non-member flow
                $data = [
                    'is_new' => false,
                    'is_member' => false,
                    'phone' => null,
                    'name' => null,
                    'total_price' => $request->total_price,
                    'amount_paid' => $amountPaid,
                    'products' => $request->products,
                ];

                $customer = Customer::create([
                    'name' => 'Non-Member',
                    'phone' => null,
                    'points' => 0,
                    'is_member' => false,
                ]);

                return $this->finalizeTransaction($data, $customer);
            }

            // Member flow
            $customer = Customer::firstOrNew(['phone' => $request->phone]);
            $isNew = !$customer->exists;

            session([
                'member_data' => [
                    'is_new' => $isNew,
                    'phone' => $request->phone,
                    'total_price' => $request->total_price,
                    'amount_paid' => $amountPaid,
                    'products' => $request->products,
                    'name' => $customer?->name,
                    'use_points' => $request->has('use_points'), // <-- TAMBAHKAN INI
                ]
            ]);

            return redirect()->route('cashier.order.verifyMemberForm');

        } catch (\Throwable $e) {
            // Tampilkan error detail di dev mode
            dd('ERROR:', $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
        }
    }


    public function verifyMemberForm()
    {
        $data = session('member_data');
        if (!$data) {
            return redirect()->route('cashier.order.create')->with('error', 'Data transaksi tidak ditemukan.');
        }


        $productDetails = collect($data['products'])->map(function ($item) {
            $product = Product::find($item['id']);
            return [
                'name' => $product->name,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'subtotal' => $product->price * $item['quantity'],
            ];
        });

        $customer = Customer::where('phone', $data['phone'])->first();
        $currentPoints = $customer?->points ?? 0;
        $earnedPoints = !$data['is_new'] ? floor($data['total_price'] * 0.01) : 0;
        $pointsUsed = (!empty($data['use_points']) && $currentPoints > 0)
                        ? min($currentPoints, $data['total_price'])
                        : 0;

        $expectedPoints = $currentPoints - $pointsUsed + $earnedPoints;

        $expectedPoints += $earnedPoints;



        return view('cashier.order.member', [
            'transactionData' => $data,
            'productDetails' => $productDetails,
            'isReturningCustomer' => !$data['is_new'],
            'expectedPoints' => $expectedPoints,
            'customer' => $customer ?? null, // amanin kalau null
            'pointsUsed' => $pointsUsed, // <--- INI DIAAA
        ]);
    }


    public function verifyMember(Request $request)
    {
        $data = session('member_data');
        if (!$data) {
            return redirect()->route('cashier.order.create')->with('error', 'Data tidak tersedia.');
        }
        $pointsUsed = $request->input('use_points_amount');
        $finalPrice = $request->input('final_price');


        $customer = $data['is_new']
            ? Customer::create([
                'name' => $request->name,
                'phone' => $data['phone'],
                'points' => 0,
                'is_member' => true,
            ])
            : Customer::where('phone', $data['phone'])->first();

        if ($request->has('use_points')) {
            $data['use_points'] = true;
        }

        return $this->finalizeTransaction($data, $customer);
    }


   protected function finalizeTransaction(array $data, Customer $customer)
{
    $totalPrice = $data['total_price'];
    $amountPaid = $data['amount_paid'];
    $isUsePoints = !empty($data['use_points']);  // Memeriksa apakah poin digunakan

    $oldPoints = $customer->points;
    $earnedPoints = $customer->is_member ? floor($totalPrice * 0.01) : 0;  // Poin yang diperoleh
    $discount = 0;

    $pointsUsed = $isUsePoints ? min($oldPoints, $totalPrice) : 0;  // Menghitung poin yang digunakan jika ada

    if ($isUsePoints) {
        $discount = $pointsUsed;
        $customer->points = $oldPoints - $discount;  // Mengurangi poin jika digunakan
    }

    $finalPrice = $totalPrice - $discount;

    // Poin yang diperoleh selalu ditambahkan meskipun tidak ada diskon
    $customer->points += $earnedPoints;

    $customer->save();  // Menyimpan perubahan poin pelanggan

    // Membuat transaksi order baru
    $order = Orders::create([
        'customer_id'   => $customer->id,
        'total_price'   => $totalPrice,
        'final_price'   => $finalPrice,
        'amount_paid'   => $amountPaid,
        'change'        => $amountPaid - $finalPrice,
        'points_used'   => $pointsUsed,
        'user_id'       => auth()->id(),
    ]);

    // Menyimpan detail produk untuk setiap produk yang dibeli
    foreach ($data['products'] as $item) {
        $product = Product::find($item['id']);
        Detail_Orders::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => $item['quantity'],
            'unit_price' => $product->price,
            'subtotal'   => $product->price * $item['quantity'],
        ]);

        // Kurangi stok produk setelah dibeli
        if ($product) {
            $product->decrement('stock', $item['quantity']);
        }
    }

    return redirect()->route('cashier.order.receipt', $order->id);
}







public function receipt($orderId)
{
    $order = Orders::with(['orderDetails.product', 'user'])->findOrFail($orderId);
    $customer = $order->customer;
    $details = $order->orderDetails;

    $totalPrice = $details->sum('subtotal');
    $finalPrice = $order->final_price;
    $discount = $order->points_used ?? 0;
    $amountPaid = $order->amount_paid ?? 0;
    $change = max(0, $amountPaid - $finalPrice);

    // Poin yang didapat hanya kalau tidak menggunakan poin untuk diskon
    $earnedPoints = floor($order->total_price * 0.01);  // <-- pakai total_price

    // Perkirakan poin sebelum transaksi
    $finalPoints = $customer->points;
    $oldPoints = $discount > 0
        ? $finalPoints + $discount
        : $finalPoints - $earnedPoints;

    return view('cashier.order.receipt', [
        'order' => $order,
        'customer' => $customer,
        'details' => $details,
        'total_price' => $totalPrice,
        'final_price' => $finalPrice,
        'discount' => $discount,
        'change' => $change,
        'earnedPoints' => $earnedPoints,
        'oldPoints' => $oldPoints,
        'finalPoints' => $finalPoints,
    ]);
}







    public function print($id)
    {

        $order = Orders::with(['customer', 'orderDetails.product', 'user'])->findOrFail($id);
        $pdf = Pdf::loadView('print', compact('order'))->setPaper('A5');
        return $pdf->download("receipt-{$order->id}.pdf");
    }

    public function export()
{
    return Excel::download(new OrdersExport, 'orders.xlsx');
}
}
