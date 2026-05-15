<?php
// C:\xampp\htdocs\project_e-commrcer\reports\daily_sales.php

require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقاعدة البيانات

// الحصول على تاريخ اليوم
$today = date('Y-m-d');

// استعلام المبيعات اليومية
$sales_query = "
    SELECT 
        o.Or_id,
        o.Or_date,
        o.Total_price,
        oa.Client_name,
        oa.Client_phone,
        COUNT(oi.or_items_id) AS items_count
    FROM 
        Orders o
    JOIN Orders_address oa ON o.OD_id = oa.OD_id
    JOIN order_items oi ON o.Or_id = oi.or_id
    WHERE 
        DATE(o.Or_date) = '$today'
    GROUP BY 
        o.Or_id
    ORDER BY 
        o.Or_date DESC
";

$orders_result = mysqli_query($conn, $sales_query);
$orders = [];

if ($orders_result) {
    $orders = mysqli_fetch_all($orders_result, MYSQLI_ASSOC);
    $total_sales = array_sum(array_column($orders, 'Total_price'));
} else {
    die("خطأ في استعلام المبيعات: " . mysqli_error($conn));
}

// استعلام المنتجات الأكثر مبيعاً
$products_query = "
    SELECT 
        p.P_name,
        SUM(oi.quantity) AS total_quantity,
        SUM(oi.quantity * oi.price) AS total_sales
    FROM 
        order_items oi
    JOIN products p ON oi.P_id = p.P_id
    JOIN Orders o ON oi.or_id = o.Or_id
    WHERE 
        DATE(o.Or_date) = '$today'
    GROUP BY 
        p.P_id
    ORDER BY 
        total_quantity DESC
    LIMIT 5
";

$products_result = mysqli_query($conn, $products_query);
$top_products = [];

if ($products_result) {
    $top_products = mysqli_fetch_all($products_result, MYSQLI_ASSOC);
} else {
    die("خطأ في استعلام المنتجات: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير المبيعات اليومية</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2e7d32;
            --secondary-color: #1b5e20;
            --accent-color: #4caf50;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --border-radius: 10px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f0f2f5;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            box-shadow: var(--box-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .header-content {
            display: flex;
            align-items: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            display: flex;
            align-items: center;
        }
        
        .header h1 i {
            margin-left: 10px;
        }
        
        .print-btn {
            background-color: white;
            color: var(--primary-color);
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }
        
        .print-btn:hover {
            background-color: #e8f5e9;
            transform: translateY(-2px);
        }
        
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 20px;
            transition: var(--transition);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .card-header h3 {
            margin: 0;
            color: var(--dark-color);
            font-size: 20px;
        }
        
        .card-header i {
            color: var(--primary-color);
            font-size: 24px;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .summary-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            text-align: center;
            transition: var(--transition);
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
        }
        
        .summary-card h4 {
            margin-top: 0;
            color: var(--dark-color);
            font-size: 16px;
        }
        
        .summary-card p {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
            color: var(--primary-color);
        }
        
        .summary-card .icon {
            font-size: 40px;
            color: var(--accent-color);
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }
        
        th, td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: #f1f1f1;
        }
        
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 5px;
            border: 1px solid #ddd;
            background-color: #fff;
            padding: 3px;
        }
        
        .product-img-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 14px;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            background-color: #f9f9f9;
            border-radius: var(--border-radius);
        }
        
        .no-data i {
            font-size: 40px;
            margin-bottom: 10px;
            color: #ccc;
        }
        
        .date-badge {
            background-color: var(--accent-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            display: inline-block;
            margin-right: 10px;
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
            }
            .print-btn, .card-header i {
                display: none !important;
            }
        }
        
        @media (max-width: 768px) {
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1>
                    <i class="fas fa-chart-line"></i>
                    تقرير المبيعات اليومية
                    <span class="date-badge"><?php echo htmlspecialchars($today); ?></span>
                </h1>
            </div>
            <button class="print-btn" onclick="window.print()">
                <i class="fas fa-print"></i>
                طباعة التقرير
            </button>
        </div>
        
        <div class="summary-cards">
            <div class="summary-card">
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h4>إجمالي المبيعات</h4>
                <p><?php echo number_format($total_sales, 2); ?> ر.ي</p>
            </div>
            
            <div class="summary-card">
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h4>عدد الطلبات</h4>
                <p><?php echo count($orders); ?></p>
            </div>
            
            <div class="summary-card">
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <h4>المنتجات المباعة</h4>
                <p>
                    <?php 
                        $total_items = 0;
                        foreach ($orders as $order) {
                            $total_items += $order['items_count'];
                        }
                        echo $total_items;
                    ?>
                </p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file-invoice-dollar"></i> الطلبات اليومية</h3>
            </div>
            
            <?php if (!empty($orders)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>اسم العميل</th>
                            <th>هاتف العميل</th>
                            <th>عدد المنتجات</th>
                            <th>المبلغ</th>
                            <th>التاريخ</th>
                            <th>التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['Or_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['Client_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['Client_phone']); ?></td>
                            <td><?php echo htmlspecialchars($order['items_count']); ?></td>
                            <td><?php echo number_format($order['Total_price'], 2); ?> ر.ي</td>
                            <td><?php echo date('H:i', strtotime($order['Or_date'])); ?></td>
                            <td>
                                <a href="daily_order_delails.php?order_id=<?php echo htmlspecialchars($order['Or_id']); ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> تفاصيل
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <h3>لا توجد طلبات اليوم</h3>
                    <p>لم يتم تسجيل أي طلبات حتى الآن</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-star"></i> المنتجات الأكثر مبيعاً</h3>
            </div>
            
            <?php if (!empty($top_products)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>صورة المنتج</th>
                            <th>اسم المنتج</th>
                            <th>الكمية المباعة</th>
                            <th>إجمالي المبيعات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_products as $product): 
                            // استعلام معدل للحصول على صورة المنتج
                            $img_query = "SELECT p.P_name, pi.P_img 
                                         FROM products p
                                         LEFT JOIN product_images pi ON p.P_id = pi.P_id
                                         WHERE p.P_name = '".mysqli_real_escape_string($conn, $product['P_name'])."'
                                         LIMIT 1";
                            $img_result = mysqli_query($conn, $img_query);
                            $product_img = 'default_product.jpg'; // صورة افتراضية
                            
                            if ($img_result && mysqli_num_rows($img_result) > 0) {
                                $img_data = mysqli_fetch_assoc($img_result);
                                $product_img = $img_data['P_img'] ?? 'default_product.jpg';
                            }
                        ?>
                        <tr>
                            <td class="product-img-container">
                                <?php if(file_exists("../images/product_images/".$product_img)): ?>
                                    <img src="../images/product_images/<?php echo htmlspecialchars($product_img); ?>" 
                                         class="product-img" 
                                         alt="<?php echo htmlspecialchars($product['P_name']); ?>"
                                         title="<?php echo htmlspecialchars($product['P_name']); ?>">
                                <?php else: ?>
                                    <img src="../images/product_images/default_product.jpg" 
                                         class="product-img" 
                                         alt="صورة افتراضية">
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['P_name']); ?></td>
                            <td><?php echo htmlspecialchars($product['total_quantity']); ?></td>
                            <td><?php echo number_format($product['total_sales'], 2); ?> ر.ي</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-box-open"></i>
                    <h3>لا توجد بيانات مبيعات اليوم</h3>
                    <p>لم يتم بيع أي منتجات حتى الآن</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // يمكن إضافة أي سكريبتات إضافية هنا إذا لزم الأمر
    </script>
</body>
</html>