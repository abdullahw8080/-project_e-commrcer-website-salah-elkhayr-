<?php
session_start(); // بدء الجلسة
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقواعد البيانات
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <link rel="icon" type = "image/png"href="../images/home_image/logo4.png"><!--رابط ايقونت الموقع-->

    <title>تغيير كلمة السر</title>
</head>
<body>
    <div class="container">
        <h2>تغيير كلمة السر</h2>
        <form method="post" name="update" id="update">
            <label for="email">البريد الإلكتروني:</label>
            <input type="email" id="email" name="email">
            
            <label for="phone_number">رقم الهاتف:</label>
            <input type="text" id="phone_number" name="phone_number" minlength="9" maxlength="14" pattern="[0-9+]{9,14}" title="يرجى إدخال رقم هاتف صحيح (من 9 إلى 14 رقمًا)">

            <label for="new-password">كلمة السر الجديدة:</label>
            <div class="password_and_eyes_icon">
                <input type="password" id="new-password" name="new-password" minlength="7" title="يرجى إدخال كلمة مرور قوية بحيث لا تقل عن 7 حروف او ارقام او رموز)">
                <span class="toggle-password" onclick="show_password()"><i class='bx bxs-show'></i></span>
            </div>
            
            <label for="confirm-password">تأكيد كلمة السر الجديدة:</label>
            <input type="password" id="confirm-password" name="confirm-password">
            
            <button type="submit" name="change_password">تغيير كلمة السر</button>
        </form>
        <div class="links">
            <a href="login.php">العودة إلى تسجيل الدخول</a>
        </div>
    </div>

    <?php
    if (isset($_POST['change_password'])) {
        // استقبال البيانات من النموذج
         //preg_replace('/\s+/', ' ') يحذف المسافات الاكبر من واحد من الوسط
        //trim()يحذف المسافة من البداية ونهاية
        //strip_tags()يقوم بحذف الاكواد
        //md5()يقوم بتشفير كلمة السر
        $input_email = preg_replace('/\s+/', ' ',trim(strip_tags($_POST["email"])));
        $input_phone_number = preg_replace('/\s+/', ' ',trim(strip_tags($_POST["phone_number"])));
        $input_new_password = preg_replace('/\s+/', ' ',trim(md5(strip_tags($_POST["new-password"])))); 
        $input_confirm_password = preg_replace('/\s+/', ' ',trim(md5(strip_tags($_POST["confirm-password"])))); // استخدام md5
        @$type_user = 1;/*(1)جعلنا نوع المستخدم 
        حتى يتوافق مع عمود نوع المستخدم حيث ان رقم واحد يعني ان نوع المستخدم ادمن وليس عميل*/
        if(!empty($input_email) && !empty($input_phone_number) && !empty($input_confirm_password)  && !empty($input_new_password)){
            // التحقق من وجود المستخدم باستخدام البريد الإلكتروني ورقم الهاتف
            $check_query = "SELECT * FROM users WHERE User_email = '$input_email' AND User_phone = '$input_phone_number' AND Type_user_id = '$type_user'";
            $check_result = mysqli_query($conn, $check_query);

            if (mysqli_num_rows($check_result) > 0) {
                // تحديث كلمة المرور
                $query = "UPDATE users SET Password = '$input_confirm_password' 
                        WHERE User_email = '$input_email' AND User_phone = '$input_phone_number' AND Type_user_id = '$type_user'";
                $result = mysqli_query($conn, $query);

                if ($result) {
                    $_SESSION['email'] = $input_email; // تخزين البريد الإلكتروني في الجلسة
                    echo '<script> alert("تم تعديل كلمة السر"); window.location.href = "login.php";</script>';
                    // إعادة التوجيه إلى الصفحة تسجيل الدخول
                    exit();//خروج
                } else {
                    echo '<script>alert("حدث خطأ أثناء تحديث كلمة السر");</script>';
                }
            } else {
                echo '<script>alert("البريد الإلكتروني أو رقم الهاتف غير صحيح");</script>';
            }
            // إغلاق الاتصال بقاعدة البيانات
            mysqli_close($conn);
    }else{
        echo '<script>alert("يجب ملء جميع الحقول");</script>';
    }
}
    ?>

    <script>
    // دالة التحقق من صحة النموذج
    document.getElementById("update").onsubmit = function() {
        let email = document.getElementById("email").value;
        let emiR = /[\w.]+@(gmail|yahoo|hotmail)+.(com|org|info|net)$/is;
        let va = emiR.test(email);

        let pass = document.getElementById("new-password").value;
        let confirm_password = document.getElementById("confirm-password").value;
        let phone_number = document.getElementById("phone_number").value;

        if (email === "") {
            alert("يجب عليك إدخال البريد الإلكتروني");
            return false;
        }

        if (va === false) {
            alert("يجب عليك إدخال البريد الإلكتروني بشكل صحيح");
            return false;
        }

        if (phone_number === "") {
            alert("يجب ملء حقل رقم الهاتف");
            return false;
        }

        if (pass === "" || pass.length < 6) {
            alert("يجب ملء حقل كلمة السر بكلمة سر بحيث تحتوي على احرف ورموز وارقام");
            return false;
        }

        if (confirm_password === "" || confirm_password !== pass) {
            alert("يجب أن يكون تأكيد كلمة السر مطابقًا لكلمة السر الجديدة");
            return false;
        }

        return true;
    };

    // دالة تبديل رؤية كلمة السر
    function show_password() {
        const password = document.querySelector('#new-password');
        const eyes = document.querySelector(".toggle-password i");

        if (password.type === 'password') {
            password.type = 'text';
            eyes.classList.remove('bxs-show');
            eyes.classList.add('bxs-hide');
        } else {
            password.type = 'password';
            eyes.classList.remove('bxs-hide');
            eyes.classList.add('bxs-show');
        }
    }
    </script>
</body>
</html>