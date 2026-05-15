<?php 
 require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
 require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقاعدة البيانات

// استعلامات للحصول على إحصائيات المخزون
$total_products_query = "SELECT COUNT(*) as total FROM products";
$in_stock_query = "SELECT COUNT(*) as in_stock FROM products WHERE P_quantity > 10";
$low_stock_query = "SELECT COUNT(*) as low_stock FROM products WHERE P_quantity > 0 AND P_quantity <= 10";
$out_of_stock_query = "SELECT COUNT(*) as out_of_stock FROM products WHERE P_quantity <= 0";

$total_products_result = mysqli_query($conn, $total_products_query);
$in_stock_result = mysqli_query($conn, $in_stock_query);
$low_stock_result = mysqli_query($conn, $low_stock_query);
$out_of_stock_result = mysqli_query($conn, $out_of_stock_query);

$total_products = mysqli_fetch_assoc($total_products_result)['total'];
$in_stock = mysqli_fetch_assoc($in_stock_result)['in_stock'];
$low_stock = mysqli_fetch_assoc($low_stock_result)['low_stock'];
$out_of_stock = mysqli_fetch_assoc($out_of_stock_result)['out_of_stock'];

//<!--=========================(حذف منتج)========================>>--> 
if (isset($_GET['delet_product_id'])) {
    $id = $_GET['delet_product_id'];
    $query = "DELETE FROM products WHERE P_id = '$id'";
    $delet = mysqli_query($conn, $query);
    if ($delet) {
        echo '<script>alert("تم حذف المنتج بنجاح");</script>';
    } else {
        echo '<script>alert("فشلة عملية حذف المنتج");</script>';
    }
    // إعادة توجيه المستخدم إلى نفس الصفحة لتحديث الجدول
    echo '<script>window.location.href = "get_inventory_stats.php";</script>';
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
            background-color: #218838;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color:rgb(12, 191, 51);
        }
        .btn-danger {
            background-color: #e74c3c;
        }
        .btn-danger:hover {
            background-color: #c0392b;
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
            background-color:rgb(6, 162, 1);
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
        a {
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>تقرير المخزون والمنتجات</h1>
        
        <form method="get">
            <div class="filter-section">
                <div class="filter-group">
                    <label for="section">القسم:</label>
                    <select id="section" name="Pr_section">
                        <option value="الكل">جميع الأقسام</option>
                        <?php
                            $query = "SELECT * FROM section";
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                $selected = (isset($_GET['Pr_section']) && $_GET['Pr_section'] == $row['Section_name']) ? 'selected' : '';
                                echo "<option value='" . $row['Section_name'] . "' $selected>" . $row['Section_name'] . "</option>";
                            }
                        ?>
                    </select>
                    
                    <label for="stock-status">حالة المخزون:</label>
                    <select id="stock-status" name="stock_status">
                        <option value="all" <?= (isset($_GET['stock_status']) && $_GET['stock_status'] == 'all') ? 'selected' : '' ?>>الكل</option>
                        <option value="in-stock" <?= (isset($_GET['stock_status']) && $_GET['stock_status'] == 'in-stock') ? 'selected' : '' ?>>متوفر</option>
                        <option value="low-stock" <?= (isset($_GET['stock_status']) && $_GET['stock_status'] == 'low-stock') ? 'selected' : '' ?>>كمية قليلة</option>
                        <option value="out-of-stock" <?= (isset($_GET['stock_status']) && $_GET['stock_status'] == 'out-of-stock') ? 'selected' : '' ?>>نفذ من المخزون</option>
                    </select>
                    
                    <input type="submit" value="تطبيق الفلتر" class="btn"> 
                </div>
                <div>
                <button class="btn" id="export-report">تصدير التقرير</button>
                <button class="btn" id="print-report">طباعة التقرير</button>
                <button class="btn btn-success" id="add-product"><a href="../admin/add_product.php" class="btn btn-success">إضافة منتج جديد</a>
                </button>
            </div>
            </div>
        </form>   
        
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-label">إجمالي المنتجات</div>
                <div class="stat-value"><?= $total_products ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">منتجات متوفرة</div>
                <div class="stat-value"><?= $in_stock ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">كمية قليلة</div>
                <div class="stat-value"><?= $low_stock ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">نفذ من المخزون</div>
                <div class="stat-value"><?= $out_of_stock ?></div>
            </div>
        </div>
        
        <table id="inventory-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصورة</th>
                    <th>اسم المنتج</th>
                    <th>القسم</th>
                    <th>السعر</th>
                    <th>الوحدة</th>
                    <th>الكمية المتاحة</th>
                    <th>حالة المخزون</th>
                    <th>مبيعات الشهر</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody id="inventory-data">
            <?php
            $section_name = isset($_GET['Pr_section']) ? $_GET['Pr_section'] : 'الكل';
            $stock_status = isset($_GET['stock_status']) ? $_GET['stock_status'] : 'all';

            $base_query = "SELECT 
                products.P_id,
                products.P_name,
                products.p_description,
                products.P_old_price,
                products.P_new_price,
                products.P_quantity,
                products.discount,
                product_unit.product_unit,
                products.p_weight,
                section.Section_name
            FROM 
                products,
                product_unit,
                section
            WHERE
                product_unit.p_unit_id = products.p_unit_id AND 
                section.Section_id = products.Section_id";

            // إضافة فلتر القسم
            if ($section_name != "الكل") {
                $base_query .= " AND section.Section_name = '$section_name'";
            }

            // إضافة فلتر حالة المخزون
            switch($stock_status) {
                case 'in-stock':
                    $base_query .= " AND products.P_quantity > 10";
                    break;
                case 'low-stock':
                    $base_query .= " AND products.P_quantity > 0 AND products.P_quantity <= 10";
                    break;
                case 'out-of-stock':
                    $base_query .= " AND products.P_quantity <= 0";
                    break;
            }

            $base_query .= " ORDER BY products.P_id DESC";

            $product_result = mysqli_query($conn, $base_query);
            $index = 0;
            while ($product_row = mysqli_fetch_assoc($product_result)) {
                $index++;
            ?>
            <tr>
                <td><?= $index ?></td>
                <?php 
                    // كود يقوم بجلب صور المنتج 
                    $product_images_query = "SELECT * FROM product_images WHERE P_id = " . $product_row['P_id'];
                    $product_image_result = mysqli_query($conn, $product_images_query);
                    if ($product_image_result && mysqli_num_rows($product_image_result) > 0) {
                        $product_images_row = mysqli_fetch_assoc($product_image_result); 
                ?>
                    <td><img class="product-img" src="<?= "../images/product_images/" . $product_images_row['P_img'] ?>" alt="صورة المنتج"></td>
                <?php } else { ?>
                    <td>لا توجد صورة</td>
                <?php } ?>

                <td><?= $product_row['P_name'] ?></td>
                <td><?= $product_row['Section_name'] ?></td>
                <td><?= $product_row['P_new_price'] . ' ريال' ?></td>
                <td><?= $product_row['product_unit'] ?></td>
                <td><?= $product_row['P_quantity'] ?></td>

                <?php
                $statusClass = '';
                $statusText = '';
                if ($product_row['P_quantity'] <= 0) {
                    $statusClass = 'out-of-stock';
                    $statusText = 'نفذ من المخزون';
                } elseif ($product_row['P_quantity'] < 10) {
                    $statusClass = 'low-stock';
                    $statusText = 'كمية قليلة';
                } else {
                    $statusClass = 'in-stock';
                    $statusText = 'متوفر';
                }
                ?>               
                <td><span class="stock-status <?= $statusClass ?>"><?= $statusText ?></span></td>
                <?php 
                    $query = "SELECT SUM(oi.quantity) as monthly_sales
                    FROM order_items oi
                    JOIN Orders o ON oi.or_id = o.Or_id
                    WHERE oi.P_id = {$product_row['P_id']} 
                    AND o.Or_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                    $sales_result = mysqli_query($conn, $query);
                    $sales = mysqli_fetch_assoc($sales_result);
                ?>
                <td><?= $sales['monthly_sales'] ? $sales['monthly_sales'] : 0 ?></td>
                <td>
                    <button class="btn edit-product"><a href="‏‏update_product.php?upatat_product_id=<?= $product_row['P_id'] ?>">تعديل</a></button>
                    <button class="btn btn-danger delete-product" onclick="confirm_delete(<?php echo $product_row['P_id']; ?>)">حذف</button>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <script>

            const printReportBtn = document.getElementById('print-report');
            const exportReportBtn = document.getElementById('export-report');

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
     
        //<!--==================(كود التأكيد من حذف القسم)==================-->
        function confirm_delete(product_id) {
            let delete_producte = confirm("هل تريد حذف هذا المنتج ؟");
            if (delete_producte == true) {
                //انشاء رابط يرجع الى الصفحة ونشاء متغير في ارابط ثم اسناد رقم المنتج الى المتغير الذي في الرابط
                window.location.href = "get_inventory_stats.php?delet_product_id=" + product_id;
            }
        }
    </script>
</body>
</html>