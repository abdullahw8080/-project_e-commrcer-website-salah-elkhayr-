<?php
// ملف: get_customer_monthly_sales.php
// الوصف: جلب المبيعات الشهرية للعميل

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

// استعلام لجلب المبيعات الشهرية
$query = "SELECT 
    MONTH(o.Or_date) AS month,
    SUM(o.Total_price) AS total_sales
FROM Orders o
JOIN Orders_address oa ON o.OD_id = oa.OD_id
WHERE oa.Client_phone = '$customer_phone'
    AND YEAR(o.Or_date) = YEAR(CURRENT_DATE)
GROUP BY MONTH(o.Or_date)
ORDER BY month";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في جلب المبيعات الشهرية']);
    exit;
}

$monthly_sales = array();
while ($row = mysqli_fetch_assoc($result)) {
    $monthly_sales[] = $row;
}

// إغلاق الاتصال
mysqli_close($conn);

// إرجاع البيانات كـ JSON
echo json_encode([
    'success' => true,
    'monthly_sales' => $monthly_sales
]);
?>