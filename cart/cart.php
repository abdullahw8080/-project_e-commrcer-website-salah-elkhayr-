<?php
ob_start(); // تفعيل مخزن المؤقت للإخراج
session_start(); // بدء الجلسة
require('../database/connect_to_database.php'); // الاتصال بقاعدة البيانات

// التحقق من وجود بيانات مرسلة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // الحصول على معرف المنتج
    $product_id = intval($_POST['product_id']);

    // التحقق من وجود المنتج في السلة
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]); // حذف المنتج من السلة
    }

    // إعادة توجيه المستخدم إلى صفحة السلة
    header("Location: cart.php");
    exit();
}

// تهيئة متغيرات
$cart_items = [];
$total_price = 0;

// جلب تفاصيل المنتجات في السلة من قاعدة البيانات
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $query = "SELECT * FROM products, product_unit, section ,product_images
                  WHERE products.P_id = '$product_id' 
                  AND product_unit.p_unit_id = products.p_unit_id 
                  AND section.Section_id = products.Section_id
                  AND product_images.P_id = products.P_id ";
                  
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $cart_items[] = [
                'id' => $row['P_id'],
                'name' => $row['P_name'],
                'price' => $row['P_new_price'],
                'quantity' => $quantity,
                'image' => $row['P_img'],
                'unit' => $row['product_unit'],
                'section' => $row['Section_name']
            ];

            // حساب السعر الإجمالي
            $total_price += $row['P_new_price'] * $quantity;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cart_style.css">
    <!---رابط استدعاء الخط -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <link rel="icon" type = "image/png"href="../images/home_image/logo4.png"><!--رابط ايقونت الموقع-->

     <!---ارابط استدعاء الايقونات -->
     <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">

    <title>سلة الطلبات</title>
   
</head>
<body>
    <div class="content">
        <h1>سلة المنتجات</h1>
        <?php if (!empty($cart_items)): ?>
            <?php foreach ($cart_items as $item): ?>
                <div class="product-card">
                    <img src="../images/product_images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="product-image">
                    <div class="product-info">
                        <div class="product-name"><?php echo $item['name']; ?></div>
                        <div class="product-price"><?php echo $item['price']; ?> ر.ي</div>
                        <div class="quantity-control">
                            
                            <a href="cart.php"><button onclick="decreaseQuantity(<?php echo $item['id']; ?>)">-</button></a>
                            <span id="quantity-<?php echo $item['id']; ?>"><?php echo $item['quantity']; ?></span>
                            <a href="cart.php"><button onclick="increaseQuantity(<?php echo $item['id']; ?>)">+</button></a>

                        </div>
                        <span><?php echo $item['unit']; ?></span>

                        <div>الإجمالي: <?php echo $item['price'] * $item['quantity']; ?> ر.ي</div>
                    </div>
                    <form method="POST" action="" class="delete-product">
                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                        <button type="submit" class="delete-button"><i class='bx bxs-trash'></i></button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 20px;">السلة فارغة.</div>
        <?php endif; ?>

        <div class="total-price">
            إجمالي السعر الكلي: <span><?php echo $total_price; ?> ر.ي</span>
        </div>

        <div class="action-buttons">
            <button onclick="window.location.href='order_addres.php'">تأكيد الطلب</button>
            <button onclick="window.location.href='../index.php'" class="go-back-button">العودة إلى الصفحة الرئيسية</button>
        </div>
    </div>

   <!--------------------(js زيادت وتنقيص الكمية داخل السلة)---------------------> 
   
    <script>
    async function updateQuantity(productId, action) {
    try {
        const response = await fetch('update_quantity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId,
                action: action // 'increase' أو 'decrease'
            }),
        });

        const result = await response.json();

        if (result.success) {
            // تحديث الكمية في الصفحة بدون إعادة تحميل
            const quantityElement = document.getElementById(`quantity-${productId}`);
            let newQuantity = parseInt(quantityElement.innerText);

            if (action === 'increase') {
                newQuantity++;
            } else if (action === 'decrease' && newQuantity > 1) {
                newQuantity--;
            } else {
                // حذف المنتج إذا وصلت الكمية إلى 0
                location.reload();
                return;
            }

            quantityElement.innerText = newQuantity;
        } else {
            alert(result.message || 'حدث خطأ أثناء تحديث الكمية.');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function increaseQuantity(productId) {
    updateQuantity(productId, 'increase');
}

function decreaseQuantity(productId) {
    updateQuantity(productId, 'decrease');
}

    </script>
</body>
</html>

<?php
mysqli_close($conn); // إغلاق الاتصال بقاعدة البيانات
ob_end_flush(); // إرسال المحتوى المخزن
?>