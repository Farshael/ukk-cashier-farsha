<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Orders;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function loginAuth(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        // Cocokkan langsung password tanpa hash

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // Jika login berhasil, alihkan ke dashboard
            return redirect()->route('dashboard')->with('loginSuccess', 'Success Login!');
        } else {
            // Jika login gagal, tampilkan pesan error
            return redirect()->back()->with('error', 'Login Failed!');
        }
    }
    public function logout()
    {
       Auth::logout();
       return redirect()->route('login')->with('logout', 'Successfully logout!');
    }

    public function index()
    {
        $users = User::all();
        return view('user.index', compact('users'));
    }

/*************  ✨ Windsurf Command 🌟  *************/
    public function dashboard()
{
    // Get the current user
    $user = Auth::user();

    // If the user is an admin, show the admin dashboard
    // Jika role admin
    if ($user->role == 'admin') {
        // Calculate the start and end dates for this month
        // We want to show the sales for the entire month
        $startDate = Carbon::now()->startOfMonth();  // Start of the month
        $endDate = Carbon::now()->endOfMonth();      // End of the month
        // Rentang waktu bulan ini untuk chart penjualan
        $startDate = Carbon::now()->startOfMonth();  // Mulai dari hari pertama bulan ini
        $endDate = Carbon::now()->endOfMonth();      // Sampai hari terakhir bulan ini

        // Get the data for the chart
        // We want to show the number of orders per day
        // for the entire month
        // Ambil data chart untuk admin (penjualan per hari selama sebulan)
        $chartData = DB::table('orders')
            ->select(
                // We want to group by the date, so we can show
                // the number of orders per day
                DB::raw('DATE(created_at) as date'),
                // We want to count the number of orders per day
                DB::raw('COUNT(*) as total')
            )
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        // Get the data for the pie chart
        // Data untuk pie chart (penjualan produk) selama bulan ini
        $productDataRaw = DB::table('orders')
            ->join('detail_orders', 'orders.id', '=', 'detail_orders.order_id')
            ->join('products', 'detail_orders.product_id', '=', 'products.id')
            ->selectRaw('products.name as product_name, SUM(detail_orders.quantity) as total_sold')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('product_name')
            ->get();

        // Total penjualan untuk kalkulasi persentase
        $totalAll = $productDataRaw->sum('total_sold');
        $productData = $productDataRaw->map(function ($item) use ($totalAll) {
            return [
                'name' => $item->product_name,
                'y' => $totalAll > 0 ? round(($item->total_sold / $totalAll) * 100, 2) : 0,
            ];
        });

        return view('dashboard', [
            'chartData' => $chartData,
            'productData' => $productData,
        ]);
    }

    // Jika role cashier
    elseif ($user->role == 'cashier') {
        $todaySales = Orders::whereDate('created_at', Carbon::today())->count();

        // Total transaksi member hari ini
        $memberSales = Orders::whereDate('created_at', Carbon::today())
        ->whereHas('customer', function ($query) {
            $query->where('is_member', true);
        })
        ->count();

    // Total transaksi non-member hari ini
    $nonMemberSales = Orders::whereDate('created_at', Carbon::today())
        ->where(function ($query) {
            $query->whereNull('customer_id')
                  ->orWhereHas('customer', function ($q) {
                      $q->where('is_member', false);
                  });
        })
        ->count();


        return view('dashboard', compact('todaySales', 'memberSales', 'nonMemberSales'));
    }

    // Jika role tidak dikenali
    return redirect()->route('permission');
}
/*******  47ee2653-3f97-478c-bb7a-63c713674919  *******/



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('user.create');
    }

    /**
     * Store a newly created resource in storage.
     */

        public function store(Request $request)
        {
            $request->validate([
                'name' => 'required',
                'email' => 'required',
                'password' => 'required',
                'role' => 'required',
            ]);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password), // Hash password
                'role' => $request->role,
            ]);

            return redirect()->route('user.index')->with('success', 'Success create user!');
        }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::findorFail($id);
        return view('user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'role' => 'required',
        ]);

        $user = User::findOrFail($id);


        $user->update([
           'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
        ]);


        return redirect()->route('user.index')->with('success', 'Success update user!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('user.index')->with('deleted', 'User has been Deleted!');
    }
}
