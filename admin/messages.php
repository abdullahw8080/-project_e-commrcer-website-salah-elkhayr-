
<?php
    require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
    require('../database/connect_to_database.php');//استدعاء ملف الاتصال بقواعد البيانات 
        //يتم وضع نقطتين في بداية المساراذا كان الملف في مجلد اخر فقط
?>

<!-- صفحة رسائل العملاء -->
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

    <title>رسائل العملاء</title>
</head>
<body>
    <div class="content" style="flex: 1;">

    <h1>رسائل العملاء</h1>
    <table border="1">
            <tr>
                <th>رقم الرسالة</th>
                <th>اسم العميل</th>
                <th>البريد الإلكتروني</th>
                <th>نص الرسالة</th>
                <th>تاريخ الرسالة</th>
            </tr>
            <!-- البيانات هنا -->
             <?php
             //كود جلب البيانات من جدول المستخدمين وجدول التعليقات
                $qurey = ("SELECT 
                    User_comments.Co_id,
                    User_comments.comment,
                    User_comments.comment_date,
                    Users.User_name,
                    Users.User_email
                    FROM 
                    User_comments  
                    INNER JOIN 
                    Users ON user_comments.user_id = Users.User_id
                    ORDER BY 
                    User_comments.Co_id DESC");
                $result = mysqli_query($conn ,$qurey);//يقوم بعمليت تنفيذ كود الجلب
                while($row = mysqli_fetch_assoc($result)){//ياخذ صف في كل دورة من قواعد البيانات
             ?>
             <tr>
                <td><?php echo $row['Co_id'];?></td><!--اسناد البيانات في الجداول-->
                <td><?php echo $row['User_name'];?></td>
                <td><?php echo $row['User_email'];?></td>
                <td><?php echo $row['comment'];?></td>
                <td><?php echo $row['comment_date'];?></td>
             </tr>
     <?php } ?>
    </table>
 </div>
</body>
</html>
