<?php
require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقواعد البيانات

if (isset($_GET['____reject_order___'])) {
    $order_id = intval($_GET['____reject_order___']); // تحويل معرّف الطلب إلى عدد صحيح

    // بدء عملية الحذف
    mysqli_begin_transaction($conn);

    try {
        // حذف العناصر المرتبطة بالطلب من pending_order_items
        $sql1 = "DELETE FROM pending_order_items WHERE or_id = $order_id";
        mysqli_query($conn, $sql1);

        // حذف الطلب من pending_Orders
        $sql2 = "DELETE FROM pending_Orders WHERE Or_id = $order_id";
        mysqli_query($conn, $sql2);

        // حذف العنوان المرتبط بالطلب من pending_Orders_address
        $sql3 = "DELETE FROM pending_Orders_address WHERE OD_id = (SELECT OD_id FROM pending_Orders WHERE Or_id = $order_id)";
        mysqli_query($conn, $sql3);

        // تأكيد العملية
        mysqli_commit($conn);

        echo '<script>alert("تم رفض الطلب بنجاح!"); window.location.href = "orders.php";</script>';
    } catch (Exception $e) {
        // التراجع عن العملية في حالة حدوث خطأ
        mysqli_rollback($conn);
        echo "حدث خطأ أثناء رفض الطلب: " . $e->getMessage();
    }
}
?>