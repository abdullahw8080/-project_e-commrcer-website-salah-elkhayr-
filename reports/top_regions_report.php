<?php
// الاتصال بقاعدة البيانات
require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقاعدة البيانات


// دالة لجلب البيانات مع الفلاتر
function getFilteredData($conn, $period = '30', $regionType = 'all', $startDate = null, $endDate = null) {
    // بناء استعلام WHERE حسب الفلاتر
    $whereClause = "";
    
    // فلترة الفترة الزمنية
    if ($period != 'custom') {
        $whereClause .= " AND o.Or_date >= DATE_SUB(CURDATE(), INTERVAL $period DAY)";
    } else if ($startDate && $endDate) {
        $whereClause .= " AND o.Or_date BETWEEN '$startDate' AND '$endDate'";
    }
    
    // فلترة نوع المنطقة
    if ($regionType != 'all') {
        // يمكن تطوير هذا الجزء حسب الحاجة
    }
    
    // استعلامات SQL لجلب البيانات مع الفلاتر
    $query_total_regions = "SELECT COUNT(DISTINCT oa.Client_street) AS total_regions 
                           FROM Orders_address oa 
                           JOIN Orders o ON oa.OD_id = o.OD_id 
                           WHERE 1=1 $whereClause";
    
    $query_top_region = "SELECT oa.Client_street, COUNT(*) AS order_count 
                         FROM Orders_address oa 
                         JOIN Orders o ON oa.OD_id = o.OD_id
                         WHERE 1=1 $whereClause
                         GROUP BY oa.Client_street 
                         ORDER BY order_count DESC 
                         LIMIT 1";
    
    $query_top_avg_order = "SELECT AVG(o.Total_price) AS avg_order 
                           FROM Orders o
                           WHERE 1=1 $whereClause";
    
    $query_total_sales = "SELECT SUM(o.Total_price) AS total_sales 
                         FROM Orders o
                         WHERE 1=1 $whereClause";
    
    $query_regions_data = "SELECT 
                            oa.Client_street AS name,
                            COUNT(*) AS orders,
                            SUM(o.Total_price) AS sales,
                            AVG(o.Total_price) AS avg_order,
                            COUNT(DISTINCT oa.Client_phone) AS customers,
                            (COUNT(*) / (SELECT COUNT(*) FROM Orders o WHERE 1=1 $whereClause)) * 100 AS market_share
                          FROM Orders_address oa
                          JOIN Orders o ON oa.OD_id = o.OD_id
                          WHERE 1=1 $whereClause
                          GROUP BY oa.Client_street
                          ORDER BY orders DESC
                          LIMIT 10";

    // تنفيذ الاستعلامات
    $data = array();
    
    try {
        $result = mysqli_query($conn, $query_total_regions);
        $data['total_regions'] = $result ? mysqli_fetch_assoc($result)['total_regions'] : 0;
        
        $result = mysqli_query($conn, $query_top_region);
        $data['top_region'] = $result ? mysqli_fetch_assoc($result) : ['Client_street' => 'غير متاح'];
        
        $result = mysqli_query($conn, $query_top_avg_order);
        $data['top_avg_order'] = $result ? mysqli_fetch_assoc($result) : ['avg_order' => 0];
        
        $result = mysqli_query($conn, $query_total_sales);
        $data['total_sales'] = $result ? mysqli_fetch_assoc($result) : ['total_sales' => 0];
        
        $data['regions_data'] = mysqli_query($conn, $query_regions_data);
    } catch (mysqli_sql_exception $e) {
        die("حدث خطأ في استعلام قاعدة البيانات: " . $e->getMessage());
    }

    return $data;
}

// جلب البيانات بناء على الفلاتر
$period = isset($_POST['period']) ? $_POST['period'] : '30';
$regionType = isset($_POST['regionType']) ? $_POST['regionType'] : 'all';
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null;
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null;

$filteredData = getFilteredData($conn, $period, $regionType, $startDate, $endDate);

// تحضير البيانات للرسم البياني والجدول
$labels = [];
$ordersData = [];
$salesData = [];
$table_data = [];

if ($filteredData['regions_data']) {
    while ($row = mysqli_fetch_assoc($filteredData['regions_data'])) {
        $labels[] = $row['name'];
        $ordersData[] = $row['orders'];
        $salesData[] = $row['sales'] / 1000; // تحويل إلى آلاف
        $table_data[] = $row;
    }
}

// إذا كان الطلب AJAX، إرجاع البيانات كـ JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode([
        'labels' => $labels,
        'ordersData' => $ordersData,
        'salesData' => $salesData,
        'table_data' => $table_data,
        'stats' => [
            'total_regions' => $filteredData['total_regions'],
            'top_region' => $filteredData['top_region']['Client_street'],
            'top_avg_order' => number_format($filteredData['top_avg_order']['avg_order'], 0) . ' ريال',
            'total_sales' => number_format($filteredData['total_sales']['total_sales'], 0) . ' ريال'
        ]
    ]);
    exit;
}

// إغلاق الاتصال
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير المناطق الأكثر طلباً - نظام إدارة المبيعات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        :root {
            --primary-color: #218838;
            --primary-dark: #2980b9;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
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
            background: linear-gradient( var(--primary-color));
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
        
        .stat-value.primary {
            color: var(--primary-color);
        }
        
        .stat-value.success {
            color: var(--secondary-color);
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
        
        .filter-group select, .filter-group input {
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
        
        .btn-success {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        .btn-filter {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-filter:hover {
            background-color: #e67e22;
        }
        
        .visualization-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }
        
        .visualization-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            width: 100%;
        }
        
        .visualization-col {
            flex: 1;
            min-width: 300px;
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            margin-top: 0;
            margin-bottom: 15px;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chart-container {
            width: 100%;
            height: 300px;
            position: relative;
        }
        
        #map {
            width: 100%;
            height: 300px;
            border-radius: 5px;
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
        
        .progress-container {
            width: 100%;
            background-color: #f1f1f1;
            border-radius: 5px;
            margin-top: 5px;
        }
        
        .progress-bar {
            height: 10px;
            border-radius: 5px;
            background-color: var(--primary-color);
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
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
            
            .chart-container, #map {
                height: 250px;
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
            .btn, .filters-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-map-marked-alt"></i>
                تقرير المناطق الأكثر طلباً
            </h1>
        </div>
        
        <div class="stats-container">
            <div class="stat-card">
                <h3>إجمالي المناطق النشطة</h3>
                <div class="stat-value primary" id="totalRegions"><?php echo $filteredData['total_regions']; ?></div>
            </div>
            <div class="stat-card">
                <h3>المنطقة الأكثر طلباً</h3>
                <div class="stat-value success" id="topRegion"><?php echo $filteredData['top_region']['Client_street']; ?></div>
            </div>
            <div class="stat-card">
                <h3>أعلى متوسط قيمة طلب</h3>
                <div class="stat-value primary" id="topAvgOrder"><?php echo number_format($filteredData['top_avg_order']['avg_order'], 0) . ' ريال'; ?></div>
            </div>
            <div class="stat-card">
                <h3>إجمالي المبيعات</h3>
                <div class="stat-value success" id="totalSales"><?php echo number_format($filteredData['total_sales']['total_sales'], 0) . ' ريال'; ?></div>
            </div>
        </div>
        
        <div class="report-tools">
            <div class="filters-container">
                <div class="filters">
                    <div class="filter-group">
                        <label for="period">الفترة الزمنية</label>
                        <select id="period" name="period">
                            <option value="7">آخر أسبوع</option>
                            <option value="30" selected>آخر شهر</option>
                            <option value="90">آخر 3 أشهر</option>
                            <option value="180">آخر 6 أشهر</option>
                            <option value="365">آخر سنة</option>
                            <option value="custom">مخصص</option>
                        </select>
                    </div>
                    
                    <div class="filter-group" id="customDateRange" style="display:none;">
                        <label for="startDate">من تاريخ</label>
                        <input type="date" id="startDate" name="startDate">
                        
                        <label for="endDate" style="margin-top:10px;">إلى تاريخ</label>
                        <input type="date" id="endDate" name="endDate">
                    </div>
                    
                    <div class="filter-group">
                        <label for="regionType">نوع المنطقة</label>
                        <select id="regionType" name="regionType">
                            <option value="all">الكل</option>
                            <option value="city">مدينة</option>
                            <option value="region">منطقة</option>
                            <option value="neighborhood">حي</option>
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
                <button class="btn btn-success" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> تصدير
                </button>
            </div>
        </div>
        
        <div class="visualization-container">
            <div class="visualization-row">
                <div class="visualization-col">
                    <h3 class="section-title"><i class="fas fa-chart-bar"></i> توزيع الطلبات حسب المناطق</h3>
                    <div class="chart-container">
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
                
                <div class="visualization-col">
                    <h3 class="section-title"><i class="fas fa-chart-pie"></i> الحصة السوقية للمناطق</h3>
                    <div class="chart-container">
                        <canvas id="marketShareChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table-container">
            <div class="loading" id="loadingIndicator">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>جاري تحميل البيانات...</p>
            </div>
            <table id="topRegionsTable">
                <thead>
                    <tr>
                        <th width="50px">#</th>
                        <th>اسم المنطقة / المدينة</th>
                        <th width="150px">عدد الطلبات</th>
                        <th width="150px">إجمالي المبيعات</th>
                        <th width="150px">متوسط قيمة الطلب</th>
                        <th width="150px">عدد العملاء</th>
                        <th width="100px">الحصة السوقية</th>
                    </tr>
                </thead>
                <tbody id="regionsData">
                    <?php
                    $counter = 1;
                    $total_orders = array_sum($ordersData);
                    foreach ($table_data as $row) {
                        $market_share = ($row['orders'] / $total_orders) * 100;
                        echo '<tr>';
                        echo '<td>' . $counter++ . '</td>';
                        echo '<td><strong>' . $row['name'] . '</strong><div class="progress-container"><div class="progress-bar" style="width: ' . number_format($market_share, 1) . '%"></div></div></td>';
                        echo '<td>' . number_format($row['orders']) . '</td>';
                        echo '<td>' . number_format($row['sales']) . ' ريال</td>';
                        echo '<td>' . number_format($row['avg_order']) . ' ريال</td>';
                        echo '<td>' . number_format($row['customers']) . '</td>';
                        echo '<td><span class="badge badge-primary">' . number_format($market_share, 1) . '%</span></td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // بيانات الرسم البياني من PHP
        const labels = <?php echo json_encode($labels); ?>;
        const ordersData = <?php echo json_encode($ordersData); ?>;
        const salesData = <?php echo json_encode($salesData); ?>;
        const tableData = <?php echo json_encode($table_data); ?>;
        
        let ordersChart, marketShareChart;
        
        // دالة تهيئة المخططات البيانية
        function initCharts() {
            // تدمير المخططات القديمة إذا كانت موجودة
            if (ordersChart) ordersChart.destroy();
            if (marketShareChart) marketShareChart.destroy();
            
            // تهيئة المخطط العمودي
            const ordersCtx = document.getElementById('ordersChart').getContext('2d');
            ordersChart = new Chart(ordersCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'عدد الطلبات',
                            data: ordersData,
                            backgroundColor: '#3498db',
                            borderColor: '#2980b9',
                            borderWidth: 1
                        },
                        {
                            label: 'إجمالي المبيعات (ألف ريال)',
                            data: salesData,
                            backgroundColor: '#2ecc71',
                            borderColor: '#27ae60',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            rtl: true
                        },
                        title: {
                            display: true,
                            text: 'توزيع الطلبات والمبيعات حسب المناطق',
                            font: {
                                size: 16
                            }
                        }
                    }
                }
            });
            
            // تهيئة مخطط Pie للحصة السوقية
            const marketShareData = tableData.map(item => item.market_share);
            const backgroundColors = [
                '#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6',
                '#1abc9c', '#d35400', '#34495e', '#16a085', '#c0392b'
            ];
            
            const marketShareCtx = document.getElementById('marketShareChart').getContext('2d');
            marketShareChart = new Chart(marketShareCtx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: marketShareData,
                        backgroundColor: backgroundColors,
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
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    return `${label}: ${value.toFixed(1)}%`;
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'الحصة السوقية للمناطق',
                            font: {
                                size: 16
                            }
                        }
                    }
                }
            });
        }
        
        // دالة تطبيق الفلاتر باستخدام AJAX
        function applyFilters() {
            const period = document.getElementById('period').value;
            const regionType = document.getElementById('regionType').value;
            const startDate = document.getElementById('startDate')?.value;
            const endDate = document.getElementById('endDate')?.value;
            
            // إظهار مؤشر التحميل
            document.getElementById('loadingIndicator').style.display = 'block';
            document.getElementById('regionsData').innerHTML = '';
            
            // إعداد بيانات الإرسال
            const formData = new FormData();
            formData.append('apply_filters', true);
            formData.append('period', period);
            formData.append('regionType', regionType);
            if (period === 'custom') {
                formData.append('startDate', startDate);
                formData.append('endDate', endDate);
            }
            
            // إرسال الطلب AJAX
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // تحديث الإحصائيات
                document.getElementById('totalRegions').textContent = data.stats.total_regions;
                document.getElementById('topRegion').textContent = data.stats.top_region;
                document.getElementById('topAvgOrder').textContent = data.stats.top_avg_order;
                document.getElementById('totalSales').textContent = data.stats.total_sales;
                
                // تحديث الجدول
                let tableHtml = '';
                const totalOrders = data.ordersData.reduce((a, b) => a + b, 0);
                
                data.table_data.forEach((row, index) => {
                    const marketShare = (row.orders / totalOrders) * 100;
                    tableHtml += `
                        <tr>
                            <td>${index + 1}</td>
                            <td><strong>${row.name}</strong><div class="progress-container"><div class="progress-bar" style="width: ${marketShare.toFixed(1)}%"></div></div></td>
                            <td>${row.orders.toLocaleString()}</td>
                            <td>${row.sales.toLocaleString()} ريال</td>
                            <td>${row.avg_order.toLocaleString()} ريال</td>
                            <td>${row.customers.toLocaleString()}</td>
                            <td><span class="badge badge-primary">${marketShare.toFixed(1)}%</span></td>
                        </tr>
                    `;
                });
                
                document.getElementById('regionsData').innerHTML = tableHtml;
                
                // إعادة تهيئة المخططات البيانية
                initCharts(data.labels, data.ordersData, data.salesData, data.table_data);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء جلب البيانات: ' + error.message);
            })
            .finally(() => {
                document.getElementById('loadingIndicator').style.display = 'none';
            });
        }
        
        // دالة تصدير إلى Excel
        function exportToExcel() {
            // يمكنك استخدام مكتبة مثل SheetJS أو TableExport هنا
            alert("سيتم تصدير البيانات إلى ملف Excel");
            
            // مثال بسيط لتصدير الجدول
            const table = document.getElementById('topRegionsTable');
            const html = table.outerHTML;
            const blob = new Blob([html], {type: 'application/vnd.ms-excel'});
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'تقرير_المناطق.xls';
            a.click();
        }
        
        // تغيير عرض فلتر التاريخ المخصص
        document.getElementById('period').addEventListener('change', function() {
            const customDateRange = document.getElementById('customDateRange');
            customDateRange.style.display = this.value === 'custom' ? 'block' : 'none';
        });

        // تهيئة التقرير عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
        });
    </script>
</body>
</html>