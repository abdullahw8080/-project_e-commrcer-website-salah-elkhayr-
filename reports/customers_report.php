<?php
// ملف: customers_report.php
// الوصف: تقرير متكامل للعملاء مع واجهة منبثقة تفاعلية

require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقاعدة البيانات


mysqli_set_charset($conn, "utf8mb4");

// 2. استعلامات جلب البيانات الأساسية

// استعلام أفضل 5 عملاء
$query_top_customers = "SELECT 
    u.User_id, u.User_name, u.User_email, u.User_phone,
    oa.Client_city,
    COUNT(o.Or_id) AS orders_count,
    SUM(o.Total_price) AS total_spent,
    AVG(o.Total_price) AS avg_order_value
FROM Users u
JOIN Orders_address oa ON u.User_phone = oa.Client_phone
JOIN Orders o ON oa.OD_id = o.OD_id
GROUP BY u.User_id
ORDER BY orders_count DESC
LIMIT 5";

$result_top_customers = mysqli_query($conn, $query_top_customers);

// تخزين بيانات العملاء المميزين
$top_customers = array();
$customer_names = array();
$orders_count = array();
$total_spent = array();

while ($row = mysqli_fetch_assoc($result_top_customers)) {
    $top_customers[] = $row;
    $customer_names[] = $row['User_name'];
    $orders_count[] = $row['orders_count'];
    $total_spent[] = $row['total_spent'];
}

// استعلام توزيع المبيعات حسب المدينة
$query_city_sales = "SELECT 
    oa.Client_city AS city,
    SUM(o.Total_price) AS city_total
FROM Orders_address oa
JOIN Orders o ON oa.OD_id = o.OD_id
GROUP BY oa.Client_city
ORDER BY city_total DESC";

$result_city_sales = mysqli_query($conn, $query_city_sales);

$cities = array();
$city_sales = array();

while ($row = mysqli_fetch_assoc($result_city_sales)) {
    $cities[] = $row['city'];
    $city_sales[] = $row['city_total'];
}

// استعلام تصنيف العملاء حسب القيمة
$query_customer_value = "SELECT 
    customer_level,
    COUNT(User_id) AS customers_count
FROM (
    SELECT 
        u.User_id,
        CASE 
            WHEN COUNT(o.Or_id) > 10 THEN 'ممتاز'
            WHEN COUNT(o.Or_id) > 5 THEN 'جيد جداً'
            WHEN COUNT(o.Or_id) > 2 THEN 'جيد'
            ELSE 'جديد'
        END AS customer_level
    FROM Users u
    LEFT JOIN Orders_address oa ON u.User_phone = oa.Client_phone
    LEFT JOIN Orders o ON oa.OD_id = o.OD_id
    GROUP BY u.User_id
) AS customer_levels
GROUP BY customer_level
ORDER BY 
    CASE customer_level
        WHEN 'ممتاز' THEN 1
        WHEN 'جيد جداً' THEN 2
        WHEN 'جيد' THEN 3
        ELSE 4
    END";

$result_customer_value = mysqli_query($conn, $query_customer_value);

$customer_value_labels = array();
$customer_value_counts = array();

while ($row = mysqli_fetch_assoc($result_customer_value)) {
    $customer_value_labels[] = $row['customer_level'];
    $customer_value_counts[] = $row['customers_count'];
}

// استعلام جميع العملاء للجدول الرئيسي
$query_all_customers = "SELECT 
    u.User_id, u.User_name, u.User_email, u.User_phone,
    oa.Client_city,
    COUNT(o.Or_id) AS orders_count,
    SUM(o.Total_price) AS total_spent,
    AVG(o.Total_price) AS avg_order_value
FROM Users u
LEFT JOIN Orders_address oa ON u.User_phone = oa.Client_phone
LEFT JOIN Orders o ON oa.OD_id = o.OD_id
GROUP BY u.User_id
ORDER BY orders_count DESC";

$result_all_customers = mysqli_query($conn, $query_all_customers);

// إغلاق الاتصال
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير العملاء الأكثر شراءً</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* أنماط CSS كما هي في الكود السابق */
        :root {
            --primary-color: #218838;
            --primary-dark:rgb(41, 185, 46);
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --gray-color: #95a5a6;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Tajawal', sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(var(--primary-color));
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .report-actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .search-filter {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .search-filter input, 
        .search-filter select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .export-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-success {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e67e22;
        }
        
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .chart-title {
            margin-bottom: 15px;
            color: var(--dark-color);
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chart-wrapper {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: right;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-align: right;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-primary {
            background-color: #e3f2fd;
            color: var(--primary-color);
        }
        
        .badge-success {
            background-color: #e8f5e9;
            color: var(--secondary-color);
        }
        
        .badge-warning {
            background-color: #fff8e1;
            color: var(--warning-color);
        }
        
        .badge-danger {
            background-color: #ffebee;
            color: var(--danger-color);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            width: 80%;
            max-width: 900px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: left;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
        
        .customer-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .detail-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .detail-card h3 {
            margin-bottom: 15px;
            color: var(--primary-color);
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .detail-value {
            color: #555;
        }
        
        .orders-table {
            width: 100%;
            margin-top: 15px;
        }
        
        .orders-table th {
            background-color: var(--secondary-color);
        }
        
        .products-table {
            width: 100%;
            margin-top: 15px;
        }
        
        .products-table th {
            background-color: var(--warning-color);
        }
        
        .section-title {
            margin: 20px 0 15px;
            color: var(--dark-color);
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0,0,0,.3);
            border-radius: 50%;
            border-top-color: #000;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
            
            .customer-details {
                grid-template-columns: 1fr;
            }
            
            .report-actions {
                flex-direction: column;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users"></i> تقرير العملاء الأكثر شراءً</h1>
            <p>تحليل شامل لعملاء المتجر حسب عدد الطلبات وقيمة المشتريات</p>
        </div>
        
        <div class="report-actions">
            <div class="search-filter">
                <input type="text" id="customerSearch" placeholder="بحث باسم العميل أو البريد الإلكتروني">
                <select id="cityFilter">
                    <option value="">جميع المدن</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= htmlspecialchars($city) ?>"><?= htmlspecialchars($city) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" id="searchBtn"><i class="fas fa-search"></i> بحث</button>
            </div>
            
            <div class="export-actions">
                <button class="btn btn-success" id="exportExcel"><i class="fas fa-file-excel"></i> تصدير Excel</button>
                <button class="btn btn-warning" id="printReport"><i class="fas fa-print"></i> طباعة</button>
            </div>
        </div>
        
        <!-- قسم الرسوم البيانية -->
        <div class="charts-container">
            <div class="chart-card">
                <h3 class="chart-title"><i class="fas fa-chart-bar"></i> أفضل 5 عملاء حسب عدد الطلبات</h3>
                <div class="chart-wrapper">
                    <canvas id="ordersChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <h3 class="chart-title"><i class="fas fa-chart-pie"></i> توزيع المشتريات حسب المدينة</h3>
                <div class="chart-wrapper">
                    <canvas id="cityChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <h3 class="chart-title"><i class="fas fa-chart-line"></i> قيمة المشتريات للعملاء المميزين</h3>
                <div class="chart-wrapper">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <h3 class="chart-title"><i class="fas fa-star"></i> تصنيف العملاء حسب القيمة</h3>
                <div class="chart-wrapper">
                    <canvas id="valueChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- جدول عرض جميع العملاء -->
        <div class="table-container">
            <table id="customersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم العميل</th>
                        <th>البريد الإلكتروني</th>
                        <th>المدينة</th>
                        <th>عدد الطلبات</th>
                        <th>إجمالي المشتريات</th>
                        <th>متوسط الطلب</th>
                        <th>تفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    mysqli_data_seek($result_all_customers, 0); // إعادة تعيين مؤشر النتائج
                    while ($customer = mysqli_fetch_assoc($result_all_customers)): 
                    ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><?= htmlspecialchars($customer['User_name']) ?></td>
                            <td><?= htmlspecialchars($customer['User_email']) ?></td>
                            <td><?= isset($customer['Client_city']) ? htmlspecialchars($customer['Client_city']) : 'غير محدد' ?></td>
                            <td><?= $customer['orders_count'] ?? 0 ?></td>
                            <td><?= number_format($customer['total_spent'] ?? 0) ?> ريال</td>
                            <td><?= number_format($customer['avg_order_value'] ?? 0) ?> ريال</td>
                            <td>
                                <button class="btn btn-primary btn-view-detail" 
                                        data-customer-id="<?= $customer['User_id'] ?>"
                                        data-customer-phone="<?= htmlspecialchars($customer['User_phone']) ?>">
                                    <i class="fas fa-eye"></i> عرض
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- نافذة عرض تفاصيل العميل -->
    <div id="customerModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-user"></i> الملف التفصيلي للعميل</h2>
            
            <div class="customer-details">
                <div class="detail-card">
                    <h3><i class="fas fa-info-circle"></i> المعلومات الأساسية</h3>
                    <div class="detail-row">
                        <span class="detail-label">الاسم الكامل:</span>
                        <span class="detail-value" id="detail-name"><span class="loading"></span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">البريد الإلكتروني:</span>
                        <span class="detail-value" id="detail-email"><span class="loading"></span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">رقم الهاتف:</span>
                        <span class="detail-value" id="detail-phone"><span class="loading"></span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">المدينة:</span>
                        <span class="detail-value" id="detail-city"><span class="loading"></span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">عدد الطلبات:</span>
                        <span class="detail-value" id="detail-orders"><span class="loading"></span></span>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-chart-line"></i> إحصائيات العميل</h3>
                    <div class="detail-row">
                        <span class="detail-label">إجمالي المشتريات:</span>
                        <span class="detail-value" id="detail-total"><span class="loading"></span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">متوسط قيمة الطلب:</span>
                        <span class="detail-value" id="detail-avg"><span class="loading"></span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">آخر طلب:</span>
                        <span class="detail-value" id="detail-last"><span class="loading"></span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">الحالة:</span>
                        <span class="detail-value"><span class="badge" id="detail-status"><span class="loading"></span></span></span>
                    </div>
                </div>
            </div>
            
            <div class="customer-charts">
                <div class="chart-card">
                    <h3 class="chart-title"><i class="fas fa-chart-bar"></i> تطور المشتريات خلال السنة</h3>
                    <div class="chart-wrapper">
                        <canvas id="customerTrendChart"></canvas>
                    </div>
                </div>
            </div>
            
            <h3 class="section-title"><i class="fas fa-shopping-cart"></i> الطلبات السابقة</h3>
            <div class="table-container">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>التاريخ</th>
                            <th>المبلغ الإجمالي</th>
                            <th>حالة الطلب</th>
                            <th>طريقة الدفع</th>
                        </tr>
                    </thead>
                    <tbody id="orders-body">
                        <tr>
                            <td colspan="5" style="text-align: center;"><span class="loading"></span> جارٍ تحميل الطلبات...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <h3 class="section-title"><i class="fas fa-box-open"></i> المنتجات المشتراة</h3>
            <div class="table-container">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>اسم المنتج</th>
                            <th>الكمية</th>
                            <th>سعر الوحدة</th>
                            <th>رقم الطلب</th>
                            <th>تاريخ الطلب</th>
                        </tr>
                    </thead>
                    <tbody id="products-body">
                        <tr>
                            <td colspan="5" style="text-align: center;"><span class="loading"></span> جارٍ تحميل المنتجات...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // بيانات الرسوم البيانية من PHP
        const customerNames = <?= json_encode($customer_names) ?>;
        const ordersCount = <?= json_encode($orders_count) ?>;
        const totalSpent = <?= json_encode($total_spent) ?>;
        const cities = <?= json_encode($cities) ?>;
        const citySales = <?= json_encode($city_sales) ?>;
        const customerValueLabels = <?= json_encode($customer_value_labels) ?>;
        const customerValueCounts = <?= json_encode($customer_value_counts) ?>;
        
        // متغير لحفظ بيانات العميل الحالي
        let currentCustomerId = null;
        let customerTrendChart = null;
        
        // تهيئة جميع الرسوم البيانية
        function initCharts() {
            // رسم بياني لعدد الطلبات (أعمدة)
            const ordersCtx = document.getElementById('ordersChart').getContext('2d');
            new Chart(ordersCtx, {
                type: 'bar',
                data: {
                    labels: customerNames,
                    datasets: [{
                        label: 'عدد الطلبات',
                        data: ordersCount,
                        backgroundColor: [
                            'rgba(46, 204, 113, 0.7)',
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(241, 196, 15, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(231, 76, 60, 0.7)'
                        ],
                        borderColor: [
                            'rgba(46, 204, 113, 1)',
                            'rgba(52, 152, 219, 1)',
                            'rgba(241, 196, 15, 1)',
                            'rgba(155, 89, 182, 1)',
                            'rgba(231, 76, 60, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `عدد الطلبات: ${context.raw}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'عدد الطلبات'
                            }
                        }
                    }
                }
            });
            
            // رسم بياني للمبيعات حسب المدينة (دائري)
            const cityCtx = document.getElementById('cityChart').getContext('2d');
            new Chart(cityCtx, {
                type: 'pie',
                data: {
                    labels: cities,
                    datasets: [{
                        data: citySales,
                        backgroundColor: [
                            'rgba(46, 204, 113, 0.7)',
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(241, 196, 15, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(231, 76, 60, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            rtl: true
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((context.raw / total) * 100);
                                    return `${context.label}: ${context.raw.toLocaleString()} ريال (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            
            // رسم بياني لقيمة المشتريات (خطي)
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: customerNames,
                    datasets: [{
                        label: 'إجمالي المشتريات (ريال)',
                        data: totalSpent,
                        backgroundColor: 'rgba(52, 219, 80, 0.2)',
                        borderColor: 'rgb(52, 219, 80)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            rtl: true
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `إجمالي المشتريات: ${context.raw.toLocaleString()} ريال`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'قيمة المشتريات (ريال)'
                            }
                        }
                    }
                }
            });
            
            // رسم بياني لتصنيف العملاء (رادار)
            const valueCtx = document.getElementById('valueChart').getContext('2d');
            new Chart(valueCtx, {
                type: 'radar',
                data: {
                    labels: customerValueLabels,
                    datasets: [{
                        label: 'عدد العملاء',
                        data: customerValueCounts,
                        backgroundColor: 'rgba(46, 204, 113, 0.2)',
                        borderColor: 'rgba(46, 204, 113, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            rtl: true
                        }
                    },
                    scales: {
                        r: {
                            angleLines: {
                                display: true
                            },
                            suggestedMin: 0
                        }
                    }
                }
            });
        }
        
        // تهيئة الرسم البياني لتطور المشتريات في النافذة المنبثقة
        function initCustomerTrendChart(months, monthlySales) {
            const trendCtx = document.getElementById('customerTrendChart').getContext('2d');
            
            // إذا كان هناك رسم بياني موجود، نقوم بتدميره أولاً
            if (customerTrendChart) {
                customerTrendChart.destroy();
            }
            
            customerTrendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'قيمة المشتريات الشهرية',
                        data: monthlySales,
                        backgroundColor: 'rgba(155, 89, 182, 0.2)',
                        borderColor: 'rgba(155, 89, 182, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            rtl: true
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `المبيعات: ${context.raw.toLocaleString()} ريال`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'قيمة المشتريات (ريال)'
                            }
                        }
                    }
                }
            });
        }
        
        // وظيفة لجلب تفاصيل العميل عبر AJAX
        function fetchCustomerDetails(customerId, customerPhone) {
            currentCustomerId = customerId;
            
            // عرض رسالة التحميل
            $('#detail-name').html('<span class="loading"></span>');
            $('#detail-email').html('<span class="loading"></span>');
            $('#detail-phone').html('<span class="loading"></span>');
            $('#detail-city').html('<span class="loading"></span>');
            $('#detail-orders').html('<span class="loading"></span>');
            $('#detail-total').html('<span class="loading"></span>');
            $('#detail-avg').html('<span class="loading"></span>');
            $('#detail-last').html('<span class="loading"></span>');
            $('#detail-status').html('<span class="loading"></span>');
            $('#orders-body').html('<tr><td colspan="5" style="text-align: center;"><span class="loading"></span> جارٍ تحميل الطلبات...</td></tr>');
            $('#products-body').html('<tr><td colspan="5" style="text-align: center;"><span class="loading"></span> جارٍ تحميل المنتجات...</td></tr>');
            
            // جلب بيانات العميل الأساسية
            $.ajax({
                url: 'get_customer_details.php',
                type: 'POST',
                dataType: 'json',
                data: { 
                    customer_id: customerId,
                    customer_phone: customerPhone
                },
                success: function(response) {
                    if (response.success) {
                        // تحديث بيانات العميل الأساسية
                        $('#detail-name').text(response.customer.User_name);
                        $('#detail-email').text(response.customer.User_email);
                        $('#detail-phone').text(response.customer.User_phone);
                        $('#detail-city').text(response.customer.Client_city || 'غير محدد');
                        $('#detail-orders').text(response.customer.orders_count);
                        $('#detail-total').text(response.customer.total_spent.toLocaleString() + ' ريال');
                        $('#detail-avg').text(response.customer.avg_order_value.toLocaleString() + ' ريال');
                        $('#detail-last').text(response.customer.last_order_date || 'لا يوجد');
                        
                        // تحديث حالة العميل
                        let statusBadge = '';
                        if (response.customer.orders_count > 10) {
                            statusBadge = '<span class="badge badge-success">ممتاز</span>';
                        } else if (response.customer.orders_count > 5) {
                            statusBadge = '<span class="badge badge-primary">جيد جداً</span>';
                        } else if (response.customer.orders_count > 2) {
                            statusBadge = '<span class="badge badge-warning">جيد</span>';
                        } else {
                            statusBadge = '<span class="badge badge-danger">جديد</span>';
                        }
                        $('#detail-status').html(statusBadge);
                        
                        // جلب الطلبات السابقة
                        fetchCustomerOrders(customerPhone);
                        
                        // جلب المنتجات المشتراة
                        fetchCustomerProducts(customerPhone);
                        
                        // جلب بيانات الرسم البياني الشهري
                        fetchCustomerMonthlySales(customerPhone);
                    } else {
                        alert('حدث خطأ أثناء جلب بيانات العميل: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('حدث خطأ في الاتصال بالخادم: ' + error);
                }
            });
        }
        
        // وظيفة لجلب الطلبات السابقة للعميل
        function fetchCustomerOrders(customerPhone) {
            $.ajax({
                url: 'get_customer_orders.php',
                type: 'POST',
                dataType: 'json',
                data: { customer_phone: customerPhone },
                success: function(response) {
                    if (response.success) {
                        let ordersHtml = '';
                        if (response.orders.length > 0) {
                            response.orders.forEach(order => {
                                ordersHtml += `
                                    <tr>
                                        <td>#${order.Or_id}</td>
                                        <td>${order.Or_date}</td>
                                        <td>${order.Total_price.toLocaleString()} ريال</td>
                                        <td><span class="badge badge-success">مكتمل</span></td>
                                        <td>${order.Type_payment}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            ordersHtml = '<tr><td colspan="5" style="text-align: center;">لا توجد طلبات سابقة</td></tr>';
                        }
                        $('#orders-body').html(ordersHtml);
                    } else {
                        $('#orders-body').html('<tr><td colspan="5" style="text-align: center;">حدث خطأ أثناء جلب الطلبات</td></tr>');
                    }
                },
                error: function() {
                    $('#orders-body').html('<tr><td colspan="5" style="text-align: center;">حدث خطأ في الاتصال بالخادم</td></tr>');
                }
            });
        }
        
        // وظيفة لجلب المنتجات المشتراة للعميل
        function fetchCustomerProducts(customerPhone) {
            $.ajax({
                url: 'get_customer_products.php',
                type: 'POST',
                dataType: 'json',
                data: { customer_phone: customerPhone },
                success: function(response) {
                    if (response.success) {
                        let productsHtml = '';
                        if (response.products.length > 0) {
                            response.products.forEach(product => {
                                productsHtml += `
                                    <tr>
                                        <td>${product.P_name}</td>
                                        <td>${product.quantity}</td>
                                        <td>${product.price.toLocaleString()} ريال</td>
                                        <td>#${product.or_id}</td>
                                        <td>${product.order_date}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            productsHtml = '<tr><td colspan="5" style="text-align: center;">لا توجد منتجات مشتراة</td></tr>';
                        }
                        $('#products-body').html(productsHtml);
                    } else {
                        $('#products-body').html('<tr><td colspan="5" style="text-align: center;">حدث خطأ أثناء جلب المنتجات</td></tr>');
                    }
                },
                error: function() {
                    $('#products-body').html('<tr><td colspan="5" style="text-align: center;">حدث خطأ في الاتصال بالخادم</td></tr>');
                }
            });
        }
        
        // وظيفة لجلب المبيعات الشهرية للعميل
        function fetchCustomerMonthlySales(customerPhone) {
            $.ajax({
                url: 'get_customer_monthly_sales.php',
                type: 'POST',
                dataType: 'json',
                data: { customer_phone: customerPhone },
                success: function(response) {
                    if (response.success) {
                        const months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 
                                      'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
                        const monthlySales = new Array(12).fill(0);
                        
                        response.monthly_sales.forEach(sale => {
                            monthlySales[sale.month - 1] = sale.total_sales;
                        });
                        
                        initCustomerTrendChart(months, monthlySales);
                    }
                }
            });
        }
        
        // وظائف النافذة المنبثقة
        const modal = document.getElementById("customerModal");
        const btnViewDetails = document.querySelectorAll(".btn-view-detail");
        const spanClose = document.getElementsByClassName("close")[0];
        
        // فتح النافذة المنبثقة عند النقر على أي زر عرض التفاصيل
        btnViewDetails.forEach(btn => {
            btn.addEventListener("click", function() {
                const customerId = this.getAttribute("data-customer-id");
                const customerPhone = this.getAttribute("data-customer-phone");
                
                modal.style.display = "block";
                fetchCustomerDetails(customerId, customerPhone);
            });
        });
        
        // إغلاق النافذة المنبثقة عند النقر على زر الإغلاق
        spanClose.addEventListener("click", function() {
            modal.style.display = "none";
        });
        
        // إغلاق النافذة المنبثقة عند النقر خارجها
        window.addEventListener("click", function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });
        
        // وظيفة البحث
        $('#searchBtn').click(function() {
            const filter = $('#customerSearch').val().toUpperCase();
            const city = $('#cityFilter').val();
            
            $('#customersTable tbody tr').each(function() {
                const name = $(this).find('td:eq(1)').text().toUpperCase();
                const email = $(this).find('td:eq(2)').text().toUpperCase();
                const customerCity = $(this).find('td:eq(3)').text();
                
                const nameMatch = name.indexOf(filter) > -1;
                const emailMatch = email.indexOf(filter) > -1;
                const cityMatch = city === '' || customerCity === city;
                
                if ((nameMatch || emailMatch) && cityMatch) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // تصدير إلى Excel
        $('#exportExcel').click(function() {
            alert('سيتم تصدير البيانات إلى ملف Excel');
            // هنا يمكنك إضافة كود التصدير الفعلي
        });
        
        // طباعة التقرير
        $('#printReport').click(function() {
            window.print();
        });
        
        // تهيئة الرسوم البيانية عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
        });
    </script>
</body>
</html>