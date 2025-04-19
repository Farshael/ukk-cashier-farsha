@extends('components.navbar')

@section('container')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 offset-md-1 offset-lg-2">
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5>{{ $customer->id ?? 'N/A' }}</h5>
                        @if($customer->is_member)
                            <p>MEMBER SINCE : {{ $customer->created_at->format('d F Y') }}</p>
                            <p>MEMBER POINT : {{ $customer->points ?? 0}}</p>
                        @else
                            <p>MEMBER SINCE : NON- MEMBER</p>
                            <p>MEMBER POIN : 0</p>
                        @endif
                    </div>
                    <div>
                        <h5>Invoice - #{{ $order->id ?? 'N/A' }}</h5>
                        <h6>{{ $order->created_at->format('d F Y') ?? 'N/A' }}</h6>
                    </div>
                </div>

                <!-- TABEL DETAIL PRODUK -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($details as $detail)
                        <tr>
                            <td>{{ $detail->product->name ?? 'Produk' }}</td>
                            <td>Rp {{ number_format($detail->unit_price ?? 0, 0, ',', '.') }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <h6>Point Used</h6>
                        <p>{{ $discount }}</p>

                    </div>
                    <div class="col-md-4">
                        <h6>Cashier</h6>
                        <p>{{ $order->user->name ?? 'Staff' }} </p>
                    </div>
                    <div class="col-md-4">
                        <h6>Change</h6>
                        <p>Rp {{ number_format($change, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 p-3 bg-light">
                    <h5>Final Price</h5>
                    <h5 class="text-dark">Rp {{ number_format($total_price, 0, ',', '.') }}</h5>
                    <h5 class="text-danger">Rp {{ number_format($final_price, 0, ',', '.') }}</h5>
                </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a class="btn btn-outline-success btn-sm" href="{{ route('print', ['order' => $order->id]) }}">Download</a>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('cashier.order.home') }}">Back</a>
                    </div>                    
            </div>
        </div>
    </div>
</div>
@endsection
