<?php
require_once '../database/connect_to_database.php';
require_once '../admin acount/admin_session.php';

// التحقق من وجود معرف الطلب
if (!isset($_GET['order_id'])) {
    header("Location: pending_orders_report.php");
    exit();
}

$orderId = intval($_GET['order_id']);

// استعلام لجلب بيانات الطلب الأساسية
$orderQuery = "SELECT po.*, poa.*, tp.Type_payment 
               FROM pending_Orders po
               JOIN pending_Orders_address poa ON po.OD_id = poa.OD_id
               JOIN Type_payment tp ON poa.Type_payment_id = tp.Type_payment_id
               WHERE po.Or_id = ?";
$stmt = mysqli_prepare($conn, $orderQuery);
mysqli_stmt_bind_param($stmt, "i", $orderId);
mysqli_stmt_execute($stmt);
$orderResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($orderResult) === 0) {
    header("Location:pending_orders_report.php");
    exit();
}

$order = mysqli_fetch_assoc($orderResult);

// استعلام لجلب عناصر الطلب
$itemsQuery = "SELECT poi.*, p.P_name, PM.P_img 
               FROM pending_order_items poi 
               JOIN products p ON poi.P_id = p.P_id
               JOIN product_images PM ON PM.P_id = p.P_id
               WHERE poi.or_id = ?";
$stmt = mysqli_prepare($conn, $itemsQuery);
mysqli_stmt_bind_param($stmt, "i", $orderId);
mysqli_stmt_execute($stmt);
$itemsResult = mysqli_stmt_get_result($stmt);
$items = mysqli_fetch_all($itemsResult, MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الطلب #<?= $orderId ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .order-info {
            margin-bottom: 30px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .info-item {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #218838;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .actions {
            text-align: center;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            display: inline-block;
        }
        .btn-process {
            background-color: #28a745;
        }
        .btn-process:hover {
            background-color: #218838;
        }
        .btn-cancel {
            background-color: #dc3545;
        }
        .btn-cancel:hover {
            background-color: #c82333;
        }
        .btn-back {
            background-color: #6c757d;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="order-header">
            <h1>تفاصيل الطلب #<?= $orderId ?></h1>
            <div>
                <span class="info-label">تاريخ الطلب:</span>
                <span><?= date('Y/m/d H:i', strtotime($order['Or_date'])) ?></span>
            </div>
        </div>

        <div class="order-info">
            <h2>معلومات العميل</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">اسم العميل:</span>
                    <span><?= htmlspecialchars($order['Client_name']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">رقم الهاتف:</span>
                    <span><?= htmlspecialchars($order['Client_phone']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">المحافظة:</span>
                    <span><?= htmlspecialchars($order['Client_state']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">المديرية:</span>
                    <span><?= htmlspecialchars($order['Client_city']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">الشارع:</span>
                    <span><?= htmlspecialchars($order['Client_street']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">الحي:</span>
                    <span><?= htmlspecialchars($order['Client_neb']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">رقم المنزل:</span>
                    <span><?= htmlspecialchars($order['Billing_id']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">رقم الشقة:</span>
                    <span><?= htmlspecialchars($order['Dep_id']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">طريقة الدفع:</span>
                    <span><?= htmlspecialchars($order['Type_payment']) ?></span>
                </div>
            </div>
        </div>

        <h2>المنتجات المطلوبة</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>المنتج</th>
                    <th>الصورة</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th>المجموع</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $index => $item): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($item['P_name']) ?></td>
                    <td>
                        <img src="../images/product_images/<?= htmlspecialchars($item['P_img']) ?>" class="product-img">
                    </td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['price'], 2) ?> ريال</td>
                    <td><?= number_format($item['quantity'] * $item['price'], 2) ?> ريال</td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="5">المجموع الكلي</td>
                    <td><?= number_format($order['Total_price'], 2) ?> ريال</td>
                </tr>
            </tbody>
        </table>

        <div class="actions">
            <a href="pending_orders_report.php" class="btn btn-back">العودة للقائمة</a>
            <button class="btn btn-process" onclick="processOrder(<?= $orderId ?>)">
                <i class="fas fa-check"></i> معالجة الطلب
            </button>
            <button class="btn btn-cancel" onclick="cancelOrder(<?= $orderId ?>)">
                <i class="fas fa-times"></i> إلغاء الطلب
            </button>
        </div>
    </div>

    <script>
        async function processOrder(orderId) {
            if (!confirm('هل أنت متأكد من معالجة هذا الطلب؟')) return;
            
            try {
                const response = await fetch(`confirm_order.php?order_id=${orderId}`);
                
                if (!response.ok) {
                    throw new Error(`خطأ في الشبكة: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    alert('تم معالجة الطلب بنجاح');
                    window.location.href = 'pending_orders_report.php';
                } else {
                    throw new Error(data.message || 'حدث خطأ غير معروف');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(`فشل معالجة الطلب: ${error.message}`);
            }
        }

        async function cancelOrder(orderId) {
            if (!confirm('هل أنت متأكد من إلغاء هذا الطلب؟')) return;
            
            try {
                const response = await fetch(`cancel_order.php?order_id=${orderId}`);
                
                if (!response.ok) {
                    throw new Error(`خطأ في الشبكة: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    alert('تم إلغاء الطلب بنجاح');
                    window.location.href = 'pending_orders_report.php';
                } else {
                    throw new Error(data.message || 'حدث خطأ غير معروف');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(`فشل إلغاء الطلب: ${error.message}`);
            }
        }
    </script>
</body>
</html>