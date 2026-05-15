<?php
require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقواعد البيانات

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
    echo '<script>window.location.href = "products.php";</script>';
}
?>
<!--========================================================>>-->
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../images/home_image/logo4.png">
    <title>المنتجات</title>
<!--==================(كود التأكيد من حذف القسم)==================-->
    <script>
        function confirm_delete(product_id) {
            let delete_producte = confirm("هل تريد حذف هذا المنتج ؟");
            if (delete_producte == true) {
                //انشاء رابط يرجع الى الصفحة ونشاء متغير في ارابط ثم اسناد رقم المنتج الى المتغير الذي في الرابط
                window.location.href = "products.php?delet_product_id=" + product_id;
            }
        }
    </script>
<!--===============================================================-->   
</head>
<body>
    <div class="content" style="flex: 1;">
        <h1>المنتجات</h1>
        <form method="GET" action="">
            <label for="section">القسم:</label>
            <select id="section" name="Pr_section">
                <option value="الكل">الكل</option>
                <?php
                $query = "SELECT * FROM section";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='" . $row['Section_name'] . "'>" . $row['Section_name'] . "</option>";
                }
                ?>
            </select>
            <input type="submit" value="بحث">
            <button type="submit" name="add-product"><a href="add_product.php">إضافة منتج</a></button>
        </form>

        <table border="1">
            <tr>
                <th>رقم المنتج</th>
                <th>اسم المنتج</th>
                <th>وصف المنتج</th>
                <th>صورة المنتج</th>
                <th>السعر القديم</th>
                <th>السعر الجديد</th>
                <th>نسبة التخفيض</th>
                <th>كمية المنتج في المخزن</th>
                <th>الوحدة</th>
                <th>الوزن</th>
                <th>القسم</th>
                <th>تعديل</th>
                <th>حذف</th>
            </tr>
            <?php
            if (isset($_GET['Pr_section'])) {
                $section_name = $_GET['Pr_section'];
            } else {
                $section_name = 'الكل';
            }

            if ($section_name == "الكل") {
                $product_query = "SELECT 
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
                    section.Section_id = products.Section_id
                ORDER BY products.P_id DESC";
            } else {
                $product_query = "SELECT 
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
                    section.Section_id = products.Section_id AND
                    section.Section_name = '$section_name'
                ORDER BY products.P_id ASC";
            }

            $product_reuslt = mysqli_query($conn, $product_query);
            while ($product_row = mysqli_fetch_assoc($product_reuslt)) {

            ?>
                <tr>
                    <td><?php echo $product_row['P_id']; ?></td>
                    <td><?php echo $product_row['P_name']; ?></td>
                    <td><?php echo $product_row['p_description']; ?></td>

                    <?php 
                        //كود يقوم بجلب صور المنتج 
                        $product_images_query = "SELECT * FROM product_images WHERE P_id = " . $product_row['P_id'];
                        $product_image_reuslt = mysqli_query($conn, $product_images_query);
                        if ($product_image_reuslt) {
                            $product_images_row = mysqli_fetch_assoc($product_image_reuslt); 
                    ?>
                        <td><img src="<?php echo "../images/product_images/" . $product_images_row['P_img']; ?>" alt=""></td>
                    <?php }?>
                    <td><?php echo $product_row['P_old_price'] . ' ريال'; ?></td>
                    <td><?php echo $product_row['P_new_price'] . ' ريال'; ?></td>
                    <td><?php echo $product_row['discount'] . ' %'; ?></td>
                    <td><?php echo $product_row['P_quantity']; ?></td>
                    <td><?php echo $product_row['product_unit']; ?></td>
                    <td><?php echo $product_row['p_weight']; ?></td>
                    <td><?php echo $product_row['Section_name']; ?></td>
                    <td><button id="update"><a href="‏‏update_product.php?upatat_product_id=<?php echo $product_row['P_id']; ?>">تعديل</a></button></td>
                    <td><button id="delete" onclick="confirm_delete(<?php echo $product_row['P_id']; ?>)">حذف</button></td>
                </tr>
            <?php
            }
            ?>
        </table>
    </div>
</body>
</html>