<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['product_id']) || !isset($data['action'])) {
        echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
        exit();
    }

    $product_id = intval($data['product_id']);
    $action = $data['action'];

    if (!isset($_SESSION['cart'][$product_id])) {
        echo json_encode(['success' => false, 'message' => 'المنتج غير موجود في السلة']);
        exit();
    }

    if ($action === 'increase') {
        $_SESSION['cart'][$product_id]++;
    } elseif ($action === 'decrease') {
        if ($_SESSION['cart'][$product_id] > 1) {
            $_SESSION['cart'][$product_id]--;
        } else {
            unset($_SESSION['cart'][$product_id]); // حذف المنتج إذا كانت الكمية 1
        }
    }

    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'طلب غير صالح']);
exit();
?>
