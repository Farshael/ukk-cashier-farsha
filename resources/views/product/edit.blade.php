    @extends('components.navbar')
    @section('container')
            <div class="page-wrapper">
                <!-- ============================================================== -->
                <!-- Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <div class="page-breadcrumb">
                    <div class="row align-items-center">

        <form action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data" onsubmit="cleanPriceBeforeSubmit()">
            @csrf
            @method('PATCH')

                @if ($errors->any())
                <ul class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                            <li>{{$error }}</li>
                    @endforeach
                </ul>
            @endif
                        <div class="col-6">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 d-flex align-items-center">
                                <li class="breadcrumb-item"><a href="index.html" class="link"><i class="mdi mdi-home-outline fs-4"></i></a></li>
                                <li class="breadcrumb-item active" aria-current="page">Product</li>
                                </ol>
                            </nav>
                            <h1 class="mb-0 fw-bold">Edit Product</h1>
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
                    <!-- ============================================================== -->
                    <!-- Start Page Content -->
                    <!-- ============================================================== -->
                    <!-- Row -->

                        <!-- Column -->
                        <!-- Column -->
                        <div class="col-lg-8 col-xlg-9 col-md-7">
                            <div class="card">
                                <div class="card-body">
                                    <form class="form-horizontal form-material mx-2">
                                        <div class="form-group">
                                            <label class="col-md-12">Product Name</label>
                                            <div class="col-md-12">
                                                <input type="text"
                                                    class="form-control form-control-line" id="name" name="name" value="{{ $product->name }}">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-12">Image</label>
                                            <div class="col-md-12">
                                                <input type="file"
                                                    class="form-control form-control-line" id="image" name="image" accept="image/*">
                                                    @if($product->image)
                                                            <img src="{{ asset('images/' . $product->image) }}" width="100">
                                                        @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-12">Price</label>
                                            <div class="col-md-12">
                                                <input type="text"
                                                    class="form-control form-control-line" id="price" name="price" value=" Rp {{ number_format($product->price, 0, ',', '.')}}" onkeyup="formatRupiah(this)">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-12">Stock</label>
                                            <div class="col-md-12">
                                                <input type="numeric"
                                                    class="form-control form-control-line" id="stock" name="stock" value="{{ $product->stock }}" disabled>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <button  type="submit" class="btn btn-outline-success btn-sm">Update</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Column -->
                    </div>
                    <!-- Row -->
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
            // Format Rupiah saat mengetik
            function formatRupiah(input) {
                let angka = input.value.replace(/[^,\d]/g, '').toString(); // Menghapus selain angka dan koma
                let split = angka.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                input.value = rupiah ? 'Rp ' + rupiah : '';
            }

            // Menghapus simbol Rupiah dan pemisah ribuan sebelum submit
            function cleanPriceBeforeSubmit() {
                let priceInput = document.getElementById("price");
                // Menghapus simbol 'Rp' dan titik pemisah ribuan
                priceInput.value = priceInput.value.replace(/[^\d]/g, ''); // Hanya angka yang tersisa
            }
        </script>

    @endsection
