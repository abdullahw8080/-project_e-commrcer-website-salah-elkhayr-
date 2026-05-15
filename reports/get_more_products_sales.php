<?php
// مسار الملف: C:\xampp\htdocs\project_e-commrcer\reports\top_products_report.php

// تضمين ملف الاتصال بقاعدة البيانات
require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقاعدة البيانات

/**
 * دالة لجلب الأقسام من قاعدة البيانات
 * @param mysqli $conn كائن اتصال قاعدة البيانات
 * @return array مصفوفة تحتوي على الأقسام (معرف القسم واسم القسم)
 */
function getSections($conn) {
    // استعلام SQL لاختيار جميع الأقسام
    $query = "SELECT Section_id, Section_name FROM Section";
    // تنفيذ الاستعلام
    $result = mysqli_query($conn, $query);
    // مصفوفة فارغة لتخزين الأقسام
    $sections = [];
    
    // إذا كان هناك نتائج وعدد الصفوف أكبر من صفر
    if ($result && mysqli_num_rows($result) > 0) {
        // اجلب كل صف كمجموعة ارتباطية وأضفه للمصفوفة
        while ($row = mysqli_fetch_assoc($result)) {
            $sections[] = $row;
        }
    }
    
    // إرجاع مصفوفة الأقسام
    return $sections;
}

/**
 * دالة لجلب المنتجات الأكثر مبيعاً حسب الفترة والقسم
 * @param mysqli $conn كائن اتصال قاعدة البيانات
 * @param string $period الفترة الزمنية (اليوم، الأسبوع، الشهر، السنة، الكل)
 * @param string $sectionId معرف القسم أو 'all' لجميع الأقسام
 * @return array مصفوفة تحتوي على المنتجات وإجمالي الإيرادات
 */
function getTopProducts($conn, $period, $sectionId) {
    // تهيئة شرط الفترة الزمنية
    $dateCondition = "";
    switch ($period) {
        case 'today':
            $dateCondition = "AND DATE(o.Or_date) = CURDATE()";
            break;
        case 'week':
            $dateCondition = "AND o.Or_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $dateCondition = "AND o.Or_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            break;
        case 'year':
            $dateCondition = "AND o.Or_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
            break;
        case 'all':
        default:
            $dateCondition = "";
    }
    
    // تهيئة شرط القسم
    $sectionCondition = "";
    if ($sectionId !== 'all') {
        $sectionCondition = "AND p.Section_id = " . intval($sectionId);
    }
    
    // استعلام SQL لجلب المنتجات الأكثر مبيعاً
    $query = "
        SELECT 
            p.P_id,
            p.P_name,
            s.Section_name,
            SUM(oi.quantity) AS total_quantity,
            SUM(oi.quantity * oi.price) AS total_revenue,
            (SELECT P_img FROM product_images WHERE P_id = p.P_id LIMIT 1) AS P_img
        FROM 
            order_items oi
        JOIN 
            products p ON oi.P_id = p.P_id
        JOIN 
            Orders o ON oi.or_id = o.Or_id
        JOIN 
            Section s ON p.Section_id = s.Section_id
        WHERE 
            1=1
            $dateCondition
            $sectionCondition
        GROUP BY 
            p.P_id
        ORDER BY 
            total_quantity DESC
        LIMIT 10
    ";
    
    // تنفيذ الاستعلام
    $result = mysqli_query($conn, $query);
    // مصفوفة فارغة للمنتجات
    $products = [];
    // تهيئة متغير إجمالي الإيرادات
    @$totalRevenue = 0;
    
    // إذا كان هناك نتائج وعدد الصفوف أكبر من صفر
    if ($result && mysqli_num_rows($result) > 0) {
        // اجلب كل صف كمجموعة ارتباطية
        while ($row = mysqli_fetch_assoc($result)) {
            // تنسيق إيرادات المنتج
            $row['total_revenue'] = ($row['total_revenue']);
            // إضافة إيرادات المنتج للإجمالي
            @$totalRevenue += $row['total_revenue'];
            // إضافة المنتج للمصفوفة
            $products[] = $row;
        }
    }
    
    // إرجاع النتائج
    return [
        'products' => $products,
        'total_revenue' => number_format(@$totalRevenue, )
    ];
}

// جلب الأقسام لعرضها في القائمة المنسدلة
$sections = getSections($conn);

// جلب المنتجات الأكثر مبيعاً (افتراضي: اليوم)
$period = isset($_GET['period']) ? $_GET['period'] : 'today';
$sectionId = isset($_GET['section']) ? $_GET['section'] : 'all';
$data = getTopProducts($conn, $period, $sectionId);
$topProducts = $data['products'];
@$totalRevenue = $data['total_revenue'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير المنتجات الأكثر مبيعاً - سلة الخير</title>
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
        select, input {
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
            background-color:rgb(33, 187, 66);
        }
        .btn-print {
            background-color: #6c757d;
        }
        .btn-print:hover {
            background-color: #5a6268;
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
        .chart-container {
            margin-top: 30px;
            height: 400px;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #777;
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>تقرير المنتجات الأكثر مبيعاً</h1>
        
        <div class="summary-card no-print">
            <i class="fas fa-chart-line"></i> إجمالي الإيرادات: <strong><?= $totalRevenue ?> ريال</strong>
        </div>
        
        <div class="filter-section no-print">
            <div class="filter-group">
                <label for="time-period">الفترة الزمنية:</label>
                <select id="time-period">
                    <option value="today" <?= $period === 'today' ? 'selected' : '' ?>>اليوم</option>
                    <option value="week" <?= $period === 'week' ? 'selected' : '' ?>>أسبوع</option>
                    <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>شهر</option>
                    <option value="year" <?= $period === 'year' ? 'selected' : '' ?>>سنة</option>
                    <option value="all" <?= $period === 'all' ? 'selected' : '' ?>>الكل</option>
                </select>
                
                <label for="section">القسم:</label>
                <select id="section">
                    <option value="all">جميع الأقسام</option>
                    <?php foreach ($sections as $section): ?>
                        <option value="<?= $section['Section_id'] ?>" <?= $sectionId == $section['Section_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($section['Section_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button class="btn" onclick="loadTopProducts()">
                    <i class="fas fa-filter"></i> تطبيق الفلتر
                </button>
            </div>
            
            <div>
                <button class="btn" onclick="exportReport()">
                    <i class="fas fa-file-excel"></i> تصدير Excel
                </button>
                <button class="btn btn-print" onclick="printPDF()">
                    <i class="fas fa-file-pdf"></i> طباعة PDF
                </button>
            </div>
        </div>
        
        <table id="products-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصورة</th>
                    <th>اسم المنتج</th>
                    <th>القسم</th>
                    <th>عدد المبيعات</th>
                    <th>الإيرادات (ريال)</th>
                </tr>
            </thead>
            <tbody id="products-data">
                <?php if (!empty($topProducts)): ?>
                    <?php foreach ($topProducts as $index => $product): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td>
                            <img src="<?=  htmlspecialchars("../images/product_images/".$product['P_img']);?>" 
                                 alt="<?= htmlspecialchars($product['P_name']) ?>" class="product-img">
                        </td>
                        <td><?= htmlspecialchars($product['P_name']) ?></td>
                        <td><?= htmlspecialchars($product['Section_name']) ?></td>
                        <td><?= $product['total_quantity'] ?></td>
                        <td><?= $product['total_revenue'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">لا توجد بيانات متاحة</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right;"><strong>الإجمالي:</strong></td>
                    <td><strong><?= $totalRevenue ?> ريال</strong></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="chart-container no-print">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <script>
        // تعريف jsPDF للاستخدام العام
const { jsPDF } = window.jspdf;
// متغير للرسم البياني
let salesChart;

/**
 * دالة لتحميل المنتجات الأكثر مبيعاً حسب الفلتر المحدد
 */
function loadTopProducts() {
    // الحصول على الفترة والقسم المحددين
    const period = document.getElementById('time-period').value;
    const section = document.getElementById('section').value;
    
    // إعادة تحميل الصفحة مع معايير الفلتر
    window.location.href = `?period=${period}&section=${section}`;
}

/**
 * دالة لتحديث الرسم البياني
 */
function updateChart() {
    // الحصول على عنصر canvas للرسم البياني
    const ctx = document.getElementById('salesChart').getContext('2d');
    // تحويل بيانات المنتجات من PHP إلى JavaScript
    const products = <?= json_encode($topProducts) ?>;
    
    // إذا كان هناك رسم بياني موجود، قم بإزالته أولاً
    if (salesChart) {
        salesChart.destroy();
    }
    
    // إذا لم يكن هناك منتجات، اخفي الرسم البياني
    if (products.length === 0) {
        document.getElementById('salesChart').style.display = 'none';
        return;
    }
    
    // إظهار الرسم البياني
    document.getElementById('salesChart').style.display = 'block';
    
    // تحضير بيانات الرسم البياني
    const labels = products.map(p => p.P_name);
    const salesData = products.map(p => p.total_quantity);
    const revenueData = products.map(p => parseFloat(p.total_revenue.replace(',', '')));
    
    // إنشاء رسم بياني جديد
    salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'عدد المبيعات',
                    data: salesData,
                    backgroundColor: 'rgba(11, 161, 0, 0.68)',
                    borderColor: 'rgba(1, 136, 32, 0.75)',
                    borderWidth: 1
                },
                {
                    label: 'الإيرادات (ريال)',
                    data: revenueData,
                    backgroundColor: 'rgb(0, 253, 17)',
                    borderColor: 'rgb(30, 175, 4)',
                    borderWidth: 1,
                    type: 'line',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'عدد المبيعات'
                    }
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'الإيرادات (ريال)'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'أفضل المنتجات مبيعاً'
                }
            }
        }
    });
}

/**
 * دالة لتصدير التقرير إلى Excel
 */
function exportReport() {
    // الحصول على جدول المنتجات
    const html = document.getElementById('products-table').outerHTML;
    // إنشاء ملف Excel
    const blob = new Blob([html], {type: 'application/vnd.ms-excel'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'تقرير_المنتجات_الأكثر_مبيعاً.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

/**
 * دالة لطباعة التقرير كـ PDF
 */
function printPDF() {
    // الحصول على عنصر التقرير
    const element = document.querySelector('.container');
    
    // تحويل العنصر إلى صورة
    html2canvas(element, {
        scale: 2,
        logging: false,
        useCORS: true
    }).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        // إنشاء مستند PDF جديد
        const pdf = new jsPDF('p', 'mm', 'a4');
        const imgWidth = 210; // عرض A4 بالميليمتر
        const pageHeight = 295; // ارتفاع A4 بالميليمتر
        const imgHeight = canvas.height * imgWidth / canvas.width;
        let heightLeft = imgHeight;
        let position = 0;
        
        // إضافة الصورة لملف PDF
        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
        
        // إضافة صفحات إضافية إذا لزم الأمر
        while (heightLeft >= 0) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
        }
        
        // حفظ ملف PDF
        pdf.save('تقرير_المنتجات_الأكثر_مبيعاً.pdf');
    });
}

// تحديث الرسم البياني عند تحميل الصفحة
window.onload = function() {
    updateChart();
};
    </script>
</body>
</html>