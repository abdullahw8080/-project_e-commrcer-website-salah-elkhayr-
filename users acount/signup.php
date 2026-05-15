<?php
    session_start();// بدء الجلسة
    require('../database/connect_to_database.php');//استدعاء ملف الاتصال بقواعد البيانات 
        //يتم وضع نقطتين في بداية المساراذا كان الملف في مجلد اخر فقط
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>إنشاء حساب</h2>
        <form method="post" name="Register" id="reg">
            <label for="username">الاسم:</label>
            <input type="text" id="username" name="username" placeholder="الاسم" maxlength="35" minlength="10"  title="يرجى ادخال الاسم بحيث لا يزيد عن 30 حرف">
            <label for="email">البريد الإلكتروني:</label>
            <input type="email" id="email" name="email">
            <label for="phone-number">رقم الهاتف:</label>
            <input type="text" id="phone-number" name="phone-number" placeholder="رقم الهاتف" minlength="9" maxlength="14" pattern="[0-9+]{9,14}" title="يرجى إدخال رقم هاتف صحيح (من 9 إلى 14 رقمًا)">
            <label for="password">كلمة السر:</label>   
            <div class="password_and_eyes_icon">
                <input type="password" id="password" name="new-password" minlength="7" title="يرجى إدخال كلمة مرور قوية بحيث لا تقل عن 7 حروف او ارقام او رموز)">
                <span class="toggle-password" onclick="show_password()"><i class='bx bxs-show'></i></span>
            </div> 
            <label for="confirm-password">تأكيد كلمة السر:</label>
            <input type="password" id="confirm-password" name="confirm-password" minlength="7" >
            <button type="submit" name="add">إنشاء حساب</button>
        </form>
        <div class="links">
            <a href="login.php">لديك حساب؟ تسجيل الدخول</a>
        </div>
    </div>

    <?php
      //preg_replace('/\s+/', ' ') يحذف المسافات الاكبر من واحد من الوسط
        //trim()يحذف المسافة من البداية ونهاية
        //strip_tags()يقوم بحذف الاكواد
        //md5()يقوم بتشفير كلمة السر
@$input_username = preg_replace('/\s+/', ' ',trim(strip_tags($_POST["username"]))); // تنظيف وحفظ اسم المستخدم
@$input_email = preg_replace('/\s+/', ' ',trim(strip_tags($_POST["email"]))); // تنظيف وحفظ البريد الإلكتروني
@$input_phone_number = preg_replace('/\s+/', ' ',trim(strip_tags($_POST["phone-number"]))); // تنظيف وحفظ رقم الهاتف
@$input_password = preg_replace('/\s+/', ' ',trim(md5(strip_tags($_POST["confirm-password"])))); 
@$input_confirm_password = preg_replace('/\s+/', ' ',trim(md5(strip_tags($_POST["confirm-password"])))); // تنظيف وحفظ كلمة المرور (مشفرة بـ MD5)
@$type_user = 2; // نوع المستخدم (2 = عميل )

@$button_add = $_POST["add"]; // زر الإضافة

// شرط عند الضغط على الزر
if (isset($button_add)) {
    if(!empty($input_username) && !empty($input_email) && !empty($input_phone_number) && !empty($input_confirm_password)  && !empty($input_password)){
        try {//مسك الخطاء اذا كان هناك تكرار في البيانات
        // تنفيذ استعلام الإدخال
        $query = "INSERT INTO users (User_name, User_email, User_phone, Type_user_id, Password) 
                  VALUES ('$input_username', '$input_email', '$input_phone_number', '$type_user', '$input_confirm_password')";
        $result = mysqli_query($conn, $query);

        if ($result) {
            $_SESSION['user_email_session'] = $input_email; // تخزين البريد الإلكتروني في الجلسة
            echo '<script>alert("تم إنشاء حساب  بنجاح"); window.location.href = "../index.php";</script>';
            exit(); // خروج
        } else {
            throw new mysqli_sql_exception(mysqli_error($conn)); // (catch)رمي استثناء في حالة فشل الاستعلام الى 
        }
    } catch (mysqli_sql_exception $e) {//مسك الخطاء اذا كان هناك تكرار في البيانات
        // التعامل مع الخطأ Duplicate entry
        if ($e->getCode() == 1062) { // 1062 هو رمز خطأ Duplicate Entry
            echo '<script>alert("البريد الإلكتروني موجود مسبقًا");</script>';
        } else {
            echo '<script>alert("حدث خطأ أثناء حفظ البيانات");</script>';
        }
    }

    mysqli_close($conn); // إغلاق الاتصال بقاعدة البيانات
}else{
    echo '<script>alert("يجب ملء جميع الحقول");</script>';
}

}
?>


<script >
      
//========================={شروط صفحة إنشاء حساب}=============================>>

document.getElementById("reg").onsubmit = function() {
    let email = document.getElementById("email").value;
    let emiR = /^[a-zA-Z0-9._%+-]+@(gmail|yahoo|hotmail)\.(com|org|info|net)$/;
    let va = emiR.test(email);

    let password = document.getElementById("password").value;
   
    let confirm_password = document.getElementById("confirm-password").value;
    let phone_number = document.getElementById("phone-number").value;

    let username = document.querySelector("#username").value.trim().replace(/\s+/g, ' ');
        if (/^([A-Za-zء-ي-]+\s?){1,5}$/.test(username) === false) {
        alert("يجب عليك إدخال ما بين كلمة واحدة وخمس كلمات فقط، مكتوبة بالحروف العربية أو الإنجليزية فقط، ومفصولة بمسافات.");
        return false;
    }

    if (!va) {
        alert("يجب عليك إدخال البريد الإلكتروني بشكل صحيح");
        return false;
    }
    if (!/^\+?[\d\s-]{9,14}$/.test(phone_number)) {//مع قبول الفواصل وعلامة - (9 - 14)كود اتحقق من رقم الهاتف مابين 
    alert("يجب ادخال رقم الهاتف بشكل صحيح");
    return false;
    }
    if(password === "" || password.length < 6){
        alert("يجب ملء حقل كلمة السر بكلمة سر بحيث تحتوي على احرف ورموز وارقام");
        return false;
    }
    if (confirm_password === "" || confirm_password !== password) {
        alert("يجب أن يكون تأكيد كلمة السر مطابقًا لكلمة السر الجديدة");
        return false;
    }
    return true;
};


// دالة تبديل رؤية كلمة السر
function show_password() {
    const password = document.querySelector('#password');
    const eyes = document.querySelector(".toggle-password");
    
    if (password.type === 'password') {
        password.type = 'text';
        eyes.innerHTML = "<i class='bx bxs-show'></i>"; // أيقونة العين المفتوحة
    } else {
        password.type = 'password';
        eyes.innerHTML ="<i class='bx bxs-hide'></i>"; // أيقونة العين المغلقة
    }
 }


    </script>
</body>
</html>
