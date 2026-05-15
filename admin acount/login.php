<?php
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقواعد البيانات

@$input_email = preg_replace('/\s+/', ' ', trim(strip_tags($_POST["email"]))); // تنظيف البريد الإلكتروني
@$input_password = preg_replace('/\s+/', ' ', trim(md5(strip_tags($_POST["password"])))); // تنظيف كلمة المرور
@$button_reg = $_POST["regster"]; // الزر
@$type_user = 1; // نوع المستخدم (1 = admin)

if (isset($button_reg)) { // عند الضغط على زر الدخول
    if (!empty($input_email) && !empty($input_password)) {
        $query = "SELECT * FROM users WHERE User_email = '$input_email' AND Password = '$input_password' AND Type_user_id = '$type_user'";
        $result = mysqli_query($conn, $query);

        if ($result) { // إذا تم تنفيذ الاستعلام بنجاح
            if (mysqli_num_rows($result) == 1) { // إذا وجدت نتيجة واحدة
                $row = mysqli_fetch_assoc($result);
                session_start();//إنشاء جلسة
                $_SESSION['admin_email'] = $input_email; // تخزين البريد في الجلسة

                echo '<script> 
                        alert("مرحبًا بك: ' . $row['User_name'] . '"); 
                        window.location.href = "../admin/index.php"; 
                      </script>';
                exit();
            } else {
                echo '<script>alert("البريد الإلكتروني أو كلمة السر غير صحيحة");</script>';
            }
        } else {
            echo '<script>alert("حدث خطأ أثناء تنفيذ الاستعلام");</script>';
        }
        mysqli_close($conn); // إغلاق الاتصال بقاعدة البيانات
    } else {
        echo '<script>alert("يجب ملء جميع الحقول");</script>';
    }
}
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

    <title>تسجيل الدخول</title>
</head>
<body>
    <div class="container">
        <h2>تسجيل دخول المشرفين</h2>
        <form method="post" name="Register" id="reg">
            <label for="email">البريد الإلكتروني:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">كلمة السر:</label>
            <div class="password_and_eyes_icon">
                <input type="password" id="password" name="password" required>
                <span class="toggle-password" onclick="show_password()"><i class='bx bxs-show'></i></span>
            </div>

            <button type="submit" name="regster">دخول</button>
        </form>
        <div class="links">
            <a href="forgot-password.php">نسيت كلمة السر؟</a>
        </div>
    </div>

    <script>
        document.getElementById("reg").onsubmit = function() {
            let email = document.getElementById("email").value;
            let emiR = /[\w.]+@(gmail|yahoo|hotmail)+.(com|org|info|net)$/is;
            let va = emiR.test(email);
            let password = document.getElementById("password").value;

            if (email === "") {
                alert("يجب عليك إدخال البريد الإلكتروني");
                return false;
            }
            if (!va) {
                alert("يجب عليك إدخال البريد الإلكتروني بشكل صحيح");
                return false;
            }
            if (password === "") {
                alert("يجب إدخال كلمة السر");
                return false;
            }
            return true;
        }
// دالة فتح العين لاضهار كلمة السر 
//وغلق العين لاخفا كلمة السر
        function show_password() {
            const password = document.querySelector('#password');
            const eyes = document.querySelector(".toggle-password");
            if (password.type === 'password') {
                password.type = 'text';
                eyes.innerHTML = "<i class='bx bxs-show'></i>";
            } else {
                password.type = 'password';
                eyes.innerHTML = "<i class='bx bxs-hide'></i>";
            }
        }
    </script>
</body>
</html>