<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Confirmation</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f9f9f9;
            padding: 20px;
        }

        .invoice-container {
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 8px;
            padding: 20px;
            width: 100%;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .invoice-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .invoice-header p {
            font-size: 16px;
            color: #555555;
        }

        .invoice-from-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .invoice-from, .invoice-details {
            width: 48%;
        }

        .invoice-from {
            padding-right: 20px;
        }

        .invoice-from h1, .invoice-details h1 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333333;
        }

        .invoice-from p, .invoice-details p {
            font-size: 16px;
            color: #555555;
            margin-bottom: 5px;
        }

        section {
            margin-bottom: 20px;
        }

        h2 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333333;
        }

        p {
            font-size: 16px;
            color: #555555;
            margin-bottom: 5px;
        }

        strong {
            color: #000000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
            border: 1px solid #dddddd;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        td {
            background-color: #ffffff;
        }

        #code {
            font-size: 30px;
            color: blue;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <header class="invoice-header">
            <h1>Subscription Confirmation</h1>
            <p>Thank you for subscribing to our {{ $plan->name }} plan.</p>
        </header>
        <div class="invoice-from-details">
            <section class="invoice-from">
                <h1>From</h1>
                <p id="code">CODE BRICKS</p>
                <p>Mall OF Sargodha</p>
                <p>4th Floor 35-A</p>
            </section>
            <section class="invoice-details">
                <h1>Invoice Details</h1>
                {{-- <p><strong>Stripe Customer ID:</strong> {{ $subscriptionDetail->stripe_customer_id }}</p> --}}
                <p><strong>Date of issue:</strong> {{ $date }}</p>
                <p>This is a {{ $plan->name }} plan</p>
            </section>
        </div>
        <section class="billing-details">
            <h1>Billing Details</h1><br>
            <p>Name:{{ $user->name }}</p>
            <p>Email:{{ $user->email }}</p>
            <p>From:hello@example.com </p>
        </section>
        <section class="summary">
            <h1>Summary</h1>
            <p><strong>Total usage charges:</strong> ${{ $plan->amount }}</p>
            <p><strong>Total Paid:</strong> ${{ $plan->amount }}</p>
        </section>
        <section class="product-usage">
            <h2>Product Usage Charges</h2>
            <p>Detailed usage information is available here</p>
            <table>
                <thead>
                    <tr>
                        <th>Plan</th>
                        {{-- <th>Interval</th> --}}
                        <th>Start</th>
                        <th>End</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $plan->name }}</td>
                        {{-- <td>{{ ucfirst($plan->plan_interval) }}</td> --}}
                        <td>{{ $date }}</td>
                        <td>{{ $plan->name }}</td>
                        <td>${{ $plan->amount }}</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
