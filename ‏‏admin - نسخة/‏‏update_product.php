<?php
    require("../admin acount/admin_session.php.php"); // التحقق من صحة الجلسة
    require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقواعد البيانات
?>

<?php
// جلب بيانات المنتج بناءً على الرقم المعرّف
$product_id = $_GET["upatat_product_id"];
if (isset($_GET["upatat_product_id"])) {
    $query = "SELECT 
        products.P_name,
        products.P_img,
        products.P_old_price,
        products.P_new_price,
        products.discount,
        products.p_description,
        products.p_weight,
        product_unit.product_unit,
        section.Section_name
    FROM 
        products,
        product_unit,
        section
    WHERE
        products.P_id = $product_id AND 
        product_unit.p_unit_id = products.p_unit_id AND 
        section.Section_id = products.Section_id";

    $result = mysqli_query($conn, $query);
    if ($result) {
        $row_1 = mysqli_fetch_assoc($result); // جلب البيانات
    }
}

//====================================== كود تعديل المنتج ==============================================>>
if (isset($_POST['update-product-button'])) { // إذا تم الضغط على زر التعديل
    @$id = $_GET['upatat_product_id']; // اسناد قيمة المتغير (upatat_product_id) إلى المتغير ($id)
    if (isset($id)) { // التحقق مما إذا كان هناك قيمة
        $product_name = trim(strip_tags($_POST['product-name'])); // اسناد القيم من الحقول إلى المتغيرات
        $product_descrption = strip_tags($_POST['p_descrption']);
        $old_price = trim(strip_tags($_POST['old-price']));
        $discount = trim(strip_tags($_POST['discount']));
        $new_price = $old_price - ($old_price * $discount / 100);
        $unit = $_POST['unit'];
        $product_weight = ($_POST['p_weight']);
        $section = $_POST['Pr_section'];

        $product_image = ($_FILES['product-image']['name']); // اسناد اسم الصورة 
        $tmp_name = ($_FILES['product-image']['tmp_name']); // نقل الصورة إلى مجلد مؤقت
       

        //==========================================  كود تعديل المنتج مع الصورة ======================================>>
        if (!empty($product_name) && !empty($product_descrption) && !empty($old_price) && $discount>=0 && !empty($unit) && !empty($product_weight) && !empty($section) && !empty($product_image)) {

            $allowed_tyeps = ['image/jpeg' , 'image/png' , 'image/PNG' , 'image/jpg'  ];//وضع انواع اصور الذي مسموح بها في مصفوفة
            $image_type = mime_content_type($tmp_name);//يقوم باخذ امتداد الصورة المدخلة    
            if(in_array($image_type , $allowed_tyeps)){//يقوم بتاكد ان امتداد الصورة المدخلة موجود ضمن الصور الذي في المصفوفة
                $new_image_name = (rand(0,1000000)."_".$product_image);//قمنا بعمل دالة تقوم بضافة ارقام عشوائية مع اسم تاصورة حتى لا يحدث تكرار لصور

                move_uploaded_file($tmp_name ,"../images/product_images/".$new_image_name); // نقل الصورة إلى المجلد المحدد
                try {//مسك الخطاء اذا كان هناك تكرار في البيانات    
                    $query = "UPDATE products SET 
                        P_name = '$product_name',
                        p_description = '$product_descrption',
                        P_img = '$new_image_name',
                        P_old_price = '$old_price',
                        P_new_price = '$new_price',
                        discount = '$discount',
                        p_unit_id = '$unit',
                        p_weight = '$product_weight',
                        Section_id = '$section'
                    WHERE 
                        products.P_id = '$id'";

                    $result = mysqli_query($conn, $query);
                    if ($result) {
                        echo '<script> alert("تم تعديل المنتج بنجاح"); window.location.href = "products.php"; // إعادة التوجيه بعد عرض الرسالة</script>';
                        exit(); // خروج
                    } else {
                        throw new mysqli_sql_exception(mysqli_error($conn)); // (catch)رمي استثناء في حالة فشل الاستعلام الى 
                    }
                }catch (mysqli_sql_exception $e) {//مسك الخطاء اذا كان هناك تكرار في البيانات
                        // التعامل مع الخطأ Duplicate entry
                        if ($e->getCode() == 1062) { // 1062 هو رمز خطأ Duplicate Entry
                            echo '<script>alert(" المنتج موجود مسبقًا");</script>';
                        } else {
                            echo '<script>alert(" حدث خطأ أثناء حفظ البيانات يرجى التحقق من صحة البيانات المدخلة");</script>';
                    }
                }
            }else{
                echo '<script>alert("هذة الصورة غير مسموح بها");</script>';   
            }
        // ======================================== كود تعديل المنتج بدون الصورة==========================>>
        }elseif (!empty($product_name) && !empty($old_price) && $discount>=0 && !empty($unit) && !empty($section)){
            try {//مسك الخطاء اذا كان هناك تكرار في البيانات    
                $query = "UPDATE products SET 
                    P_name = '$product_name',
                    p_description = '$product_descrption',
                    P_old_price = '$old_price',
                    P_new_price = '$new_price',
                    discount = '$discount',
                    p_unit_id = '$unit',
                    p_weight = '$product_weight',
                    Section_id = '$section'
                WHERE 
                    products.P_id = '$id'";

                $result = mysqli_query($conn, $query);
                if ($result) {
                    echo '<script> alert("تم تعديل المنتج بنجاح"); window.location.href = "products.php"; // إعادة التوجيه بعد عرض الرسالة</script>';
                    exit(); // خروج
                } else {
                    throw new mysqli_sql_exception(mysqli_error($conn)); // (catch)رمي استثناء في حالة فشل الاستعلام الى 
                }
            } 
            catch (mysqli_sql_exception $e) {//مسك الخطاء اذا كان هناك تكرار في البيانات
                // التعامل مع الخطأ Duplicate entry
                if ($e->getCode() == 1062) { // 1062 هو رمز خطأ Duplicate Entry
                    echo '<script>alert(" المنتج موجود مسبقًا");</script>';
                } else {
                    echo '<script>alert(" حدث خطأ أثناء حفظ البيانات يرجى التحقق من صحة البيانات المدخلة");</script>';
                }
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
    <link rel="stylesheet" href="style.css">

     <!---ارابط استدعاء الخط -->
     <link rel="preconnect" href="https://fonts.googleapis.com">
     <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
     <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
     <link rel="icon" type = "image/png"href="../images/home_image/logo4.png"><!--رابط ايقونت الموقع-->


    <title>تعديل منتج</title>
    <style>
        /* نسخ نفس التنسيق للصفحة */
    </style>
</head>
<body>
    <div class="content">
        <h1>تعديل منتج</h1>
        <form action="#" method="post" enctype="multipart/form-data">
            <label for="product-name">اسم المنتج:</label>
            <input type="text" id="product-name" name="product-name" value ="<?php echo $row_1['P_name'];?>" required>

            <label for="product-image">صورة المنتج:</label>
            <input type="file" id="product-image" name="product-image" >

            <label for="p_descrption"> وصف المنتج</label>
            <input type="text" id="p_descrption" name="p_descrption" value ="<?php echo $row_1['p_description'];?>" required>

            <label for="old-price">السعر القديم:</label>
            <input type="number" id="old-price" name="old-price" value ="<?php echo $row_1['P_old_price'];?>" required>

            <label for="discount">نسبة التخفيض:</label>
            <input type="number" id="discount" name="discount" value ="<?php echo $row_1['discount'];?>" required>

            <label for="unit">الوحدة:</label>
            <select id="unit" name="unit" value ="<?php echo $row_1['product_unit'];?>">
            <?php
                    $query = ("SELECT * from product_unit");
                    $result = mysqli_query($conn , $query);
                    while ($row_2 = mysqli_fetch_assoc($result)){
                       echo "<option value='" . $row_2['p_unit_id'] . "' >" . $row_2['product_unit'] . "</option>";
                   }
                ?>
            </select>

            <label for="p_weight"> وزن المنتج</label>
            <input type="text" id="p_weight" name="p_weight" value ="<?php echo $row_1['p_weight'];?>" required>

            <label for="section">القسم:</label>
            <select id="section" name="Pr_section" value ="<?php echo $row_1['Section_name'];?>">
                <?php
                    $query = ("SELECT * from section ");
                    $result = mysqli_query($conn , $query);
                    while ($row_3 = mysqli_fetch_assoc($result)){
                       echo "<option value='" . $row_3['Section_id'] . "' >" . $row_3['Section_name'] . "</option>";

                   }
                   mysqli_close($conn); // إغلاق الاتصال بقاعدة البيانات
                ?>
            </select>
            <button type="submit" name = "update-product-button" >تعديل المنتج</button>
        </form>
    </div>
</body>
</html>
