<?php
$host     = "localhost";
$user     = "root";
$password = "";
$db_name  = "project_ecommerce";

// إنشاء الاتصال بقاعدة البيانات
$conn = mysqli_connect($host, $user, $password, $db_name);

// التحقق من نجاح الاتصال
if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

// ضبط الترميز إلى utf8mb4 للتعامل مع الأحرف العربية بشكل صحيح
if (!mysqli_set_charset($conn, "utf8mb4")) {
    die("فشل ضبط الترميز: " . mysqli_error($conn));
}

// في حال نجاح الاتصال يمكن عرض رسالة (اختياري)
// echo '<script>alert("تم الاتصال بقاعدة البيانات بنجاح");</script>';
?>
