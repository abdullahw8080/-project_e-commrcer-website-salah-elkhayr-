<?php
require_once '../database/connect_to_database.php';
require_once '../admin acount/admin_session.php';

// التحقق من وجود معرف الطلب
if (!isset($_GET['order_id'])) {
    header("Location: get_daily_sales.php");
    exit();
}

$orderId = intval($_GET['order_id']);

// استعلام لجلب بيانات الطلب الأساسية
$orderQuery = "SELECT po.*, poa.*, tp.Type_payment 
               FROM orders po
               JOIN orders_address poa ON po.OD_id = poa.OD_id
               JOIN Type_payment tp ON poa.Type_payment_id = tp.Type_payment_id
               WHERE po.Or_id = ?";
$stmt = mysqli_prepare($conn, $orderQuery);
mysqli_stmt_bind_param($stmt, "i", $orderId);
mysqli_stmt_execute($stmt);
$orderResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($orderResult) === 0) {
    header("Location:daily_order_delails.php");
    exit();
}

$order = mysqli_fetch_assoc($orderResult);

// استعلام لجلب عناصر الطلب
$itemsQuery = "SELECT poi.*, p.P_name, PM.P_img 
               FROM order_items poi 
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
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2e7d32;
            --secondary-color: #1b5e20;
            --accent-color: #4caf50;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
        }
        
        .container::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 10px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }
        
        h1, h2, h3 {
            color: var(--dark-color);
            margin-top: 0;
        }
        
        h1 {
            font-size: 28px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        h1 i {
            color: var(--primary-color);
        }
        
        h2 {
            font-size: 22px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            position: relative;
        }
        
        h2::after {
            content: "";
            position: absolute;
            bottom: -2px;
            right: 0;
            width: 100px;
            height: 2px;
            background: var(--primary-color);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .order-status {
            background-color: var(--primary-color);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .order-info {
            margin-bottom: 40px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .info-card {
            background-color: #f9fafb;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: var(--transition);
        }
        
        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .info-item {
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            flex: 1;
        }
        
        .info-value {
            flex: 1;
            text-align: left;
            color: var(--dark-color);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        th, td {
            padding: 15px;
            text-align: center;
            border: none;
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            font-size: 15px;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #f1f3f5;
        }
        
        .product-img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #eee;
            background-color: white;
            padding: 5px;
            transition: var(--transition);
        }
        
        .product-img:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .total-row {
            font-weight: 700;
            background-color: #f1f8fe;
        }
        
        .total-row td {
            font-size: 16px;
        }
        
        .total-amount {
            color: var(--primary-color);
            font-size: 18px;
        }
        
        .actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: var(--border-radius);
            cursor: pointer;
            text-decoration: none;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 600;
            transition: var(--transition);
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn i {
            font-size: 16px;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn:active {
            transform: translateY(1px);
        }
        
        .btn-back {
            background-color: #6c757d;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
        }
        
        .btn-print {
            background-color: #17a2b8;
        }
        
        .btn-print:hover {
            background-color: #138496;
        }
        
        .btn-process {
            background-color: var(--success-color);
        }
        
        .btn-process:hover {
            background-color: #3d8b40;
        }
        
        .btn-cancel {
            background-color: var(--danger-color);
        }
        
        .btn-cancel:hover {
            background-color: #d11a2a;
        }
        
        .order-date {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: #f0f7ff;
            padding: 8px 15px;
            border-radius: 20px;
            color: var(--primary-color);
            font-weight: 500;
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            .container, .container * {
                visibility: visible;
            }
            .container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none;
            }
            .actions {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 15px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            th, td {
                padding: 10px 5px;
                font-size: 14px;
            }
            
            .product-img {
                width: 50px;
                height: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="order-header">
            <h1><i class="fas fa-file-invoice"></i> تفاصيل الطلب #<?= $orderId ?></h1>
            <div class="order-date">
                <i class="far fa-calendar-alt"></i>
                <?= date('Y/m/d H:i', strtotime($order['Or_date'])) ?>
            </div>
        </div>

        <div class="order-info">
            <h2><i class="fas fa-user-circle"></i> معلومات العميل</h2>
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-item">
                        <span class="info-label">اسم العميل:</span>
                        <span class="info-value"><?= htmlspecialchars($order['Client_name']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">رقم الهاتف:</span>
                        <span class="info-value"><?= htmlspecialchars($order['Client_phone']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">طريقة الدفع:</span>
                        <span class="info-value"><?= htmlspecialchars($order['Type_payment']) ?></span>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-item">
                        <span class="info-label">المحافظة:</span>
                        <span class="info-value"><?= htmlspecialchars($order['Client_state']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">المديرية:</span>
                        <span class="info-value"><?= htmlspecialchars($order['Client_city']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">الشارع:</span>
                        <span class="info-value"><?= htmlspecialchars($order['Client_street']) ?></span>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-item">
                        <span class="info-label">الحي:</span>
                        <span class="info-value"><?= htmlspecialchars($order['Client_neb']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">رقم المنزل:</span>
                        <span class="info-value"><?= htmlspecialchars($order['Billing_id']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">رقم الشقة:</span>
                        <span class="info-value"><?= htmlspecialchars($order['Dep_id']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <h2><i class="fas fa-shopping-basket"></i> المنتجات المطلوبة</h2>
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
                        <img src="../images/product_images/<?= htmlspecialchars($item['P_img']) ?>" 
                             class="product-img" 
                             alt="<?= htmlspecialchars($item['P_name']) ?>"
                             title="<?= htmlspecialchars($item['P_name']) ?>">
                    </td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['price'], 2) ?> ريال</td>
                    <td><?= number_format($item['quantity'] * $item['price'], 2) ?> ريال</td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="5" style="text-align: left; padding-left: 30px;">المجموع الكلي</td>
                    <td class="total-amount"><?= number_format($order['Total_price'], 2) ?> ريال</td>
                </tr>
            </tbody>
        </table>

        <div class="actions">
            <button onclick="window.print()" class="btn btn-print">
                <i class="fas fa-print"></i> طباعة الفاتورة
            </button>
            <a href="get_daily_sales.php" class="btn btn-back">
                <i class="fas fa-arrow-right"></i> العودة للقائمة
            </a>
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