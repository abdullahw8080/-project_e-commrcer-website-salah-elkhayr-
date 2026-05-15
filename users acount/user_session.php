<?php
// تفعيل عرض الأخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// التحقق من عدم بدء الجلسة بالفعل
if (session_status() === PHP_SESSION_NONE) {
    // تعديل إعدادات الجلسة قبل بدئها
    $lifetime = 60 * 60 * 24 * 365 * 10; // 10 سنوات
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => '', // يمكنك تعيين النطاق إذا لزم الأمر
        'secure' => false, // قم بتعيينه إلى true إذا كنت تستخدم HTTPS
        'httponly' => true, // يمنع الوصول إلى الكوكي عبر JavaScript
        'samesite' => 'Lax' // يحسن الأمان
    ]);

    // تعيين مدة صلاحية ملفات الجلسة على الخادم
    ini_set('session.gc_maxlifetime', $lifetime);

    // بدء الجلسة
    session_start();

    // التحقق من وجود متغير الجلسة
/*if (isset($_SESSION["user_email_session"])) {
    echo "مرحبًا، " . $_SESSION["user_email_session"];
} else {
    echo "لم يتم تسجيل الدخول بعد.";
}*/
}

// التحقق من وجود متغير الجلسة

?>