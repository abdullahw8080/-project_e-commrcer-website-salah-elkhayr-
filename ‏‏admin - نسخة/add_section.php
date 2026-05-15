
<?php
    require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
    require('../database/connect_to_database.php'); // الاتصال بقاعدة البيانات
?>

<?php
//<!--=========================(اضافة قسم)========================>>--> 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $section_name = strip_tags($_POST['section-name']);
    $section_image =($_FILES['section-image']['name']);
    $new_image_name = (rand(0,1000000)."_".$section_image);//قمنا بعمل دالة تقوم بضافة ارقام عشوائية مع اسم تاصورة حتى لا يحدث تكرار لصور
    $tmp_name = $_FILES['section-image']['tmp_name']; // نقل الصورة إلى مجلد مؤقت
    $add_section = $_POST['add-section'];

    if (isset($add_section)) {
        if(!empty($section_name) && !empty($section_image)){

            $allowed_tyeps = ['image/jpeg' , 'image/png' , 'image/PNG' , 'image/jpg'  ];//وضع انواع اصور الذي مسموح بها في مصفوفة
            $image_type = mime_content_type($tmp_name);//يقوم باخذ امتداد الصورة المدخلة
            if(in_array($image_type , $allowed_tyeps)){//يقوم بتاكد ان امتداد الصورة المدخلة موجود ضمن الصور الذي في المصفوفة
                $new_image_name = (rand(0,1000000)."_".$section_image);//قمنا بعمل دالة تقوم بضافة ارقام عشوائية مع اسم تاصورة حتى لا يحدث تكرار لصور
                move_uploaded_file($tmp_name,"../images/‏‏section_images/". $new_image_name); // نقل الصورة إلى المجلد المحدد
                try {//استثنا لتجنب تكرار القسم في قاعدة البيانات
                    $query = "INSERT INTO section (Section_name, section_image) VALUES ('$section_name', '$new_image_name')";
                    $result = mysqli_query($conn, $query);
                    if ($result) {
                        echo '<script>alert("تم إضافة القسم بنجاح"); window.location.href = "add_section.php"; // إعادة التوجيه بعد عرض الرسالة</script>';
                        exit(); // خروج
                    } else{
                        throw new mysqli_sql_exception(mysqli_error($conn)); // (catch)رمي استثناء في حالة فشل الاستعلام الى 
                    }  
                }//try_end
                catch (mysqli_sql_exception $e) {
                    // التعامل مع الخطأ Duplicate entry
                    if ($e->getCode() == 1062) { // 1062 هو رمز خطأ Duplicate Entry
                        echo '<script>alert("خطا!  تم اضافة القسم سابقا");</script>';
                    }else {
                    echo '<script>alert("حدث خطأ أثناء حفظ القسم يرجى التحقق من صحة الادخال");</script>';
                    }
                }
            }else{
                echo '<script>alert("هذة الصورة غير مسموح بها");</script>';   
            }

        }else{
            echo '<script>alert("يجب ملء جميع الحقول");</script>';   
        }
    }
   
}

?>
<!--=========================(حذف قسم)========================>>--> 
<?php

    if(isset($_GET['section_product_id'])){
        @$id = $_GET['section_product_id'];
        $query = "DELETE from section where Section_id = '$id'";
        $delet = mysqli_query($conn , $query);
        if($delet){
            echo '<script>alert("تم حذف القسم بنجاح");</script>';
        }
        else{
            echo '<script>alert("فشل عملية حذف القسم بنجاح");</script>';
        }
         // إعادة توجيه المستخدم إلى نفس الصفحة لتحديث الجدول
    echo '<script>window.location.href = "add_section.php";</script>';
    }
?>
<!--========================================================>>--> 

<!-- صفحة إضافة قسم -->
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
    <title>إضافة قسم</title>
<!--==================(كود التأكيد من حذف القسم)==================-->
    <script>
        function confirm_delete(section_id) {
            let delete_section = confirm("هل تريد حذف هذا القسم ؟");
            if (delete_section == true) {
                //انشاء رابط يرجع الى الصفحة ونشاء متغير في ارابط ثم اسناد رقم القسم الى المتغير الذي في الرابط
                window.location.href = "add_section.php?section_product_id=" + section_id;
            }
        }
    </script>
<!--==================================================================-->    
</head>
<body>
    <div class="content">
        <h1>إضافة قسم</h1>
        <form action="#" method="post" enctype="multipart/form-data">
            <label for="section-name">اسم القسم:</label>
            <input type="text" id="section-name" name="section-name" required>

            <label for="section-image"> صورة القسم:</label>
            <input type="file" id="section-image" name="section-image" required>

            <button type="submit" name = "add-section">إضافة قسم</button>
        </form>
        <h2>الأقسام</h2>
        <table border="1">
                <tr>
                    <th>رقم القسم</th>
                    <th>اسم القسم</th>
                    <th>صورة القسم</th>
                    <th>تعديل</th>
                    <th>حذف</th>
                </tr>
                <!-- البيانات هنا -->
                 <tr>
                     <!-------------(كود يقوم بجلب اسماء الاقسام وعرضها في الجدول)---------->
                     <?php
                         $query = "SELECT * from section";
                         $result = mysqli_query($conn,$query);
                         while($row = mysqli_fetch_assoc($result)){//يقوم في كل دورة باخذ صف وسنادة لمتغير (row)
                     ?>
                    <td><?php echo $row['Section_id']; ?></td><!--,وضع رقم في عمود الرقم في الجدول الذي في الواجهة-->
                    <td><?php echo $row['Section_name']; ?> </td><!--,وضع اسم القسم في عمود الاسم في الجدول الذي في الواجهة-->
                    <td><img src="<?php echo  "../images/‏‏section_images/".$row['section_image'];?>"></td><!--,وضع صورة القسم في عمود الرقم في الجدول الذي في الواجهة-->

                    <!-- ودمجة مع رابط الصفحة (se_id)زر لستدعا صفحة تعديل القسم مع اسناد رقم القسم في متغير -->
                    <td><button id="update"><a href="‏‏update_section.php?upatat_section_id=<?php echo $row['Section_id'];?>">تعديل</a></button></td>
                    <!-- ودمجة مع رابط الصفحة الرئيسية لضافة القسم (delet_section_id)زر   لحذف القسم مع اسناد رقم القسم في متغير -->
                    <td><button id="delete" onclick="confirm_delete(<?php echo $row['Section_id']; ?>)">حذف</button></td>
                 </tr>
                <!--تقوم ال (while)
                      بإنشاء كود html
                      في كل مرة ثم وضع القيم الستدعاة من قواعد البيانات داخل الكود-->
                 <?php
                }//اغلاق دوارة ال while
               ?> 
        </table>
    </div>
</body>
</html>

