<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Receipt</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .mt-4 { margin-top: 16px; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border-bottom: 1px dashed #ccc;
            padding: 6px;
        }

        .summary td {
            border: none;
        }

        .footer {
            margin-top: 20px;
            font-size: 11px;
        }

        .bold {
            font-weight: bold;
        }

        .divider {
            border-bottom: 1px solid #000;
            margin: 10px 0;
        }
    </style>
</head>
<body>

    <h3 class="text-center">Receipt</h3>
    <p class="text-center bold">GO - MART</p>
    <div class="divider"></div>

    @if ($order->customer && $order->customer->is_member)
        <p><strong>Status Member:</strong> Member</p>
        <p><strong>No. HP:</strong> {{ $order->customer->phone ?? '-' }}</p>
        <p><strong>Member Join:</strong> {{ \Carbon\Carbon::parse($order->customer->created_at)->format('d F Y') }}</p>
        <p><strong>Member Point:</strong> {{ $order->customer->points }}</p>
    @else
        <p><strong>Status Member:</strong> Non-Member</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Sub Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderDetails as $detail)
                <tr>
                    <td>{{ $detail->product->name }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>Rp. {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                    <td>Rp. {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary mt-3">
        <tr>
            <td><strong>Total Price</strong></td>
            <td class="text-right">Rp. {{ number_format($order->total_price, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Point Used</strong></td>
            <td class="text-right">{{ $order->points_used ?? 0 }}</td>
        </tr>
        <tr>
            <td><strong>Price after use Point</strong></td>
            <td class="text-right">Rp. {{ number_format($order->final_price, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Change</strong></td>
            <td class="text-right">Rp. {{ number_format($order->change, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer text-right">
        <p>{{ \Carbon\Carbon::parse($order->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i') }} | {{ $order->user->name ?? 'Petugas' }}</p>
    </div>

    <p class="text-center mt-4">Thanks for your Orders!</p>

</body>
</html>
