
<?php
require '../users acount/user_session.php';
if (isset($_SESSION["user_email_session"])) {
    echo "مرحبًا، " . $_SESSION["user_email_session"];
} else {
    echo "لم يتم تسجيل الدخول بعد.";
}
// التحقق من عدم بدء الجلسة بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
    require('../database/connect_to_database.php');//استدعاء ملف الاتصال بقواعد البيانات 
        //يتم وضع نقطتين في بداية المساراذا كان الملف في مجلد اخر فقط
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">

     <!---ارابط استدعاء الخط -->
     <link rel="preconnect" href="https://fonts.googleapis.com">
     <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
     <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
     
    <title>تسجيل الدخول</title>
 
</head>
<body>
    <div class="container">
        <h2>تسجيل الدخول</h2>
        <form method="post" name="Register" id="reg">
            <label for="email">البريد الإلكتروني:</label>
            <input type="email" id="email" name="email" >
            
            <label for="password">كلمة السر:</label>
            <div class="password_and_eyes_icon">
                <input type="password" id="password" name="password" >
                <span class="toggle-password" onclick="show_password()"><i class='bx bxs-show'></i></span>
            </div>

            <button type="submit" name ="regster">دخول</button>
        </form>
        <div class="links">
            <a href="signup.php">إنشاء حساب جديد</a> |
            <a href="forgot-password.php">نسيت كلمة السر؟</a> |
            <a href="../index.php">عوده الى المتجر</a>

        </div>
    </div>

    <?php
        //preg_replace('/\s+/', ' ') يحذف المسافات الاكبر من واحد من الوسط
        //trim()يحذف المسافة من البداية ونهاية
        //اسناد القيمة الذي في حقل ادخال البريد الى متغير مع حذف المسافات من الامام والمسافات الاكبر من واحدة في الوسط
        @$input_email = preg_replace('/\s+/', ' ',trim(strip_tags($_POST["email"])));
        @$input_password = preg_replace('/\s+/', ' ',trim(md5(strip_tags($_POST["password"]))));//اسناد القيمة الذي في حقل ادخال كلمة اسر الى متغير
        @$button_reg = $_POST["regster"];// الزر الاضافة
        @$type_user = 2;/*(2)جعلنا نوع المستخدم 
        حتى يتوافق مع عمود نوع المستخدم حيث ان رقم اثنين يعني ان نوع المستخدم عميل وليس ادمن*/
        //يجب وضع @ حتى لا يظهر خطاء
        //strip_tags وضيفتها تحذف الاكواد حق html , js
        if(isset($button_reg)){ //شرط عند الضغط على الزر ينفذ الكود التالي
            if(!empty($input_email) && !empty($input_password)){
                $query = ("SELECT *  from users where User_email = '$input_email' AND Password = '$input_password' AND Type_user_id = '$type_user'");
                $result = mysqli_query($conn ,$query );//كود وضع متغير الاستعلام ومتغير الاتصال بقواعد البيانات في متغير  تنفيذ الاستعلام
                
                if($result){ // تنفيذ الاستعلام
                    $row = mysqli_fetch_assoc($result);
                    if(mysqli_num_rows($result)==1){// كود يقوم بجلب  صف واحد
                    
                        echo '<script>  window.location.href = "../index.php"; // إعادة التوجيه بعد عرض الرسالة</script>';
                        $_SESSION ['user_email_session'] = $input_email ; // تخزين البريد الإلكتروني في الجلسة
                        exit();//خروج
                    }
                    else{
                        echo '<script>alert("البريد الالكتروني او كلمة السر خطأ");</script>';
                    }
                }else{
                    echo '<script>alert("حدث خطأ أثناء تنفيذ الاستعلام");</script>';
                }
                mysqli_close($conn);//اغلاق قاعدة البيانات بعد كل عملية
        }else{
            echo '<script>alert("يجب ملء جميع الحقول");</script>';
        }
     }
    ?>

<script>
    //==================================================>>
    document.getElementById("reg").onsubmit = function(){
    let email = document.getElementById("email").value;//يقوم بجلب الايميل المدخل وإسنادة للمتغير
    let emiR = /[\w.]+@(gmail|yahoo|hotmail)+.(com|org|info|net)$/is;//صيغة الايميل الذي يجيب ان يكون الايميل مكتب عليها
    let va = emiR.test(email);//مقارنة صيغة الايميل المدخل مع صيغة الايميل المحدد
    let password = document.getElementById("password").value;

    if (email === "") {
            alert("يجب عليك إدخال البريد الالكتروني");
            return false;
        }
    if (va === false) {
        alert("يجب عليك إدخال البريد الالكتروني بشكل صحيح");
        return false;
    }

    if(password === ""){
        alert("يجب  إدخال كلمة السر ");
        return false;
    }
        return true ;
    }

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