<?php

namespace App\Exports;

use App\Models\Orders;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Detail_Orders;
use Maatwebsite\Excel\Concerns\FromQuery;

class OrdersExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Detail_Orders::with(['order.customer', 'order.user', 'product']);
    }

    public function headings(): array
    {
        return [
            'No', 'Customer Name', 'Date Sale', 'Final Price', 'Made By', 'Member Status', 'Phone', 'Points',
            'Product Name', 'Quantity', 'Price', 'Subtotal'
        ];
    }

    public function map($detail): array
    {
        static $no = 1;

        return [
            $no++,
            $detail->order->customer->name ?? '-',
            $detail->order->created_at->format('Y-m-d H:i'),
            $detail->order->final_price,
            $detail->order->user->name ?? '-',
            $detail->order->customer?->is_member ? 'Member' : 'Non-Member',
            $detail->order->customer->phone ?? '-',
            $detail->order->customer->points ?? 0,
            $detail->product->name ?? 'N/A',
            $detail->quantity,
            $detail->unit_price,
            $detail->subtotal,
        ];
    }
}
