
<?php
    require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
    require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقاعدة البيانات
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type = "image/png"href="../images/home_image/logo4.png"><!--رابط ايقونت الموقع-->

    <title>لوحة التحكم - السوق الإلكتروني</title>
<style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #218838;
            color: white;
            height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }
        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            background-color: #005713;
        }
        .sidebar a:hover {
            background-color: #00731b;
        }
        .content {
            flex: 1;
            padding: 20px;
            box-sizing: border-box;
        }
</style>
</head>
<body>
    <div class="sidebar">
        <h2>لوحة التحكم</h2>
        <a href="../index.php" target="_blank">الصفحة الرئيسية</a>
        <a href="add_section.php" target="_blank">إضافة قسم</a>
        <a href="add_product.php" target="_blank">إضافة منتج</a>
        <a href="products.php" target="_blank">المنتجات</a>
        <a href="orders.php" target="_blank">طلبات الزبائن</a>
        <a href="messages.php" target="_blank">رسائل العملاء</a>
        <a href="../admin acount/signup.php" target="_blank">إنشاء حساب موظف</a>
        <a href="../admin acount/logout.php">تسجيل الخروج</a><!--إستدعاء صفحة انها الجلسة والخروج-->
    </div>
    <div class="content">
        <h1>مرحبًا بك في لوحة التحكم</h1>
        <p>اختر إحدى الخيارات من القائمة الجانبية.</p>
    </div>
</body>
</html>