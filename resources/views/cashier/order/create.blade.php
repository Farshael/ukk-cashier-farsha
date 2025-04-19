@extends('components.navbar')

@section('container')
<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row align-items-center">
            <div class="col-6">
                <h1 class="mb-0 fw-bold">Pilih Produk</h1>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            @foreach ($products as $product)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 text-center product-card" data-product-id="{{ $product->id }}">
                        <img src="{{ asset('images/' . $product->image) }}" class="card-img-top w-50 mx-auto mt-3" alt="{{ $product->name }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="text-muted">Stok: <span class="stock">{{ $product->stock }}</span></p>
                            <input type="hidden" class="stock-value" value="{{ $product->stock }}">
                            <p><strong>Rp {{ number_format($product->price, 0, ',', '.') }}</strong></p>
                            <input type="hidden" class="product-price" value="{{ $product->price }}">
                            <div class="d-flex justify-content-center align-items-center mb-3">
                                <button class="btn btn-outline-secondary btn-sm mx-2 minus-btn">-</button>
                                <span class="quantity">0</span>
                                <button class="btn btn-outline-secondary btn-sm mx-2 plus-btn">+</button>
                            </div>
                            <p>Sub Total: <b class="subtotal">Rp 0 ,-</b></p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <form action="{{ route('cashier.order.checkout') }}" method="GET">
            @csrf
            <div id="hidden-inputs"></div>
            <div class="fixed-bottom bg-white shadow p-3 border-top border-warning w-100 d-flex justify-content-center">
                <button type="submit" class="btn btn-outline-primary btn-sm">Next</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const hiddenInputsContainer = document.getElementById('hidden-inputs');

    document.querySelectorAll('.plus-btn').forEach((btn) => {
        btn.addEventListener('click', function () {
            const card = this.closest('.product-card');
            const quantityEl = card.querySelector('.quantity');
            const stockEl = card.querySelector('.stock');
            const price = parseFloat(card.querySelector('.product-price').value);
            const subtotalEl = card.querySelector('.subtotal');

            let quantity = parseInt(quantityEl.textContent);
            let stock = parseInt(stockEl.textContent);

            if (stock > 0) {
                quantity++;
                stock--;

                quantityEl.textContent = quantity;
                stockEl.textContent = stock;
                subtotalEl.textContent = 'Rp ' + (price * quantity).toLocaleString('id-ID');
            } else {
                alert("Stok habis!");
            }
        });
    });

    document.querySelectorAll('.minus-btn').forEach((btn) => {
        btn.addEventListener('click', function () {
            const card = this.closest('.product-card');
            const quantityEl = card.querySelector('.quantity');
            const stockEl = card.querySelector('.stock');
            const price = parseFloat(card.querySelector('.product-price').value);
            const subtotalEl = card.querySelector('.subtotal');

            let quantity = parseInt(quantityEl.textContent);
            let stock = parseInt(stockEl.textContent);

            if (quantity > 0) {
                quantity--;
                stock++;

                quantityEl.textContent = quantity;
                stockEl.textContent = stock;
                subtotalEl.textContent = 'Rp ' + (price * quantity).toLocaleString('id-ID');
            }
        });
    });

    form.addEventListener('submit', function (e) {
        hiddenInputsContainer.innerHTML = '';

        let total = 0;
        let selectedProducts = [];

        document.querySelectorAll('.product-card').forEach((card) => {
            const productId = card.dataset.productId;
            const name = card.querySelector('.card-title').textContent.trim();
            const price = parseFloat(card.querySelector('.product-price').value);
            const quantity = parseInt(card.querySelector('.quantity').textContent);

            if (quantity > 0) {
                hiddenInputsContainer.innerHTML += `
                    <input type="hidden" name="products[${productId}][name]" value="${name}">
                    <input type="hidden" name="products[${productId}][price]" value="${price}">
                    <input type="hidden" name="products[${productId}][quantity]" value="${quantity}">
                    <input type="hidden" name="products[${productId}][id]" value="${productId}">
                `;
                total += price * quantity;
                selectedProducts.push({ name, price, quantity, product_id: productId });
            }
        });

        hiddenInputsContainer.innerHTML += `
            <input type="hidden" name="total" value="${total}">
        `;

        localStorage.setItem('selectedProducts', JSON.stringify(selectedProducts));
        localStorage.setItem('total', total);
    });
});
</script>
@endsection