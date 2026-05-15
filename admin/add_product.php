<?php
require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقواعد البيانات

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = strip_tags($_POST['product-name']); // اسناد اسم المنتج إلى متغير
    $product_descrption = strip_tags($_POST['p_descrption']);
    $old_price = strip_tags($_POST['old-price']);
    $discount = strip_tags($_POST['discount']);
    $new_price = ($old_price - ($old_price * $discount / 100)); // حساب السعر الجديد
    $product_quantity = strip_tags($_POST['P_quantity']);
    $unit = ($_POST['unit']);
    $product_weight = ($_POST['p_weight']);
    $section = ($_POST['product_section']); // اسناد قسم المنتج إلى المتغير

    $add_product = $_POST['add-product']; // اسناد الزر إلى المتغير

    if (isset($add_product)) {
        try {
            // إضافة المنتج إلى جدول products
            $product_query = "INSERT INTO products (P_name, P_old_price, P_new_price, P_quantity, Discount, p_unit_id, p_description, p_weight, Section_id)
                              VALUES ('$product_name', '$old_price', '$new_price','$product_quantity', '$discount', '$unit', '$product_descrption', '$product_weight', '$section')";
            $product_result = mysqli_query($conn, $product_query);

            if ($product_result) {
                // الحصول على الـ P_id للمنتج المضاف حديثًا
                $query = "SELECT P_id FROM products WHERE P_name = '$product_name' AND P_old_price = '$old_price' AND p_description = '$product_descrption'";
                $result = mysqli_query($conn, $query);
                $product_id_row = mysqli_fetch_assoc($result);
                $product_id = $product_id_row['P_id']; // استخراج قيمة P_id

                // إضافة الصور
                $product_image_1 = ($_FILES['product-image_1']['name']); // اسم الصورة الأولى
                $tmp_name_1 = ($_FILES['product-image_1']['tmp_name']); // الملف المؤقت للصورة الأولى

                $product_image_2 = ($_FILES['product-image_2']['name']); // اسم الصورة الثانية
                $tmp_name_2 = ($_FILES['product-image_2']['tmp_name']); // الملف المؤقت للصورة الثانية

                $product_image_3 = ($_FILES['product-image_3']['name']); // اسم الصورة الثالثة
                $tmp_name_3 = ($_FILES['product-image_3']['tmp_name']); // الملف المؤقت للصورة الثالثة

                $allowed_types = ['image/jpeg', 'image/png', 'image/PNG', 'image/jpg']; // أنواع الصور المسموح بها

                if (!empty($product_image_1) && !empty($product_image_2) && !empty($product_image_3)) {
                    $image_type_1 = mime_content_type($tmp_name_1); // نوع الصورة الأولى
                    $image_type_2 = mime_content_type($tmp_name_2); // نوع الصورة الثانية
                    $image_type_3 = mime_content_type($tmp_name_3); // نوع الصورة الثالثة

                    if (in_array($image_type_1, $allowed_types) && in_array($image_type_2, $allowed_types) && in_array($image_type_3, $allowed_types)) {
                        $new_image_name_1 = rand(0, 1000000) . "_" . $product_image_1; // إنشاء اسم جديد للصورة الأولى
                        move_uploaded_file($tmp_name_1, "../images/product_images/" . $new_image_name_1); // نقل الصورة الأولى

                        $new_image_name_2 = rand(0, 1000000) . "_" . $product_image_2; // إنشاء اسم جديد للصورة الثانية
                        move_uploaded_file($tmp_name_2, "../images/product_images/" . $new_image_name_2); // نقل الصورة الثانية

                        $new_image_name_3 = rand(0, 1000000) . "_" . $product_image_3; // إنشاء اسم جديد للصورة الثالثة
                        move_uploaded_file($tmp_name_3, "../images/product_images/" . $new_image_name_3); // نقل الصورة الثالثة

                        // إضافة الصور إلى جدول product_images
                        $images_query_1 = "INSERT INTO product_images (P_id, P_img) VALUES ('$product_id', '$new_image_name_1')";
                        $images_query_2 = "INSERT INTO product_images (P_id, P_img) VALUES ('$product_id', '$new_image_name_2')";
                        $images_query_3 = "INSERT INTO product_images (P_id, P_img) VALUES ('$product_id', '$new_image_name_3')";

                        mysqli_query($conn, $images_query_1);
                        mysqli_query($conn, $images_query_2);
                        mysqli_query($conn, $images_query_3);
                    } else {
                        echo '<script>alert("أمتداد أحد الصور غير مسموح به");</script>';
                    }
                }elseif(!empty($product_image_1) && !empty($product_image_2) && empty($product_image_3)){
                    $image_type_1 = mime_content_type($tmp_name_1); // نوع الصورة الأولى
                    $image_type_2 = mime_content_type($tmp_name_2); // نوع الصورة الثانية
                    if (in_array($image_type_1, $allowed_types) && in_array($image_type_2, $allowed_types)) {
                        $new_image_name_1 = rand(0, 1000000) . "_" . $product_image_1; // إنشاء اسم جديد للصورة الأولى
                        move_uploaded_file($tmp_name_1, "../images/product_images/" . $new_image_name_1); // نقل الصورة الأولى

                        $new_image_name_2 = rand(0, 1000000) . "_" . $product_image_2; // إنشاء اسم جديد للصورة الثانية
                        move_uploaded_file($tmp_name_2, "../images/product_images/" . $new_image_name_2); // نقل الصورة الثانية
                        
                        $images_query_1 = "INSERT INTO product_images (P_id, P_img) VALUES ('$product_id', '$new_image_name_1')";
                        $images_query_2 = "INSERT INTO product_images (P_id, P_img) VALUES ('$product_id', '$new_image_name_2')";

                        mysqli_query($conn, $images_query_1);
                        mysqli_query($conn, $images_query_2);
                    } else {
                        echo '<script>alert("أمتداد أحد الصور غير مسموح به");</script>';
                    }
                }elseif(!empty($product_image_1) && empty($product_image_2) && !empty($product_image_3)){
                    $image_type_1 = mime_content_type($tmp_name_1); // نوع الصورة الأولى
                    $image_type_3 = mime_content_type($tmp_name_3); // نوع الصورة الثانية
                    if (in_array($image_type_1, $allowed_types) && in_array($image_type_3, $allowed_types)) {
                        $new_image_name_1 = rand(0, 1000000) . "_" . $product_image_1; // إنشاء اسم جديد للصورة الأولى
                        move_uploaded_file($tmp_name_1, "../images/product_images/" . $new_image_name_1); // نقل الصورة الأولى

                        $new_image_name_3 = rand(0, 1000000) . "_" . $product_image_3; // إنشاء اسم جديد للصورة الثانية
                        move_uploaded_file($tmp_name_3, "../images/product_images/" . $new_image_name_3); // نقل الصورة الثانية
                        
                        $images_query_1 = "INSERT INTO product_images (P_id, P_img) VALUES ('$product_id', '$new_image_name_1')";
                        $images_query_3 = "INSERT INTO product_images (P_id, P_img) VALUES ('$product_id', '$new_image_name_3')";

                        mysqli_query($conn, $images_query_1);
                        mysqli_query($conn, $images_query_3);
                    } else {
                        echo '<script>alert("أمتداد أحد الصور غير مسموح به");</script>';
                    }
                }elseif(!empty($product_image_1) && empty($product_image_2) && empty($product_image_3)){
                    $image_type_1 = mime_content_type($tmp_name_1); // نوع الصورة الأولى
                    if (in_array($image_type_1, $allowed_types)) {
                        $new_image_name_1 = rand(0, 1000000) . "_" . $product_image_1; // إنشاء اسم جديد للصورة الأولى
                        move_uploaded_file($tmp_name_1, "../images/product_images/" . $new_image_name_1); // نقل الصورة الأولى

                        $images_query_1 = "INSERT INTO product_images (P_id, P_img) VALUES ('$product_id', '$new_image_name_1')";
                        mysqli_query($conn, $images_query_1);
                    } else {
                        echo '<script>alert("أمتداد أحد الصور غير مسموح به");</script>';
                    }
                }
                echo '<script>alert("تم إضافة المنتج ");</script>';
            } else {
                throw new mysqli_sql_exception(mysqli_error($conn)); // رمي استثناء في حالة فشل الاستعلام
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // 1062 هو رمز خطأ Duplicate Entry
                echo '<script>alert("خطأ! تم إضافة المنتج سابقًا");</script>';
            } else {
                echo '<script>alert("حدث خطأ أثناء حفظ المنتج، يرجى التحقق من صحة الإدخال");</script>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Style.css">

     <!---ارابط استدعاء الخط -->
     <link rel="preconnect" href="https://fonts.googleapis.com">
     <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
     <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
     <link rel="icon" type = "image/png"href="../images/home_image/logo4.png"><!--رابط ايقونت الموقع-->
    <style>
     
    </style>

    <title>إضافة منتج</title>
    <style>
        /* نسخ نفس التنسيق للصفحة */
    </style>
</head>
<body>
    <div class="content">
        <h1>إضافة منتج</h1>
        <form action="#" method="post" enctype="multipart/form-data">
            <label for="product-name">اسم المنتج:</label>
            <input type="text" id="product-name" name="product-name" required>

        <div class="images_input">
            <div>
                <label for="product-image">صورة المنتج * :</label>
                <input type="file" id="product-image" name="product-image_1" required>
            </div>
            <div>
                <label for="product-image">صورة المنتج:</label>
                <input type="file" id="product-image" name="product-image_2" >
            </div>
            <div>
                <label for="product-image">صورة المنتج:</label>
                <input type="file" id="product-image" name="product-image_3" >
            </div>
        </div>
           
            <label for="p_descrption"> وصف المنتج</label>
            <input type="text" id="p_descrption" name="p_descrption" >

            <label for="old-price">السعر القديم:</label>
            <input type="number" id="old-price" name="old-price" >

            <label for="discount">نسبة التخفيض:</label>
            <input type="number" id="discount" name="discount" value = 0>
            
            <label for="p_quantity"> كمية المنتج في المخزن:</label>
            <input type="number" id="P_quantity" name="P_quantity" >


            <label for="unit">الوحدة:</label>
            <select id="unit" name="unit">
            
           
            <?php
                    $query = ("SELECT * from product_unit");
                    $result = mysqli_query($conn , $query);
                    while ($row = mysqli_fetch_assoc($result)){
                       echo "<option value='" . $row['p_unit_id'] . "'>" . $row['product_unit'] . "</option>";
                   }
                ?>
            </select>

            <label for="p_weight"> وزن المنتج</label>
            <input type="text" id="p_weight" name="p_weight" >

            <label for="section">القسم:</label>
            <select id="section" name="product_section">
                <?php
                    $query = ("SELECT * from section");
                    $result = mysqli_query($conn , $query);
                    while ($row = mysqli_fetch_assoc($result)){
                       echo "<option value='" . $row['Section_id'] . "'>" . $row['Section_name'] . "</option>";
                   }
                   mysqli_close($conn); // إغلاق الاتصال بقاعدة البيانات
                ?>
            </select>

            <button type="submit" name = "add-product">إضافة منتج</button>
        </form>
    </div>
</body>
</html>
