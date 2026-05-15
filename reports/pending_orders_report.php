<?php
// تفعيل عرض الأخطاء للتطوير
error_reporting(E_ALL);
ini_set('display_errors', 1);

require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقاعدة البيانات

// دالة لجلب طرق الدفع مع التعامل مع الأخطاء
function getPaymentMethods($conn) {
    $methods = [];
    try {
        $query = "SELECT Type_payment_id, Type_payment FROM Type_payment";
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            throw new Exception("خطأ في استعلام طرق الدفع: " . mysqli_error($conn));
        }
        
        while ($row = mysqli_fetch_assoc($result)) {
            $methods[] = $row;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
    return $methods;
}

// دالة محسنة لجلب الطلبات المعلقة
function getPendingOrders($conn, $period, $paymentMethod) {
    $orders = [];
    try {
        // بناء شروط الاستعلام
        $dateCondition = match ($period) {
            'today' => "AND DATE(po.Or_date) = CURDATE()",
            'week' => "AND po.Or_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)",
            'month' => "AND po.Or_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)",
            default => ""
        };

        $paymentCondition = ($paymentMethod !== 'all') ? 
            "AND poa.Type_payment_id = " . intval($paymentMethod) : "";

        $query = "SELECT 
                    po.Or_id,
                    poa.Client_name,
                    po.Or_date,
                    tp.Type_payment,
                    po.Total_price,
                    COUNT(poi.or_items_id) AS item_count
                  FROM pending_Orders po
                  JOIN pending_Orders_address poa ON po.OD_id = poa.OD_id
                  JOIN Type_payment tp ON poa.Type_payment_id = tp.Type_payment_id
                  LEFT JOIN pending_order_items poi ON po.Or_id = poi.or_id
                  WHERE 1=1 $dateCondition $paymentCondition
                  GROUP BY po.Or_id
                  ORDER BY po.Or_date DESC";

        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            throw new Exception("خطأ في استعلام الطلبات المعلقة: " . mysqli_error($conn));
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
    return $orders;
}

// دالة لجلب تفاصيل منتجات الطلب المعلق
function getPendingOrderItems($conn, $orderId) {
    $items = [];
    try {
        $query = "SELECT 
                    p.P_id,
                    p.P_name,
                    p.P_img,
                    poi.quantity,
                    poi.price
                 FROM pending_order_items poi
                 JOIN products p ON poi.P_id = p.P_id
                 WHERE poi.or_id = " . intval($orderId);
        
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            throw new Exception("خطأ في استعلام تفاصيل الطلب: " . mysqli_error($conn));
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
    return $items;
}

// جلب البيانات
$paymentMethods = getPaymentMethods($conn);
$period = $_GET['period'] ?? 'today';
$paymentMethod = $_GET['payment_method'] ?? 'all';
$pendingOrders = getPendingOrders($conn, $period, $paymentMethod);
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <!-- باقي الهيد بدون تغيير -->
    <script>
        // دالة محسنة لمعالجة الطلب
        async function processOrder(orderId) {
            if (!confirm('هل أنت متأكد من معالجة هذا الطلب؟')) return;
            
            try {
                const response = await fetch(`process_order.php?order_id=${orderId}`);
                
                if (!response.ok) {
                    throw new Error(`خطأ في الشبكة: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    alert('تم معالجة الطلب بنجاح');
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'حدث خطأ غير معروف');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(`فشل معالجة الطلب: ${error.message}`);
            }
        }

        // باقي الدوال بدون تغيير
    </script>
</head>
<body>
    <!-- باقي الهيكل بدون تغيير -->
</body>
</html>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الطلبات المعلقة - سلة الخير</title>
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
        .summary-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 18px;
            border: 1px solid #eee;
        }
        .summary-card strong {
            color: #2c3e50;
        }
        .filter-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .btn {
            padding: 8px 15px;
            background-color: #218838;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 5px;
        }
        .btn:hover {
            background-color:rgb(25, 167, 56);
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
        .pending-order {
            background-color: #fff3cd;
        }
        .order-details {
            display: none;
            background-color: #f8f9fa;
        }
        .order-details.show {
            display: table-row;
        }
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        .items-table {
            width: 100%;
            margin: 10px 0;
        }
        .items-table th {
            background-color: #6c757d;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                background-color: white;
                font-size: 12pt;
            }
            .container {
                box-shadow: none;
                width: 100%;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>تقرير الطلبات المعلقة</h1>
        
        <div class="summary-card no-print">
            <i class="fas fa-clock"></i> عدد الطلبات المعلقة: <strong><?= count($pendingOrders) ?></strong>
        </div>
        
        <div class="filter-section no-print">
            <div class="filter-group">
                <label for="time-period">الفترة الزمنية:</label>
                <select id="time-period">
                    <option value="today" <?= $period === 'today' ? 'selected' : '' ?>>اليوم</option>
                    <option value="week" <?= $period === 'week' ? 'selected' : '' ?>>أسبوع</option>
                    <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>شهر</option>
                    <option value="all" <?= $period === 'all' ? 'selected' : '' ?>>الكل</option>
                </select>
                
                <label for="payment-method">طريقة الدفع:</label>
                <select id="payment-method">
                    <option value="all">الكل</option>
                    <?php foreach ($paymentMethods as $method): ?>
                        <option value="<?= $method['Type_payment_id'] ?>" <?= $paymentMethod == $method['Type_payment_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($method['Type_payment']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button class="btn" onclick="loadPendingOrders()">
                    <i class="fas fa-filter"></i> تطبيق الفلتر
                </button>
            </div>
            
            <div>
                <button class="btn" onclick="exportReport()">
                    <i class="fas fa-file-excel"></i> تصدير Excel
                </button>
                <button class="btn" onclick="window.print()">
                    <i class="fas fa-print"></i> طباعة
                </button>
            </div>
        </div>
        
        <table id="orders-table">
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>اسم العميل</th>
                    <th>تاريخ الطلب</th>
                    <th>طريقة الدفع</th>
                    <th>المبلغ الإجمالي</th>
                    <th>عدد المنتجات</th>
                    <th class="no-print">الإجراءات</th>
                </tr>
            </thead>
            <tbody id="orders-data">
                <?php if (!empty($pendingOrders)): ?>
                    <?php foreach ($pendingOrders as $order): ?>
                    <tr class="pending-order">
                        <td><?= $order['Or_id'] ?></td>
                        <td><?= htmlspecialchars($order['Client_name']) ?></td>
                        <td><?= date('Y/m/d H:i', strtotime($order['Or_date'])) ?></td>
                        <td><?= htmlspecialchars($order['Type_payment']) ?></td>
                        <td><?= number_format($order['Total_price'], 2) ?> ريال</td>
                        <td><?= $order['item_count'] ?></td>
                        <td class="no-print">
                            <button class="btn btn-process" onclick="processOrder(<?= $order['Or_id'] ?>)">
                                <i class="fas fa-check"></i> معالجة
                            </button>
                            <button class="btn btn-cancel" onclick="cancelOrder(<?= $order['Or_id'] ?>)">
                                <i class="fas fa-times"></i> إلغاء
                            </button>
                            <button class="btn" >
                                <i class="fas fa-info-circle"> <a href="order_details.php?order_id=<?= $order['Or_id'] ?>" class="btn btn-info" >تفاصيل </a></i> 

                            </button>
                        </td>
                    </tr>
                    <tr class="order-details" id="details-<?= $order['Or_id'] ?>">
                        <td colspan="7">
                            <h4>تفاصيل الطلب:</h4>
                            <div id="order-items-<?= $order['Or_id'] ?>">
                                <?php 
                                $items = getPendingOrderItems($conn, $order['Or_id']);
                                if (!empty($items)): ?>
                                    <table class="items-table">
                                        <thead>
                                            <tr>
                                                <th>المنتج</th>
                                                <th>الصورة</th>
                                                <th>الكمية</th>
                                                <th>السعر</th>
                                                <th>المجموع</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['P_name']) ?></td>
                                                <td>
                                                    <img src="<?= htmlspecialchars('../images/product_images/'.$item['P_img']) ?>" 
                                                         class="product-img">
                                                </td>
                                                <td><?= $item['quantity'] ?></td>
                                                <td><?= number_format($item['price'], 2) ?> ريال</td>
                                                <td><?= number_format($item['quantity'] * $item['price'], 2) ?> ريال</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p>لا توجد تفاصيل متاحة لهذا الطلب</p>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">لا توجد طلبات معلقة</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
            // دالة محسنة لمعالجة الطلب
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
                        window.location.reload();
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
                const response = await fetch(`cancel_order.php?order_id=${orderId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`خطأ في الشبكة: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message || 'تم إلغاء الطلب بنجاح');
                    window.location.reload(); // إعادة تحميل الصفحة
                } else {
                    throw new Error(data.message || 'حدث خطأ أثناء الإلغاء');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(`فشل إلغاء الطلب: ${error.message}`);
            }
        }
                
        function exportReport() {
            // يمكنك استخدام مكتبة مثل SheetJS أو TableExport لتصدير البيانات
            const html = document.getElementById('orders-table').outerHTML;
            const blob = new Blob([html], {type: 'application/vnd.ms-excel'});
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'تقرير_الطلبات_المعلقة.xls';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    </script>
</body>
</html>