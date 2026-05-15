<?php
// ملف: get_customer_orders.php
// الوصف: جلب الطلبات السابقة للعميل

header('Content-Type: application/json');

// الاتصال بقاعدة البيانات
$host = "localhost";
$user = "root";
$password = "";
$db_name = "project_ecommerce";

$conn = mysqli_connect($host, $user, $password, $db_name);

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'فشل الاتصال بقاعدة البيانات']);
    exit;
}

mysqli_set_charset($conn, "utf8mb4");

// جلب رقم هاتف العميل من POST
$customer_phone = isset($_POST['customer_phone']) ? mysqli_real_escape_string($conn, $_POST['customer_phone']) : '';

if (!$customer_phone) {
    echo json_encode(['success' => false, 'message' => 'رقم هاتف العميل غير صحيح']);
    exit;
}

// استعلام لجلب الطلبات السابقة
$query = "SELECT 
    o.Or_id, o.Or_date, o.Total_price,
    tp.Type_payment
FROM Orders o
JOIN Orders_address oa ON o.OD_id = oa.OD_id
JOIN Type_payment tp ON oa.Type_payment_id = tp.Type_payment_id
WHERE oa.Client_phone = '$customer_phone'
ORDER BY o.Or_date DESC
LIMIT 10";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في جلب الطلبات']);
    exit;
}

$orders = array();
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

// إغلاق الاتصال
mysqli_close($conn);

// إرجاع البيانات كـ JSON
echo json_encode([
    'success' => true,
    'orders' => $orders
]);
?>