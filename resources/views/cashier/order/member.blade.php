        @extends('components.navbar')

        @section('container')
        <div class="container mt-5 d-flex justify-content-center">
            <div class="card shadow-sm w-100" style="max-width: 600px;">
                <div class="card-body">
            <h3>Order Summary</h3>

            @if($transactionData && $productDetails)
                <ul class="list-group mb-3">
                    @foreach ($productDetails as $product)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $product['name'] }} x {{ $product['quantity'] }}
                            <span>Rp {{ number_format($product['subtotal'], 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
                

                <h5>Total: Rp {{ number_format($transactionData['total_price'], 0, ',', '.') }}</h5>

                <form action="{{ route('cashier.order.verifyMember') }}" method="POST" class="mt-3">
                    @csrf

                    @if ($isReturningCustomer)
                        <p class="mt-3">Member: <strong>{{ $customer->name }}</strong></p>
                        <p>Poin saat ini: {{ $customer->points }}</p>
                    @else
                        <p class="mt-3">New Member, Name Member:</p>
                        <div class="mb-3">
                            <label for="name" class="form-label">Member Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                    @endif

                    
                    {{-- Gunakan poin jika member lama dan punya poin --}}
                    @if ($isReturningCustomer && $customer->points > 0)
                    <div class="mb-3">
                        <label for="use_points" class="form-label">Use Point</label>
                        <div class="form-check">
                            <input type="checkbox" name="use_points" class="form-check-input" id="use_points"
                                onchange="toggleUsePoints()">
                            <label class="form-check-label" for="use_points">
                                Use Point (Rp {{ number_format($customer->points, 0, ',', '.') }})
                            </label>
                        </div>
                    </div>
                @endif                

                        <div class="mb-3">
                            <label for="use_points_amount" class="form-label">Point Used</label>
                            <input type="text" id="use_points_amount" name="use_points_amount" class="form-control" value="0" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Total after Discount</label>
                            <input type="text" id="discounted_total" class="form-control" 
                                value="Rp {{ number_format($transactionData['total_price'], 0, ',', '.') }}" readonly>
                        </div>

                        <script>
                            

                            function toggleUsePoints() {
                                const checkbox = document.getElementById("use_points");
                                const usePointsAmount = document.getElementById("use_points_amount");
                                const discountedTotal = document.getElementById("discounted_total");
                                const total = {{ $transactionData['total_price'] }};
                                const points = {{ $customer ? $customer->points : 0 }};

                                if (checkbox.checked) {
                                    let usedPoints = Math.min(points, total);
                                    usePointsAmount.value = usedPoints;
                                    let newTotal = total - usedPoints;
                                    discountedTotal.value = "Rp " + newTotal.toLocaleString('id-ID');
                                } else {
                                    usePointsAmount.value = 0;
                                    discountedTotal.value = "Rp " + total.toLocaleString('id-ID');
                                }
                            }
                        </script>
                    

                    <button type="submit" class="btn btn-outline-success btn-sm">Payment Confirmation</button>
                </form>
            @else
                <p class="text-danger">Transaction Data not Found.</p>
            @endif
        </div>
        </div>
        </div>
        @endsection
