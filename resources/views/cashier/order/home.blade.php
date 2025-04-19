@extends('components.navbar')

@section('container')

<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row align-items-center">
            <div class="col-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 d-flex align-items-center">
                        <li class="breadcrumb-item"><a href="/" class="link"><i class="mdi mdi-home-outline fs-4"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Orders</li>
                    </ol>
                </nav>
                <h1 class="mb-0 fw-bold">Orders</h1>
            </div>
            <div class="col-6 text-end d-flex gap-2 justify-content-end">
                <form method="GET" action="{{ route('cashier.order.home') }}" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search customer..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Search</button>
                </form>
                <a href="{{ route('cashier.order.export') }}" class="btn btn-outline-success btn-sm">Export (.xlsx)</a>
                <a href="{{ route('cashier.order.create') }}" class="btn btn-primary btn-sm text-white">Create Orders</a>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-3">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer Name</th>
                                <th>Date Sale</th>
                                <th>Price Total</th>
                                <th>Made By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = ($orders->currentPage() - 1) * $orders->perPage() + 1; @endphp
                            @foreach ($orders as $item)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $item->customer->name ?? '-' }}</td>
                                <td>{{ $item->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }} WIB</td>
                                <td>Rp{{ number_format($item->final_price, 0, ',', '.') }}</td>
                                <td>{{ $item->user->name ?? '-' }}</td>
                                <td>
                                    @php
                                    $products = $item->orderDetails->map(function ($d) {
                                        return [
                                            'product' => $d->product->name ?? 'N/A',
                                            'quantity' => $d->quantity,
                                            'price' => $d->unit_price ?? 0,
                                            'subtotal' => $d->subtotal ?? 0,
                                        ];
                                    });
                                    @endphp

                                    <button class="btn btn-success text-white btn-sm view-btn"
                                        data-order='@json($item)'
                                        data-products='@json($products)'>
                                        View
                                    </button>

                                    <a href="{{ route('print', ['order' => $item->id]) }}" class="btn btn-info btn-sm text-white">Download</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    <nav>
                        <ul class="pagination justify-content-center pagination-sm">
                            {{ $orders->withQueryString()->links() }}
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Order Details -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Customer Info</h6>
                            <p><strong>Status: </strong><span id="memberStatus"></span></p>
                            <p><strong>Phone: </strong><span id="memberPhone"></span></p>
                            <p><strong>Points: </strong><span id="memberPoints"></span></p>
                            <p><strong>Joined: </strong><span id="memberJoined"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Order Info</h6>
                            <p><strong>Created At: </strong><span id="createdAt"></span></p>
                            <p><strong>Made By: </strong><span id="madeBy"></span></p>
                        </div>
                    </div>

                    <h6>Product List</h6>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="productList"></tbody>
                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        <h5>Total: <span id="totalPrice"></span></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));

        document.querySelectorAll('.view-btn').forEach(button => {
            button.addEventListener('click', function () {
                const order = JSON.parse(this.dataset.order);
                const products = JSON.parse(this.dataset.products);

                // Customer Info
                document.getElementById('memberStatus').textContent = order.customer && order.customer.is_member ? 'Member' : 'Non-Member';
                document.getElementById('memberPhone').textContent = order.customer?.phone ?? '-';
                document.getElementById('memberPoints').textContent = order.customer?.points ?? '-';
                document.getElementById('memberJoined').textContent = order.customer?.created_at
                    ? new Date(order.customer.created_at).toLocaleDateString()
                    : '-';

                // Order Info
                const { DateTime } = luxon;

document.getElementById('createdAt').textContent = DateTime.fromISO(order.created_at)
    .setZone('Asia/Jakarta') // Set ke zona waktu Jakarta
    .toFormat('yyyy-MM-dd HH:mm'); // Format sesuai dengan 'Y-m-d H:i'
                document.getElementById('madeBy').textContent = order.user?.name ?? '-';

                // Product List
                const productList = document.getElementById('productList');
                productList.innerHTML = '';
                products.forEach(p => {
                    productList.innerHTML += `
                        <tr>
                            <td>${p.product}</td>
                            <td>${p.quantity}</td>
                            <td>Rp ${parseInt(p.price).toLocaleString('id-ID')}</td>
                            <td>Rp ${parseInt(p.subtotal).toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });

                // Total
                document.getElementById('totalPrice').textContent = `Rp ${parseInt(order.final_price).toLocaleString('id-ID')}`;

                modal.show();
            });
        });
    });
</script>
@endpush

<script src="https://cdn.jsdelivr.net/npm/luxon@2.3.0/build/global/luxon.min.js"></script>

@endsection
