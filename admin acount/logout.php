<?php
    session_start(); // بدء الجلسة
    session_destroy(); // تدمير الجلسة
    header("Location: login.php"); // توجيه المستخدم إلى صفحة تسجيل الدخول
    exit(); // إنهاء التنفيذ
?>