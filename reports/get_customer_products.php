<?php
// ملف: get_customer_products.php
// الوصف: جلب المنتجات المشتراة للعميل

header('Content-Type: application/json');
require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقاعدة البيانات


mysqli_set_charset($conn, "utf8mb4");

// جلب رقم هاتف العميل من POST
$customer_phone = isset($_POST['customer_phone']) ? mysqli_real_escape_string($conn, $_POST['customer_phone']) : '';

if (!$customer_phone) {
    echo json_encode(['success' => false, 'message' => 'رقم هاتف العميل غير صحيح']);
    exit;
}

// استعلام لجلب المنتجات المشتراة
$query = "SELECT 
    p.P_name, oi.quantity, oi.price,
    o.Or_id AS or_id, o.Or_date AS order_date
FROM order_items oi
JOIN products p ON oi.P_id = p.P_id
JOIN Orders o ON oi.or_id = o.Or_id
JOIN Orders_address oa ON o.OD_id = oa.OD_id
WHERE oa.Client_phone = '$customer_phone'
ORDER BY o.Or_date DESC
LIMIT 20";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في جلب المنتجات']);
    exit;
}

$products = array();
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

// إغلاق الاتصال
mysqli_close($conn);

// إرجاع البيانات كـ JSON
echo json_encode([
    'success' => true,
    'products' => $products
]);
?>