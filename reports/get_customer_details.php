<?php
// ملف: get_customer_details.php
// الوصف: جلب تفاصيل العميل من قاعدة البيانات

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

// جلب بيانات العميل من POST
$customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;
$customer_phone = isset($_POST['customer_phone']) ? mysqli_real_escape_string($conn, $_POST['customer_phone']) : '';

if (!$customer_id || !$customer_phone) {
    echo json_encode(['success' => false, 'message' => 'معرّف العميل أو رقم الهاتف غير صحيح']);
    exit;
}

// استعلام لجلب تفاصيل العميل
$query = "SELECT 
    u.User_id, u.User_name, u.User_email, u.User_phone,
    oa.Client_city,
    COUNT(o.Or_id) AS orders_count,
    SUM(o.Total_price) AS total_spent,
    AVG(o.Total_price) AS avg_order_value,
    MAX(o.Or_date) AS last_order_date
FROM Users u
LEFT JOIN Orders_address oa ON u.User_phone = oa.Client_phone
LEFT JOIN Orders o ON oa.OD_id = o.OD_id
WHERE u.User_id = $customer_id AND u.User_phone = '$customer_phone'
GROUP BY u.User_id";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'العميل غير موجود']);
    exit;
}

$customer = mysqli_fetch_assoc($conn, $result);

// إغلاق الاتصال
mysqli_close($conn);

// إرجاع البيانات كـ JSON
echo json_encode([
    'success' => true,
    'customer' => [
        'User_id' => $customer['User_id'],
        'User_name' => $customer['User_name'],
        'User_email' => $customer['User_email'],
        'User_phone' => $customer['User_phone'],
        'Client_city' => $customer['Client_city'],
        'orders_count' => (int)$customer['orders_count'],
        'total_spent' => (float)$customer['total_spent'],
        'avg_order_value' => (float)$customer['avg_order_value'],
        'last_order_date' => $customer['last_order_date']
    ]
]);
?>