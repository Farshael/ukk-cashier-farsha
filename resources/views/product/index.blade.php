@extends('components.navbar')
@section('container')

        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 d-flex align-items-center">
                              <li class="breadcrumb-item"><a href="/" class="link"><i class="mdi mdi-home-outline fs-4"></i></a></li>
                              <li class="breadcrumb-item active" aria-current="page">Product</li>
                            </ol>
                          </nav>
                        <h1 class="mb-0 fw-bold">Product</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
                            <a href="{{ route('product.create') }}"class="btn btn-outline-success btn-sm"
                                ">Create Product</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">

                @if(Session::get('success'))
                <div class="alert alert-success"> {{ Session::get('success') }} </div>
                @endif
                @if(Session::get('deleted'))
                <div class="alert alert-warning"> {{ Session::get('deleted') }} </div>
                @endif
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col"></th>
                                                <th scope="col">Product Name</th>
                                                <th scope="col">Price</th>
                                                <th scope="col">Stock</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $no = 1; @endphp
                                            @foreach ($products as $item)
                                            <tr>
                                                <td>{{ $no++ }}</td>
                                                <td><img src="{{ asset('images/' . $item->image) }}" alt="Product Image" width="150">
                                                </td>
                                                <td>{{ $item['name'] }}</td>
                                                <td>Rp {{ number_format($item['price'], 0, ',', '.')}} </td>
                                                <td>{{ $item['stock'] }}</td>
                                                <td>
                                                    <a href="{{ route('product.edit', $item->id) }}" class="btn btn-success text-white btn-sm d-inline-block me-2" title="Edit Product">Edit</a>
                                                    <button class="btn btn-primary text-white btn-sm d-inline-block me-2" onclick="adjustStock({{ $item->id }}, '{{ $item->name }}', {{ $item->stock }})" title="Update Stock">Update Stok</button>
                                                    <form action="{{ route('product.delete', $item->id) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-danger text-white btn-sm" type="submit" onclick="confirmDelete({{ $item->id }})" title="Delete Product">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>


                <!-- ============================================================== -->
                <!-- End PAge Content -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Right sidebar -->
                <!-- ============================================================== -->
                <!-- .right-sidebar -->
                <!-- ============================================================== -->
                <!-- End Right sidebar -->
                <!-- ============================================================== -->
            </div>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->

            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->

    <script>
        function confirmDelete(productId) {
            Swal.fire({
                title: "Are you sure?",
                text: "This action cannot be undone!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + productId).submit();
                }
            });
        }
    </script>
    @if(session('deleted'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '{{ session('deleted') }}',
            showConfirmButton: true
        });
    </script>
    @endif

    <script>

        function adjustStock(productId, productName, currentStock) {
    Swal.fire({
        title: 'Update Stock',
        html: `
            <label class="swal2-label">Product</label>
            <input class="swal2-input" id="product-name" value="${productName}" disabled>
            <br>
            <label class="swal2-label">Stock</label>
            <input type="number" class="swal2-input" id="product-stock" value="${currentStock}" min="0">
        `,
        showCancelButton: true,
        confirmButtonText: 'Update',
        cancelButtonText: 'Cancel',
        focusConfirm: false,
        preConfirm: () => {
            const newStock = document.getElementById('product-stock').value;
            if (!newStock || newStock < 0) {
                Swal.showValidationMessage('The stock must be greater than or equal to 0!');
            }
            return { stock: newStock };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/product/${productId}/adjust-stock`, { // Gunakan URL dengan ID produk
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ stock: result.value.stock }) // Kirim data stok
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Updated!', 'Stok berhasil diperbarui.', 'success')
                    .then(() => location.reload()); // Refresh halaman
                } else {
                    Swal.fire('Error!', 'Gagal memperbarui stok.', 'error');
                }
            })
            .catch(error => Swal.fire('Error!', 'Terjadi kesalahan jaringan.', 'error'));
        }
    });
}

    </script>


@endsection
