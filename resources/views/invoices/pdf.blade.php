<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 14px;
        }

        .invoice-box {
            width: 100%;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .details-table,
        .items-table {
            width: 100%;
            text-align: left;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th,
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .items-table th {
            background: #f9f9f9;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 5px;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <div class="header">
            @php
                // 1. Define the path to your image
                $imagePath = public_path('logo/smrlogo.png');

                // 2. Read the file and convert it to Base64
                $imageData = base64_encode(file_get_contents($imagePath));

                // 3. Format it for the HTML src attribute
                $src = 'data:image/png;base64,' . $imageData;
            @endphp

            <img src="{{ $src }}" alt="Company Logo" style="max-width: 200px; margin-bottom: 10px;">
            <p>Order Date: {{ \Carbon\Carbon::parse($order->order_date)->format('d M, Y') }} | Order ID:
                #{{ $order->id }}</p>
        </div>

        <table class="details-table">
            <tr>
                <td style="width: 50%;">
                    <strong>Billed To:</strong><br>
                    {{ $order->customer->name ?? 'Guest' }}<br>
                    {{ $order->customer->email ?? '' }}<br>
                    @if (is_array($order->billing_address))
                        {{ $order->billing_address['street'] ?? '' }}<br>
                        {{ $order->billing_address['city'] ?? '' }}, {{ $order->billing_address['zip'] ?? '' }}
                    @endif
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}<br>
                    <strong>Order Status:</strong> {{ ucfirst($order->current_status) }}
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->OrderItem as $item)
                    <tr>
                        <td>
                            {{ $item->product->name ?? 'Unknown Product' }}

                            @if (!empty($item->variation) && is_array($item->variation))
                                <br>
                                <small style="color: #666;">
                                    {!! collect($item->variation)->filter(fn($value) => !empty($value))->map(fn($value, $key) => ucwords(str_replace('_', ' ', $key)) . ': ' . $value)->implode('<br>') !!}
                                </small>
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->discounted_price ?? $item->price, 2) }}</td>
                        <td>${{ number_format(($item->discounted_price ?? $item->price) * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td style="width: 120px;">${{ number_format($order->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Discount:</strong></td>
                <td>-${{ number_format($order->discount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Delivery Charge:</strong></td>
                <td>+${{ number_format($order->delivery_charge, 2) }}</td>
            </tr>
            <tr>
                <td>
                    <h3><strong>Net Total:</strong></h3>
                </td>
                <td>
                    <h3>${{ number_format($order->net_total, 2) }}</h3>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
