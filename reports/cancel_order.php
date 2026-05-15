<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../database/connect_to_database.php';

$response = ['success' => false, 'message' => ''];

try {
    // التحقق من وجود معرف الطلب
    if (!isset($_GET['order_id'])) {
        throw new Exception('لم يتم تحديد معرّف الطلب');
    }

    $orderId = intval($_GET['order_id']);
    if ($orderId <= 0) {
        throw new Exception('معرّف الطلب غير صحيح');
    }

    // بدء المعاملة
    mysqli_begin_transaction($conn);

    // 1. الحصول على OD_id أولاً
    $query = "SELECT OD_id FROM pending_Orders WHERE Or_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        throw new Exception('لم يتم العثور على الطلب المعلق');
    }
    
    $row = mysqli_fetch_assoc($result);
    $odId = $row['OD_id'];

    // 2. حذف عناصر الطلب أولاً
    $query = "DELETE FROM pending_order_items WHERE or_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);

    // 3. حذف الطلب الرئيسي
    $query = "DELETE FROM pending_Orders WHERE Or_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);

    // 4. حذف العنوان
    $query = "DELETE FROM pending_Orders_address WHERE OD_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $odId);
    mysqli_stmt_execute($stmt);

    // تأكيد العملية
    mysqli_commit($conn);
    
    $response['success'] = true;
    $response['message'] = 'تم إلغاء الطلب بنجاح';

} catch (Exception $e) {
    mysqli_rollback($conn);
    $response['message'] = $e->getMessage();
    error_log('Error in cancel_order.php: ' . $e->getMessage());
}

echo json_encode($response);