<?php
require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقواعد البيانات

if (isset($_GET['____confirm_order___'])) {
    $order_id = intval($_GET['____confirm_order___']); // تحويل معرّف الطلب إلى عدد صحيح

    // بدء المعاملة لضمان إتمام العمليات جميعها أو التراجع عنها في حالة حدوث خطأ
    mysqli_begin_transaction($conn);

    try {
        // استعلام للحصول على معرّف العنوان (OD_id) الخاص بالطلب
        $query_get_od_id = "SELECT OD_id FROM pending_Orders WHERE Or_id = $order_id;";
        $result_od_id = mysqli_query($conn, $query_get_od_id);
        $od_id = mysqli_fetch_assoc($result_od_id)['OD_id'];
        
        // نقل العنوان من جدول العناوين المعلقة إلى جدول العناوين الرئيسية
        $query_transfer_address = "
            INSERT INTO Orders_address (OD_id, Client_name, Client_phone, Client_state, Client_city, Client_street, Client_neb, Billing_id, Dep_id, Type_payment_id)
            SELECT OD_id, Client_name, Client_phone, Client_state, Client_city, Client_street, Client_neb, Billing_id, Dep_id, Type_payment_id
            FROM pending_Orders_address WHERE OD_id = $od_id;
        ";
        mysqli_query($conn, $query_transfer_address);

        // نقل الطلب من جدول الطلبات المعلقة إلى جدول الطلبات الرئيسية
        $query_transfer_order = "
            INSERT INTO Orders (OD_id, Total_price, Or_date)
            SELECT OD_id, Total_price, Or_date FROM pending_Orders WHERE Or_id = $order_id;
        ";
        mysqli_query($conn, $query_transfer_order);

        // استعلام للحصول على معرّف الطلب الجديد من جدول الطلبات الرئيسية
        $query_get_new_order_id = "SELECT Or_id FROM Orders ORDER BY Or_id DESC LIMIT 1;";
        $result_new_order_id = mysqli_query($conn, $query_get_new_order_id);
        $new_order_id = mysqli_fetch_assoc($result_new_order_id)['Or_id'];

        // نقل المنتجات من جدول المنتجات المعلقة إلى جدول المنتجات الرئيسية
        $query_transfer_items = "
        INSERT INTO order_items (P_id, or_id, quantity, price)
        SELECT P_id, $new_order_id, quantity, price FROM pending_order_items WHERE or_id = $order_id";
        mysqli_query($conn, $query_transfer_items);

        // جلب كافة العناصر المنقولة لتحديث الكميات في المخزن
        $query_get_items = "SELECT P_id, quantity FROM pending_order_items WHERE or_id = $order_id";
        $result = mysqli_query($conn, $query_get_items);

        // تحديث كمية كل منتج في المخزن بحيث ينقص كمية المخزن المخزنة بناء على كمية المنتج المطلوبة
        while ($product = mysqli_fetch_assoc($result)) {
        $update_query = "UPDATE products SET P_quantity = P_quantity - " . $product['quantity'] . 
                    " WHERE P_id = " . $product['P_id'];
        mysqli_query($conn, $update_query);
        }


        $query_delete_pending_address = "DELETE FROM pending_Orders_address WHERE OD_id = $od_id;";
        mysqli_query($conn, $query_delete_pending_address);

        // حذف المنتجات من جدول المنتجات المعلقة
        $query_delete_pending_items = "DELETE FROM pending_order_items WHERE or_id = $order_id;";
        mysqli_query($conn, $query_delete_pending_items);

        // حذف الطلب من جدول الطلبات المعلقة
        $query_delete_pending_order = "DELETE FROM pending_Orders WHERE Or_id = $order_id;";
        mysqli_query($conn, $query_delete_pending_order);

        // إنهاء المعاملة بنجاح
        mysqli_commit($conn);
        echo '<script>alert("تم تأكيد الطلب ونقله بنجاح."); window.location.href = "orders.php";</script>';
    } catch (Exception $e) {
        // التراجع عن كافة العمليات إذا حدث خطأ
        mysqli_rollback($conn);
        echo "حدث خطأ أثناء تأكيد الطلب: " . $e->getMessage();
    }
} else {
    echo "لم يتم تحديد الطلب للتأكيد.";
}
?>
