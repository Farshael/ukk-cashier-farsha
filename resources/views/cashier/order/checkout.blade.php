@extends('components.navbar')

@section('container')
<div class="container mt-5 d-flex justify-content-center">
    <div class="card shadow-sm w-100" style="max-width: 600px;">
        <div class="card-body">
            <h4 class="card-title mb-4 text-center">Checkout</h4>

            {{-- Produk yang dipilih --}}
            <h5>Seleced Product</h5>
            <ul class="list-group mb-3">
                @foreach ($products as $product)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $product['name'] }}
                        <span>Rp {{ number_format($product['price'], 0, ',', '.') }} x {{ $product['quantity'] }}</span>
                    </li>
                @endforeach
            </ul>
            <h5>Total Belanja: <span id="total-display">Rp {{ number_format($total, 0, ',', '.') }}</span></h5>

            {{-- Form Checkout --}}
            <form id="checkout-form" action="{{ route('cashier.order.store') }}" method="POST" class="mt-4">
                @csrf

                {{-- Status Member --}}
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="non-member">Non-Member</option>
                        <option value="member">Member</option>
                    </select>
                </div>

                {{-- No Telepon (jika member) --}}
                <div class="mb-3 d-none" id="phone-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>

                {{-- Total Bayar --}}
                <div class="mb-3">
                    <label for="total_bayar" class="form-label">Price Total</label>
                    <input type="text" class="form-control" id="total_bayar" name="total_bayar" required>
                </div>

                {{-- Hidden Inputs --}}
                <input type="hidden" id="real_total" name="total_price" value="{{ $total }}">

                @foreach ($products as $index => $product)
                    <input type="hidden" name="products[{{ $index }}][id]" value="{{ $product['id'] }}">
                    <input type="hidden" name="products[{{ $index }}][name]" value="{{ $product['name'] }}">
                    <input type="hidden" name="products[{{ $index }}][price]" value="{{ $product['price'] }}">
                    <input type="hidden" name="products[{{ $index }}][quantity]" value="{{ $product['quantity'] }}">
                @endforeach

                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary btn-sm">Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusSelect = document.getElementById('status');
    const phoneGroup = document.getElementById('phone-group');
    const form = document.getElementById('checkout-form');
    const totalBayarInput = document.getElementById('total_bayar');
    const realTotal = parseInt(document.getElementById('real_total').value);

    // Tampilkan/hide input telepon berdasarkan status
    statusSelect.addEventListener('change', function () {
        if (this.value === 'member') {
            phoneGroup.classList.remove('d-none');
            phoneGroup.querySelector('input').setAttribute('required', 'required');
        } else {
            phoneGroup.classList.add('d-none');
            phoneGroup.querySelector('input').removeAttribute('required');
        }
    });

    // Trigger saat halaman pertama kali load
    statusSelect.dispatchEvent(new Event('change'));

    // Validasi sebelum submit
    form.addEventListener('submit', function (e) {
        const rawValue = totalBayarInput.value.replace(/[^\d]/g, '');
        const totalBayar = parseInt(rawValue || 0);

        if (isNaN(totalBayar) || totalBayar < realTotal) {
            alert(`Total bayar harus minimal Rp ${realTotal.toLocaleString('id-ID')}`);
            e.preventDefault(); // Stop form submit jika invalid
        }
    });

    // Format angka input jadi "Rp xxx"
    totalBayarInput.addEventListener('input', function () {
        let angka = this.value.replace(/[^\d]/g, '');
        if (angka) {
            this.value = 'Rp ' + parseInt(angka).toLocaleString('id-ID');
        } else {
            this.value = '';
        }
    });
});
</script>
@endsection
