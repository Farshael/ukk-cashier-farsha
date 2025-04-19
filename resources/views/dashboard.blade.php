@extends('components.navbar')

@section('container')
<div class="page-wrapper">
    <!-- Konten Dashboard -->
    <div class="container">
        @if (Session::has('loginSuccess'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil!',
                text: '{{ Session::get('loginSuccess') }}',
                showConfirmButton: true,

            });
        </script>
    @endif

    @if (Session::has('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal!',
                text: '{{ Session::get('error') }}',
                showConfirmButton: true,

            });
        </script>
    @endif

        @if(Auth::user()->role == 'admin')
        <div class="card">
            <div class="card-body">
            <h1>Dashboard Admin</h1>
            <h2 class="mb-4">Welcome, Administrator!</h2>

            <div class="row">
                <!-- Chart Jumlah Penjualan -->
                <div class="col-12 col-md-7">
                    <h5 class="fw-bold">Total Sales</h5>
                    <canvas id="salesChart" width="400" height="200"></canvas>
                </div>

                <!-- Chart Persentase Penjualan Produk -->
                <div class="col-12 col-md-5">
                    <h5 class="fw-bold">Product Sales Percentage</h5>
                    <canvas id="productChart" width="400" height="200"></canvas>
                </div>
            </div>

            @if (Session::has('loginSuccess'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil!',
                    text: '{{ Session::get('loginSuccess') }}',
                    showConfirmButton: true,
                });
            </script>
        @endif

        @if (Session::has('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal!',
                    text: '{{ Session::get('error') }}',
                    showConfirmButton: true,
                });
            </script>
        @endif
        @elseif(Auth::user()->role == 'cashier')
        <div class="card">
            <div class="card-body">
            <h1>Dashboard Cashier</h1>
            <h2 class="mb-4">Welcome, Cashier!</h2>

            <div class="card">
                <div class="card-body text-center">
                    <h3 class="card-title">Total Sales Today</h3>
                    <h2 style="font-size: 3rem;">{{ $todaySales }}</h2>
                    <p class="mb-0 text-muted">Total number of sales that occurred today.</p>
                  <small>Last Updated: {{ now()->setTimezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</small>
                </div>
            </div>

        @else
            <p class="alert alert-danger">Role tidak dikenali.</p>
        @endif
    </div>
</div>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
    @if(Auth::user()->role == 'admin')
        <!-- Hanya pakai Chart.js untuk admin -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Sales Chart (Bar Chart)
                const salesLabels = @json($chartData->pluck('date'));
                const salesData = @json($chartData->pluck('total'));

                const ctxSales = document.getElementById('salesChart').getContext('2d');
                new Chart(ctxSales, {
                    type: 'bar',
                    data: {
                        labels: salesLabels,
                        datasets: [{
                            label: 'Total Sales',
                            data: salesData,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: { y: { beginAtZero: true } }
                    }
                });

                // Product Sales (Pie Chart)
                const productLabels = @json($productData->pluck('name'));
                const productData = @json($productData->pluck('y'));

                const ctxProduct = document.getElementById('productChart').getContext('2d');
                new Chart(ctxProduct, {
                    type: 'pie',
                    data: {
                        labels: productLabels,
                        datasets: [{
                            data: productData,
                            backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A1', '#FFD700'],
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });
            });
        </script>
         <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endif
@endpush
