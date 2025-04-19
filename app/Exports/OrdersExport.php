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

        // Group orders by order_id
        static $groupedDetails = [];

        $orderId = $detail->order_id;

        if (!isset($groupedDetails[$orderId])) {
            $groupedDetails[$orderId] = [
                'customer_name' => $detail->order->customer->name ?? '-',
                'date_sale' => $detail->order->created_at->format('Y-m-d H:i'),
                'final_price' => $detail->order->final_price,
                'made_by' => $detail->order->user->name ?? '-',
                'member_status' => $detail->order->customer?->is_member ? 'Member' : 'Non-Member',
                'phone' => $detail->order->customer->phone ?? '-',
                'points' => $detail->order->customer->points ?? 0,
                'products' => [],
                'total_quantity' => 0,
                'total_subtotal' => 0,
            ];
        }

        $groupedDetails[$orderId]['products'][] = [
            'product_name' => $detail->product->name ?? 'N/A',
            'quantity' => $detail->quantity,
            'unit_price' => $detail->unit_price,
            'subtotal' => $detail->subtotal,
        ];

        $groupedDetails[$orderId]['total_quantity'] += $detail->quantity;
        $groupedDetails[$orderId]['total_subtotal'] += $detail->subtotal;

        // If this is the last product in the order, map the details
        $mappedData = [];
        foreach ($groupedDetails as $orderDetails) {
            foreach ($orderDetails['products'] as $product) {
                $mappedData[] = [
                    $no++, // Incrementing row number
                    $orderDetails['customer_name'],
                    $orderDetails['date_sale'],
                    $orderDetails['final_price'],
                    $orderDetails['made_by'],
                    $orderDetails['member_status'],
                    $orderDetails['phone'],
                    $orderDetails['points'],
                    implode(", ", array_column($orderDetails['products'], 'product_name')), // Concatenate product names
                    $orderDetails['total_quantity'], // Total quantity
                    $product['unit_price'], // Unit price of first product (or take average if necessary)
                    $orderDetails['total_subtotal'], // Total subtotal of order
                ];
            }
        }

        return $mappedData;
    }
}
