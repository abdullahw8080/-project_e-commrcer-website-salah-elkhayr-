<?php
/*// تعيين مدة الجلسة إلى 30 ثانية
session_set_cookie_params(60);

// التحقق من وقت انتهاء الجلسة
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 30)) {
    // تدمير الجلسة بعد انتهاء المهلة
    session_unset();
    session_destroy();
    header("Location: ../admin acount/login.php");
    exit();
}

// تحديث وقت النشاط الأخير
$_SESSION['last_activity'] = time();*/
session_start();

// التحقق من وجود متغير الجلسة 'email'
if (!isset($_SESSION['admin_email'])) {
    header("Location: ../admin acount/login.php");
    exit();
}
?>
