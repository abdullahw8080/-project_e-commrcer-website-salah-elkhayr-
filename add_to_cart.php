<!--ملف add_to_cart-->

<?php

// التحقق من وجود بيانات مرسلة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // الحصول على معرف المنتج والكمية
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // التحقق من صحة البيانات
    if ($product_id > 0 && $quantity > 0) {
        // إذا كانت السلة غير موجودة، ننشئها
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // إذا كان المنتج موجودًا في السلة، نزيد الكمية
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            // إذا لم يكن المنتج موجودًا، نضيفه إلى السلة
            $_SESSION['cart'][$product_id] = $quantity;
        }

        // إعادة توجيه المستخدم إلى صفحة المنتجات مع رسالة نجاح
        header("Location: " . $_SERVER['HTTP_REFERER']); // العودة إلى الصفحة السابقة
        exit();
    } else {
        // إعادة توجيه المستخدم مع رسالة خطأ
        header("Location: " . $_SERVER['HTTP_REFERER']); // العودة إلى الصفحة السابقة
        exit();
    }
} else {
    // إعادة توجيه المستخدم إذا لم يتم إرسال البيانات بشكل صحيح
   // header("Location: index.php");
   // exit();
}
?>

    <!-- عرض رسائل النجاح أو الخطأ -->
 <?php
  if (isset($_GET['success'])) {
        echo '<script>alert("تم !ضافة المنتج بنجاح");</script>';
    }
  if (isset($_GET['error'])) {
        echo '<script>alert("حدث خطأ أثناء إضافة المنتج إلى السلة.");</script>';
    }
?>