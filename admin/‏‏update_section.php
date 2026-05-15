<?php
    require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
    require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقواعد البيانات
?>

<?php
    // جلب بيانات القسم بناءً على الرقم المعرّف
    $section_id = $_GET["upatat_section_id"];
    if (isset($_GET["upatat_section_id"])) {
        $query = "SELECT Section_name, section_image FROM section WHERE Section_id = $section_id";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result); // جلب البيانات
        }    
    }
      
?>  
<!--=========================(تعديل قسم)========================>>--> 
<?php
    @$update_section = $_POST['up-section'];//اسناد زر التعديل للمتغير
    if(isset($update_section)){//اتحقق اما ذا تم الضغط على زر التعديل
        $section_id = $_GET['upatat_section_id'];//الذي مدمجة في رابط الصفحة ووضعها في المتغير الجديد(upatat_section_id) اسناد قيمة المتغير 
        if(isset($section_id)){
            @$section_name = strip_tags($_POST['section-name']);
            //---------------------------->>
            $section_image = ($_FILES['section-image']['name']);
            $tmp_name = ($_FILES['section-image']['tmp_name']); // نقل الصورة إلى مجلد مؤقت
            
            //---------------------------->
                //================ كود تعديل المنتج مع الصورة==============================>>
                if (!empty($section_name)&& !empty($section_image)) {
                    $allowed_tyeps = ['image/jpeg' , 'image/png' , 'image/jpg' ];//وضع انواع اصور الذي مسموح بها في مصفوفة
                    $image_type = mime_content_type($tmp_name);//يقوم باخذ امتداد الصورة المدخلة
                    if(in_array($image_type , $allowed_tyeps)){//يقوم بتاكد ان امتداد الصورة المدخلة موجود ضمن الصور الذي في المصفوفة
                        $new_image_name = (rand(0,1000000)."_".$section_image);//قمنا بعمل دالة تقوم بضافة ارقام عشوائية مع اسم تاصورة حتى لا يحدث تكرار لصور
                        move_uploaded_file($tmp_name,"../images/‏‏section_images/". $new_image_name);
                        // نقل الصورة إلى المجلد المحدد
                    try{
                        $query =( "UPDATE section set Section_name = '$section_name' , section_image = '$new_image_name' WHERE Section_id = '$section_id'");
                        $result = mysqli_query($conn , $query);
                            if ($result) {
                            echo '<script> alert("تم تعديل القسم بنجاح"); window.location.href = "add_section.php"; // إعادة التوجيه بعد عرض الرسالة</script>';
                                exit(); // خروج
                            } else {
                                throw new mysqli_sql_exception(mysqli_error($conn)); // (catch)رمي استثناء في حالة فشل الاستعلام الى 
                            }
                    } catch (mysqli_sql_exception $e) {//مسك الخطاء اذا كان هناك تكرار في البيانات
                            // التعامل مع الخطأ Duplicate entry
                            if ($e->getCode() == 1062) { // 1062 هو رمز خطأ Duplicate Entry
                                echo '<script>alert("خطا!يوجد هناك قسم بهذا الاسم");</script>';
                            } else {
                                echo '<script>alert(" حدث خطأ أثناء حفظ البيانات يرجى التحقق من صحة البيانات المدخلة");</script>';
                            }
                        } 
                    }else{
                        echo '<script>alert("هذة الصورة غير مسموح بها");</script>';   
                    }    
                //====================================  كود تعديل المنتج بدون الصورة====================>>   
                    }elseif (!empty($section_name)) { 
                    try{
                    $query =( "UPDATE section set Section_name = '$section_name'  WHERE Section_id = '$section_id'");
                    $result = mysqli_query($conn , $query);
                        if ($result) {
                            echo '<script> alert("تم تعديل القسم بنجاح"); window.location.href = "add_section.php"; // إعادة التوجيه بعد عرض الرسالة</script>';
                            exit(); // خروج
                        } else {
                            throw new mysqli_sql_exception(mysqli_error($conn)); // (catch)رمي استثناء في حالة فشل الاستعلام الى 
                        }
                    }catch (mysqli_sql_exception $e) {//مسك الخطاء اذا كان هناك تكرار في البيانات
                        // التعامل مع الخطأ Duplicate entry
                        if ($e->getCode() == 1062) { // 1062 هو رمز خطأ Duplicate Entry
                            echo '<script>alert("خطا!يوجد هناك قسم بهذا الاسم");</script>';
                        } else {
                            echo '<script>alert(" حدث خطأ أثناء حفظ البيانات يرجى التحقق من صحة البيانات المدخلة");</script>';
                        }
                    } 
                //=======================================================================================>>    
            }else{
                    echo '<script>alert("يجب ملء الحقول المطلوبة");</script>';   
            }
        
        }
       
    }

      
   
    
?>
<!-- صفحة إضافة قسم -->
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type = "image/png"href="../images/home_image/logo4.png"><!--رابط ايقونت الموقع-->

    <link rel="stylesheet" href="style.css">

    <title>إضافة قسم</title>
    <style>
        /* نسخ نفس التنسيق للصفحة */
    </style>
</head>
<body>
    <div class="content">
        <h1>إضافة قسم</h1>
        <form action="#" method="post"  enctype="multipart/form-data">
            <label for="category-name">اسم القسم:</label>
            <input type="text" id="category-name" name="section-name"  value ="<?php echo $row['Section_name'];?>" required>

            <label for="section-image"> صورة القسم:</label>
            <input type="file" id="section-image" name="section-image" >

            <button type="submit" name = "up-section">تعديل القسم</button>
        </form>
    </div>
</body>
</html>

