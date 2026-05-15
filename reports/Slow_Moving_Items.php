<?php
// الاتصال بقاعدة البيانات
require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقاعدة البيانات



// استعلام لجلب المنتجات الراكدة (آخر 6 أشهر)
$query = "SELECT 
            p.P_id as id,
            p.P_name as product_name,
            s.Section_name as category,
            p.P_new_price as price,
            p.P_quantity as stock_quantity,
            (SELECT P_img FROM product_images WHERE P_id = p.P_id LIMIT 1) as image_url,
            COUNT(oi.or_items_id) as sales_count,
            MAX(o.Or_date) as last_sale_date
          FROM products p
          LEFT JOIN order_items oi ON p.P_id = oi.P_id
          LEFT JOIN Orders o ON oi.or_id = o.Or_id
          LEFT JOIN Section s ON p.Section_id = s.Section_id
          WHERE o.Or_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) OR o.Or_date IS NULL
          GROUP BY p.P_id
          HAVING sales_count < 5 OR sales_count IS NULL
          ORDER BY sales_count ASC, last_sale_date ASC";

$result = mysqli_query($conn, $query);

$products = [];
$totalProducts = 0;
$unsoldProducts = 0;
$slowMovingProducts = 0;
$totalStockValue = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
    $totalProducts++;
    
    if ($row['sales_count'] == 0 || $row['sales_count'] === null) {
        $unsoldProducts++;
    }
    
    if ($row['sales_count'] < 5) {
        $slowMovingProducts++;
    }
    
    $totalStockValue += $row['price'] * $row['stock_quantity'];
}

// تحليل Bin
$binAnalysis = [
    'high' => ['count' => 0, 'value' => 0, 'label' => 'مرتفع (50+)'],      // 50+ قطع
    'medium' => ['count' => 0, 'value' => 0, 'label' => 'متوسط (20-49)'],    // 20-49 قطع
    'low' => ['count' => 0, 'value' => 0, 'label' => 'منخفض (1-19)'],       // 1-19 قطع
    'none' => ['count' => 0, 'value' => 0, 'label' => 'منعدم (0)']       // 0 قطع
];

foreach ($products as $product) {
    $stock = $product['stock_quantity'];
    $value = $product['price'] * $stock;
    
    if ($stock >= 50) {
        $binAnalysis['high']['count']++;
        $binAnalysis['high']['value'] += $value;
    } elseif ($stock >= 20) {
        $binAnalysis['medium']['count']++;
        $binAnalysis['medium']['value'] += $value;
    } elseif ($stock >= 1) {
        $binAnalysis['low']['count']++;
        $binAnalysis['low']['value'] += $value;
    } else {
        $binAnalysis['none']['count']++;
        $binAnalysis['none']['value'] += $value;
    }
}

// حساب النسب المئوية
$totalCount = array_sum(array_column($binAnalysis, 'count'));
$totalValue = array_sum(array_column($binAnalysis, 'value'));

foreach ($binAnalysis as &$bin) {
    $bin['count_percentage'] = $totalCount > 0 ? round(($bin['count'] / $totalCount) * 100, 1) : 0;
    $bin['value_percentage'] = $totalValue > 0 ? round(($bin['value'] / $totalValue) * 100, 1) : 0;
}
unset($bin);

// استعلام لجلب الأقسام لقائمة الفلاتر
$sections_query = "SELECT Section_id, Section_name FROM Section";
$sections_result = mysqli_query($conn, $sections_query);
$sections = [];
while ($section = mysqli_fetch_assoc($sections_result)) {
    $sections[] = $section;
}

// إغلاق الاتصال بقاعدة البيانات
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير المنتجات الراكدة - نظام إدارة المخزون</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #218838;
            --primary-dark: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #3498db;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --gray-color: #95a5a6;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .container {
            max-width: 98%;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(to left, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 20px 25px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 15px 25px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        
        .stat-card {
            flex: 1;
            min-width: 200px;
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: var(--gray-color);
        }
        
        .stat-value {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .stat-value.danger {
            color: var(--danger-color);
        }
        
        .report-tools {
            padding: 15px 25px;
            background-color: #fff;
            border-bottom: 1px solid #eee;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 15px;
        }
        
        .filters-container {
            flex: 1;
            min-width: 300px;
        }
        
        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 14px;
            color: var(--dark-color);
        }
        
        .filter-group select {
            width: 80%;
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            background-color: white;
            font-family: 'Tajawal', sans-serif;
        }
        
        .actions-container {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            height: fit-content;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-info {
            background-color: var(--info-color);
            color: white;
        }
        
        .btn-info:hover {
            background-color: #2980b9;
        }
        
        .btn-filter {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-filter:hover {
            background-color: #e67e22;
        }
        
        .table-container {
            padding: 0 20px 20px 20px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 15px;
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
        
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #eee;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-danger {
            background-color: #ffebee;
            color: var(--danger-color);
        }
        
        .badge-warning {
            background-color: #fff8e1;
            color: var(--warning-color);
        }
        
        .badge-success {
            background-color: #e8f5e9;
            color: var(--primary-dark);
        }
        
        .action-btns {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        
        .action-btn {
            padding: 5px 8px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .action-btn.edit {
            background-color: var(--primary-color);
            color: white;
        }
        
        .action-btn.delete {
            background-color: var(--danger-color);
            color: white;
        }
        
        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        /* إضافة أنماط جديدة لمخطط Bin */
        .bin-analysis {
            padding: 20px;
            margin: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        
        .bin-chart-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        
        .chart-container {
            flex: 1;
            min-width: 300px;
            height: 300px;
        }
        
        .bin-table {
            width: 100%;
            margin-top: 20px;
        }
        
        .bin-table th {
            background-color: var(--info-color);
        }
        
        @media (max-width: 768px) {
            .filters {
                grid-template-columns: 1fr;
            }
            
            .stat-card {
                min-width: 100%;
            }
            
            .report-tools {
                flex-direction: column;
            }
            
            .actions-container {
                justify-content: flex-start;
            }
            
            .chart-container {
                min-width: 100%;
            }
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
            .btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-box-open"></i>
                تقرير المنتجات الراكدة وتحليل Bin
            </h1>
        </div>
        
        <div class="stats-container">
            <div class="stat-card">
                <h3>إجمالي المنتجات</h3>
                <div class="stat-value"><?php echo $totalProducts; ?></div>
            </div>
            <div class="stat-card">
                <h3>المنتجات التي لم تباع</h3>
                <div class="stat-value danger"><?php echo $unsoldProducts; ?></div>
            </div>
            <div class="stat-card">
                <h3>المنتجات الراكدة</h3>
                <div class="stat-value danger"><?php echo $slowMovingProducts; ?></div>
            </div>
            <div class="stat-card">
                <h3>قيمة المخزون الراكد</h3>
                <div class="stat-value"><?php echo number_format($totalStockValue); ?> ريال</div>
            </div>
        </div>
        
        <!-- قسم تحليل Bin -->
        <div class="bin-analysis">
            <h2><i class="fas fa-chart-pie"></i> تحليل Bin للمنتجات الراكدة</h2>
            <p>هذا التحليل يظهر توزيع المنتجات الراكدة حسب فئات المخزون لمساعدتك في إدارة المساحات التخزينية بكفاءة.</p>
            
            <div class="bin-chart-container">
                <div class="chart-container">
                    <canvas id="binChart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="valueChart"></canvas>
                </div>
            </div>
            
            <table class="bin-table">
                <thead>
                    <tr>
                        <th>فئة Bin</th>
                        <th>عدد المنتجات</th>
                        <th>نسبة المنتجات</th>
                        <th>إجمالي القيمة</th>
                        <th>نسبة القيمة</th>
                    </tr>
                </thead>
                <tbody id="binTableBody">
                    <?php foreach ($binAnalysis as $bin): ?>
                    <tr>
                        <td><?php echo $bin['label']; ?></td>
                        <td><?php echo $bin['count']; ?></td>
                        <td><?php echo $bin['count_percentage']; ?>%</td>
                        <td><?php echo number_format($bin['value']); ?> ريال</td>
                        <td><?php echo $bin['value_percentage']; ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="report-tools">
            <div class="filters-container">
                <div class="filters">
                    <div class="filter-group">
                        <label for="section">القسم</label>
                        <select id="section">
                            <option value="all">جميع الأقسام</option>
                            <?php foreach ($sections as $section): ?>
                            <option value="<?php echo $section['Section_id']; ?>"><?php echo $section['Section_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="period">الفترة الزمنية</label>
                        <select id="period">
                            <option value="3">آخر 3 أشهر</option>
                            <option value="6" selected>آخر 6 أشهر</option>
                            <option value="12">آخر سنة</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sales">حد المبيعات</label>
                        <select id="sales">
                            <option value="5">أقل من 5 مبيعات</option>
                            <option value="10">أقل من 10 مبيعات</option>
                            <option value="0" selected>لم تباع أبداً</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="actions-container">
                <button class="btn btn-filter" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> تطبيق الفلتر
                </button>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> طباعة
                </button>
                <button class="btn btn-info" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> تصدير
                </button>
            </div>
        </div>
        
        <div class="table-container">
            <table id="slow-moving-items">
                <thead>
                    <tr>
                        <th width="50px">#</th>
                        <th width="80px">الصورة</th>
                        <th>اسم المنتج</th>
                        <th>القسم</th>
                        <th width="120px">عدد المبيعات</th>
                        <th width="120px">آخر بيع</th>
                        <th width="120px">المخزون</th>
                        <th width="120px">السعر</th>
                        <th width="120px">الحالة</th>
                        <th width="120px">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $index => $product): 
                        $status = '';
                        $badgeClass = '';
                        
                        if ($product['sales_count'] === null || $product['sales_count'] == 0) {
                            $status = 'لم يباع';
                            $badgeClass = 'badge-danger';
                        } elseif ($product['sales_count'] < 5) {
                            $status = 'راكد';
                            $badgeClass = 'badge-warning';
                        } else {
                            $status = 'طبيعي';
                            $badgeClass = 'badge-success';
                        }
                        
                        $lastSale = $product['last_sale_date'] ? date('d/m/Y', strtotime($product['last_sale_date'])) : 'لم يباع';
                    ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><img src="<?= "../images/product_images/" . $product['image_url'] ?>" class="product-img" alt="صورة المنتج"></td>
                        <td><?php echo $product['product_name']; ?></td>
                        <td><?php echo $product['category']; ?></td>
                        <td><?php echo $product['sales_count'] ?? 0; ?></td>
                        <td><?php echo $lastSale; ?></td>
                        <td><?php echo $product['stock_quantity']; ?></td>
                        <td><?php echo number_format($product['price']); ?> ريال</td>
                        <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $status; ?></span></td>
                        <td>
                            <div class="action-btns">
                                <button class="action-btn edit" title="تعديل المنتج">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete" title="حذف المنتج" data-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // بيانات تحليل Bin
        const binData = {
            categories: <?php echo json_encode(array_column($binAnalysis, 'label')); ?>,
            productsCount: <?php echo json_encode(array_column($binAnalysis, 'count')); ?>,
            totalValue: <?php echo json_encode(array_column($binAnalysis, 'value')); ?>
        };

        // دالة لتهيئة مخططات Bin
        function initBinCharts() {
            // مخطط دائري لعدد المنتجات حسب فئة Bin
            const binCtx = document.getElementById('binChart').getContext('2d');
            new Chart(binCtx, {
                type: 'pie',
                data: {
                    labels: binData.categories,
                    datasets: [{
                        data: binData.productsCount,
                        backgroundColor: [
                            '#e74c3c',
                            '#f39c12',
                            '#3498db',
                            '#95a5a6'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'توزيع المنتجات الراكدة حسب فئة Bin',
                            font: {
                                size: 16
                            }
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // مخطط دائري لقيمة المخزون حسب فئة Bin
            const valueCtx = document.getElementById('valueChart').getContext('2d');
            new Chart(valueCtx, {
                type: 'pie',
                data: {
                    labels: binData.categories,
                    datasets: [{
                        data: binData.totalValue,
                        backgroundColor: [
                            '#e74c3c',
                            '#f39c12',
                            '#3498db',
                            '#95a5a6'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'توزيع قيمة المخزون حسب فئة Bin',
                            font: {
                                size: 16
                            }
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // دالة تطبيق الفلاتر
        function applyFilters() {
            const section = document.getElementById('section').value;
            const period = document.getElementById('period').value;
            const sales = document.getElementById('sales').value;
            
            // إعادة تحميل الصفحة مع معلمات الفلتر
            window.location.href = `?section=${section}&period=${period}&sales=${sales}`;
        }
        
        // دالة تصدير إلى Excel
        function exportToExcel() {
            alert("سيتم تصدير البيانات إلى ملف Excel");
            // يمكنك هنا إضافة كود التصدير الفعلي إلى Excel
        }
        
        // إضافة مستمعين للأحداث لحذف المنتجات
        document.querySelectorAll('.action-btn.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                if (confirm("هل أنت متأكد من رغبتك في حذف هذا المنتج؟")) {
                    // إرسال طلب AJAX لحذف المنتج
                    fetch('delete_product.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("تم حذف المنتج بنجاح");
                            location.reload();
                        } else {
                            alert("حدث خطأ أثناء حذف المنتج");
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert("حدث خطأ أثناء حذف المنتج");
                    });
                }
            });
        });

        // تهيئة المخططات عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            initBinCharts();
        });
    </script>
</body>
</html>