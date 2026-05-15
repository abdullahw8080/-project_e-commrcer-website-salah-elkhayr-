<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require("../admin acount/admin_session.php");
require('../database/connect_to_database.php');

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_GET['order_id'])) {
        throw new Exception('لم يتم تحديد معرّف الطلب');
    }

    $order_id = intval($_GET['order_id']);
    if ($order_id <= 0) {
        throw new Exception('معرّف الطلب غير صحيح');
    }

    // بدء المعاملة
    mysqli_begin_transaction($conn);

    // 1. الحصول على OD_id أولاً
    $query = "SELECT OD_id FROM pending_Orders WHERE Or_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        throw new Exception('لم يتم العثور على الطلب المعلق');
    }
    
    $row = mysqli_fetch_assoc($result);
    $od_id = $row['OD_id'];

    // 2. نقل العنوان إلى Orders_address
    $query = "INSERT INTO Orders_address 
              SELECT * FROM pending_Orders_address WHERE OD_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $od_id);
    mysqli_stmt_execute($stmt);
    $new_address_id = mysqli_insert_id($conn);

    // 3. نقل الطلب إلى Orders
    $query = "INSERT INTO Orders (OD_id, Total_price, Or_date)
              SELECT ?, Total_price, Or_date FROM pending_Orders WHERE Or_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $new_address_id, $order_id);
    mysqli_stmt_execute($stmt);
    $new_order_id = mysqli_insert_id($conn);

    // 4. نقل العناصر إلى order_items
    $query = "INSERT INTO order_items (P_id, or_id, quantity, price)
              SELECT P_id, ?, quantity, price FROM pending_order_items WHERE or_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $new_order_id, $order_id);
    mysqli_stmt_execute($stmt);


    // جلب كافة العناصر المنقولة لتحديث الكميات في المخزن
    $query_get_items = "SELECT P_id, quantity FROM pending_order_items WHERE or_id = $order_id";
    $result = mysqli_query($conn, $query_get_items);

    // تحديث كمية كل منتج في المخزن
    while ($product = mysqli_fetch_assoc($result)) {
    $update_query = "UPDATE products SET P_quantity = P_quantity - " . $product['quantity'] . 
                " WHERE P_id = " . $product['P_id'];
    mysqli_query($conn, $update_query);
    }


    // 5. حذف البيانات المعلقة
    $queries = [
        "DELETE FROM pending_order_items WHERE or_id = ?",
        "DELETE FROM pending_Orders WHERE Or_id = ?",
        "DELETE FROM pending_Orders_address WHERE OD_id = ?"
    ];

    foreach ($queries as $query) {
        $stmt = mysqli_prepare($conn, $query);
        $param = ($query === $queries[2]) ? $od_id : $order_id;
        mysqli_stmt_bind_param($stmt, "i", $param);
        mysqli_stmt_execute($stmt);
    }

    mysqli_commit($conn);
    $response['success'] = true;
    $response['message'] = 'تم معالجة الطلب بنجاح';

} catch (Exception $e) {
    mysqli_rollback($conn);
    error_log('Error in confirm_order.php: ' . $e->getMessage());
}

echo json_encode($response);