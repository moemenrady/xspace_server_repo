<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إيصال X Space</title>
    <style>
        @page {
            size: 80mm auto; /* رول 80mm */
            margin: 5mm;
        }
        body {
            font-family: "Tahoma", sans-serif;
            font-size: 14px;
            color: #000;
            direction: rtl;
            margin: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            letter-spacing: 1px;
        }
        .header p {
            margin: 2px 0;
            font-size: 12px;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .items th, .items td {
            padding: 4px 2px;
            text-align: center;
        }
        .items th {
            border-bottom: 1px dashed #000;
            font-size: 13px;
        }
        .items td {
            font-size: 12px;
        }
        .total {
            border-top: 2px dashed #000;
            margin-top: 5px;
            padding-top: 5px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 12px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }
        .highlight {
            font-weight: bold;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>X Space</h2>
        <p>مكانك المثالي للعمل والإبداع 💡</p>
        <p>استمتع بكل لحظة هنا!</p>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>العدد</th>
                <th>المنتج</th>
                <th>المجموع</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach ($items as $item)
                @php $grandTotal += $item['total']; @endphp
                <tr>
                    <td>{{ $item['qty'] }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['total'] }} جينة</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        الإجمالي: {{ $grandTotal }} جينة
    </div>

    <div class="footer">
        شكرًا لزيارتك X Space 💼<br>
        نتطلع لرؤيتك مرة أخرى!
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
