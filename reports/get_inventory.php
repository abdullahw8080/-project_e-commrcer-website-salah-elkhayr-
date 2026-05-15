<?php
// الاتصال بقاعدة البيانات
require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقاعدة البيانات

// جلب الأقسام من قاعدة البيانات
$sections = [];
$query = "SELECT Section_id, Section_name FROM Section";
$result = mysqli_query($conn, $query);
if ($result) {
    $sections = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// جلب إحصائيات المخزون
$stats = [
    'total_products' => 0,
    'in_stock' => 0,
    'low_stock' => 0,
    'out_of_stock' => 0
];

$query = "SELECT COUNT(*) as total_products FROM products";
$result = mysqli_query($conn, $query);
if ($result) {
    $stats['total_products'] = mysqli_fetch_assoc($result)['total_products'];
}

$query = "SELECT COUNT(*) as in_stock FROM products WHERE P_quantity > 10";
$result = mysqli_query($conn, $query);
if ($result) {
    $stats['in_stock'] = mysqli_fetch_assoc($result)['in_stock'];
}

$query = "SELECT COUNT(*) as low_stock FROM products WHERE P_quantity BETWEEN 1 AND 10";
$result = mysqli_query($conn, $query);
if ($result) {
    $stats['low_stock'] = mysqli_fetch_assoc($result)['low_stock'];
}

$query = "SELECT COUNT(*) as out_of_stock FROM products WHERE P_quantity <= 0";
$result = mysqli_query($conn, $query);
if ($result) {
    $stats['out_of_stock'] = mysqli_fetch_assoc($result)['out_of_stock'];
}

// جلب بيانات المخزون
$inventory = [];
$query = "SELECT 
            p.P_id, 
            p.P_name, 
            p.P_new_price, 
            p.P_quantity as quantity_available,
            s.Section_name,
            (SELECT pi.P_img FROM product_images pi WHERE pi.P_id = p.P_id LIMIT 1) as P_img
          FROM products p
          JOIN Section s ON p.Section_id = s.Section_id";
$result = mysqli_query($conn, $query);
if ($result) {
    $inventory = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    // حساب مبيعات الشهر لكل منتج
    foreach ($inventory as &$product) {
        $query = "SELECT SUM(oi.quantity) as monthly_sales
                  FROM order_items oi
                  JOIN Orders o ON oi.or_id = o.Or_id
                  WHERE oi.P_id = {$product['P_id']} 
                  AND o.Or_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        $sales_result = mysqli_query($conn, $query);
        if ($sales_result) {
            $sales = mysqli_fetch_assoc($sales_result);
            $product['monthly_sales'] = $sales['monthly_sales'] ?? 0;
        }
    }
}

// إذا كان هناك طلب AJAX
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    if ($_GET['ajax'] === 'get_inventory') {
        $section = $_GET['section'] ?? 'all';
        $status = $_GET['status'] ?? 'all';
        
        $query = "SELECT 
                    p.P_id, 
                    p.P_name, 
                    p.P_new_price, 
                    p.P_quantity as quantity_available,
                    s.Section_name,
                    (SELECT pi.P_img FROM product_images pi WHERE pi.P_id = p.P_id LIMIT 1) as P_img
                  FROM products p
                  JOIN Section s ON p.Section_id = s.Section_id";
        
        $conditions = [];
        
        if ($section !== 'all') {
            $conditions[] = "p.Section_id = $section";
        }
        
        if ($status !== 'all') {
            switch ($status) {
                case 'in-stock':
                    $conditions[] = "p.P_quantity > 10";
                    break;
                case 'low-stock':
                    $conditions[] = "p.P_quantity BETWEEN 1 AND 10";
                    break;
                case 'out-of-stock':
                    $conditions[] = "p.P_quantity <= 0";
                    break;
            }
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $result = mysqli_query($conn, $query);
        $products = [];
        if ($result) {
            $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            foreach ($products as &$product) {
                $query = "SELECT SUM(oi.quantity) as monthly_sales
                          FROM order_items oi
                          JOIN Orders o ON oi.or_id = o.Or_id
                          WHERE oi.P_id = {$product['P_id']} 
                          AND o.Or_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                $sales_result = mysqli_query($conn, $query);
                if ($sales_result) {
                    $sales = mysqli_fetch_assoc($sales_result);
                    $product['monthly_sales'] = $sales['monthly_sales'] ?? 0;
                }
            }
        }
        
        echo json_encode(['products' => $products]);
        exit;
    }
    
    if ($_GET['ajax'] === 'get_stats') {
        $stats = [
            'total_products' => 0,
            'in_stock' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0
        ];
        
        $query = "SELECT COUNT(*) as total_products FROM products";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $stats['total_products'] = mysqli_fetch_assoc($result)['total_products'];
        }
        
        $query = "SELECT COUNT(*) as in_stock FROM products WHERE P_quantity > 10";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $stats['in_stock'] = mysqli_fetch_assoc($result)['in_stock'];
        }
        
        $query = "SELECT COUNT(*) as low_stock FROM products WHERE P_quantity BETWEEN 1 AND 10";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $stats['low_stock'] = mysqli_fetch_assoc($result)['low_stock'];
        }
        
        $query = "SELECT COUNT(*) as out_of_stock FROM products WHERE P_quantity <= 0";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $stats['out_of_stock'] = mysqli_fetch_assoc($result)['out_of_stock'];
        }
        
        echo json_encode($stats);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير المخزون والمنتجات - سلة الخير</title>
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
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-danger {
            background-color: #e74c3c;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .btn-success {
            background-color: #2ecc71;
        }
        .btn-success:hover {
            background-color: #27ae60;
        }
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
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
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
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
            background-color: #3498db;
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
        .low-stock {
            background-color: #fff3cd;
        }
        .out-of-stock {
            background-color: #f8d7da;
        }
        .in-stock {
            background-color: #d4edda;
        }
        .stock-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .close {
            font-size: 24px;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .loading {
            text-align: center;
            padding: 20px;
            display: none;
        }
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }    </style>
</head>
<body>
    <div class="container">
        <h1>تقرير المخزون والمنتجات</h1>
        
        <div class="filter-section">
            <div class="filter-group">
                <label for="section">القسم:</label>
                <select id="section">
                    <option value="all">جميع الأقسام</option>
                    <?php foreach ($sections as $section): ?>
                        <option value="<?= $section['Section_id'] ?>"><?= htmlspecialchars($section['Section_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <label for="stock-status">حالة المخزون:</label>
                <select id="stock-status">
                    <option value="all">الكل</option>
                    <option value="in-stock">متوفر</option>
                    <option value="low-stock">كمية قليلة</option>
                    <option value="out-of-stock">نفذ من المخزون</option>
                </select>
                
                <button class="btn" id="apply-filter">تطبيق الفلتر</button>
            </div>
            
            <div>
                <button class="btn" id="export-report">تصدير التقرير</button>
                <button class="btn" id="print-report">طباعة التقرير</button>
                <button class="btn btn-success" id="add-product">إضافة منتج جديد</button>
            </div>
        </div>
        
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-label">إجمالي المنتجات</div>
                <div class="stat-value" id="total-products"><?= $stats['total_products'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">منتجات متوفرة</div>
                <div class="stat-value" id="in-stock"><?= $stats['in_stock'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">كمية قليلة</div>
                <div class="stat-value" id="low-stock"><?= $stats['low_stock'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">نفذ من المخزون</div>
                <div class="stat-value" id="out-of-stock"><?= $stats['out_of_stock'] ?></div>
            </div>
        </div>
        
        <div class="loading" id="loading">
            <div class="loading-spinner"></div>
            <p>جاري تحميل البيانات...</p>
        </div>
        
        <table id="inventory-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصورة</th>
                    <th>اسم المنتج</th>
                    <th>القسم</th>
                    <th>السعر</th>
                    <th>الكمية المتاحة</th>
                    <th>حالة المخزون</th>
                    <th>مبيعات الشهر</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody id="inventory-data">
                <?php foreach ($inventory as $index => $product): ?>
                    <?php
                    $statusClass = '';
                    $statusText = '';
                    if ($product['quantity_available'] <= 0) {
                        $statusClass = 'out-of-stock';
                        $statusText = 'نفذ من المخزون';
                    } elseif ($product['quantity_available'] < 10) {
                        $statusClass = 'low-stock';
                        $statusText = 'كمية قليلة';
                    } else {
                        $statusClass = 'in-stock';
                        $statusText = 'متوفر';
                    }
                    ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><img src="<?=  htmlspecialchars("../images/product_images/".$product['P_img']);?>" 
                                 alt="<?= htmlspecialchars($product['P_name']) ?>" class="product-img"></td>
                        <td><?= htmlspecialchars($product['P_name']) ?></td>
                        <td><?= htmlspecialchars($product['Section_name']) ?></td>
                        <td><?= $product['P_new_price'] ?> ر.س</td>
                        <td><?= $product['quantity_available'] ?></td>
                        <td><span class="stock-status <?= $statusClass ?>"><?= $statusText ?></span></td>
                        <td><?= $product['monthly_sales'] ?? 0 ?></td>
                        <td>
                            <button class="btn edit-product"><a href="../admin/‏‏update_product.php?upatat_product_id=<?php echo $product['P_id']; ?>">تعديل</a></button>
                            <button class="btn btn-danger delete-product" data-id="<?= $product['P_id'] ?>">حذف</button>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // متغيرات عامة
        let currentProductId = null;
        
        // عناصر DOM
        const loadingElement = document.getElementById('loading');
        const inventoryTable = document.getElementById('inventory-table');
        const inventoryData = document.getElementById('inventory-data');
        const applyFilterBtn = document.getElementById('apply-filter');
        const exportReportBtn = document.getElementById('export-report');
        const printReportBtn = document.getElementById('print-report');
        const addProductBtn = document.getElementById('add-product');
        const editModal = document.getElementById('editModal');
        const closeModal = document.querySelector('.close');
        const productForm = document.getElementById('product-form');
        
        // أحداث الفلترة
        applyFilterBtn.addEventListener('click', loadInventory);
        
        // أحداث الطباعة والتصدير
        printReportBtn.addEventListener('click', () => {
            window.print();
        });
        
        exportReportBtn.addEventListener('click', () => {
            const section = document.getElementById('section').value;
            const status = document.getElementById('stock-status').value;
            
            // يمكن تطوير هذه الوظيفة لتصدير البيانات بصيغة Excel أو CSV
            alert(`سيتم تصدير البيانات حسب الفلتر الحالي:\nالقسم: ${section}\nحالة المخزون: ${status}`);
        });
        
        // أحداث النموذج
        addProductBtn.addEventListener('click', () => {
            document.getElementById('product-id').value = '';
            document.getElementById('product-name').value = '';
            document.getElementById('product-price').value = '';
            document.getElementById('product-quantity').value = '';
            document.getElementById('product-description').value = '';
            document.getElementById('product-weight').value = '';
            document.getElementById('product-discount').value = '0';
            document.getElementById('product-section').value = '<?= $sections[0]['Section_id'] ?? '' ?>';
            editModal.style.display = 'block';
        });
        
        closeModal.addEventListener('click', () => {
            editModal.style.display = 'none';
        });
        
        window.addEventListener('click', (event) => {
            if (event.target === editModal) {
                editModal.style.display = 'none';
            }
        });
        

        
        // دالة تحميل المخزون
        function loadInventory() {
            const section = document.getElementById('section').value;
            const status = document.getElementById('stock-status').value;
            
            showLoading();
            
            fetch(`?ajax=get_inventory&section=${section}&status=${status}`)
                .then(response => response.json())
                .then(data => {
                    displayInventoryData(data.products);
                    updateStats();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ أثناء جلب البيانات');
                })
                .finally(() => {
                    hideLoading();
                });
        }
        
        // دالة عرض بيانات المخزون
        function displayInventoryData(products) {
            inventoryData.innerHTML = '';
            
            products.forEach((product, index) => {
                const row = document.createElement('tr');
                
                let statusClass, statusText;
                if (product.quantity_available <= 0) {
                    statusClass = 'out-of-stock';
                    statusText = 'نفذ من المخزون';
                } else if (product.quantity_available < 10) {
                    statusClass = 'low-stock';
                    statusText = 'كمية قليلة';
                } else {
                    statusClass = 'in-stock';
                    statusText = 'متوفر';
                }
                
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td><img src="${product.P_img || 'images/default-product.png'}" alt="${product.P_name}" class="product-img"></td>
                    <td>${product.P_name}</td>
                    <td>${product.Section_name}</td>
                    <td>${product.P_new_price} ر.س</td>
                    <td>${product.quantity_available}</td>
                    <td><span class="stock-status ${statusClass}">${statusText}</span></td>
                    <td>${product.monthly_sales || 0}</td>
                    <td>
                        <button class="btn edit-product" data-id="${product.P_id}">تعديل</button>
                        <button class="btn btn-danger delete-product" data-id="${product.P_id}">حذف</button>
                    </td>
                `;
                inventoryData.appendChild(row);
            });
        }
        
        // دالة تحديث الإحصائيات
        function updateStats() {
            fetch('?ajax=get_stats')
                .then(response => response.json())
                .then(stats => {
                    document.getElementById('total-products').textContent = stats.total_products;
                    document.getElementById('in-stock').textContent = stats.in_stock;
                    document.getElementById('low-stock').textContent = stats.low_stock;
                    document.getElementById('out-of-stock').textContent = stats.out_of_stock;
                });
        }
        
        // دالة تعديل المنتج
        function editProduct(productId) {
            showLoading();
            
            fetch(`get_product.php?id=${productId}`)
                .then(response => response.json())
                .then(product => {
                    document.getElementById('modal-title').textContent = 'تعديل المنتج';
                    document.getElementById('product-id').value = product.P_id;
                    document.getElementById('product-name').value = product.P_name;
                    document.getElementById('product-price').value = product.P_new_price;
                    document.getElementById('product-quantity').value = product.P_quantity;
                    document.getElementById('product-description').value = product.p_description || '';
                    document.getElementById('product-weight').value = product.p_weight || '';
                    document.getElementById('product-discount').value = product.discount || 0;
                    document.getElementById('product-section').value = product.Section_id;
                    
                    editModal.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ أثناء جلب بيانات المنتج');
                })
                .finally(() => {
                    hideLoading();
                });
        }
        
        // دالة حذف المنتج
        function deleteProduct(productId) {
            showLoading();
            
            fetch('delete_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: productId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تم حذف المنتج بنجاح');
                    loadInventory();
                } else {
                    alert('حدث خطأ أثناء حذف المنتج: ' + (data.error || ''));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء حذف المنتج');
            })
            .finally(() => {
                hideLoading();
            });
        }
        
        // إرسال نموذج المنتج
        productForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const productId = document.getElementById('product-id').value;
            const isNew = productId === '';
            
            const formData = new FormData();
            formData.append('id', productId);
            formData.append('name', document.getElementById('product-name').value);
            formData.append('price', document.getElementById('product-price').value);
            formData.append('quantity', document.getElementById('product-quantity').value);
            formData.append('section_id', document.getElementById('product-section').value);
            formData.append('description', document.getElementById('product-description').value);
            formData.append('weight', document.getElementById('product-weight').value);
            formData.append('discount', document.getElementById('product-discount').value);
            
            const imageFile = document.getElementById('product-image').files[0];
            if (imageFile) {
                formData.append('image', imageFile);
            }
            
            showLoading();
            
            fetch(isNew ? 'add_product.php' : 'update_product.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(isNew ? 'تم إضافة المنتج بنجاح' : 'تم تحديث المنتج بنجاح');
                    editModal.style.display = 'none';
                    loadInventory();
                } else {
                    alert('حدث خطأ: ' + (data.error || ''));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء حفظ البيانات');
            })
            .finally(() => {
                hideLoading();
            });
        });
        
        // دوال مساعدة
        function showLoading() {
            loadingElement.style.display = 'block';
            inventoryTable.style.opacity = '0.5';
        }
        
        function hideLoading() {
            loadingElement.style.display = 'none';
            inventoryTable.style.opacity = '1';
        }
        
        // تحميل البيانات عند فتح الصفحة
        document.addEventListener('DOMContentLoaded', () => {
            // البيانات تم تحميلها بالفعل من خلال PHP
        });
    </script>
</body>
</html>