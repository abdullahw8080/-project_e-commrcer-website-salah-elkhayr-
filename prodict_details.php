<?php
session_start(); // بدء جلسة العمل
require('database/connect_to_database.php'); // استدعاء ملف الاتصال بقواعد البيانات


if (isset($_POST["add-to-cart"])) {
    // التحقق من وجود بيانات مرسلة
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
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

            // عرض رسالة نجاح وإعادة التوجيه إلى نفس الصفحة
            echo "<script>alert('تمت إضافة المنتج إلى السلة بنجاح!'); window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
            exit();
        } else {
            // إذا كانت البيانات غير صالحة
            echo "<script>alert('البيانات غير صالحة!'); window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل المنتج</title>
    <!---ارابط استدعاء الخط -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">

    <!---ارابط استدعاء مكاتب -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        /* التنسيقات العامة */
      
        body {
            font-family: "Cairo", serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }

        /* حاوية المنتج */
        .product-container {
            background-color: #fff;
            border-radius: 10px;
            border: #2ecc71 solid 2px;
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 700px; 
            padding: 20px;
            box-sizing: border-box;
            position: relative; /* لجعل زر العودة داخل الحاوية */
        }

        /* زر العودة */
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px; /* وضع الزر على يسار الحاوية */
            background-color: #ff0000;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            z-index: 1000; /* للتأكد من ظهور الزر فوق العناصر الأخرى */
        }

        .back-button:hover {
            background-color: #d52e2e;
        }
        .add-to-prefer{
            position: absolute;
            top: 15px;
            right: 20px; /* وضع الزر على يسار الحاوية */
            color: #060000;
            background: none;
            border: none;  
            padding: 10px 15px;
            text-decoration: none;
            font-size: 24px;
            z-index: 1000; /* للتأكد من ظهور الزر فوق العناصر الأخرى */
        }

        .product-image {
            position: relative;
            width: 100%;
            min-height: 300px; /* ارتفاع أدنى للحفاظ على المساحة */
            overflow: hidden;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .product-image img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
            position: absolute;
            opacity: 0;
        }

        .product-image img.active {
            opacity: 1;
            transform: translateX(0);
            position: static; /* للحفاظ على التنسيق الأصلي */
        }

        .product-image img.next {
            transform: translateX(100%);
        }

        .product-image img.prev {
            transform: translateX(-100%);
        }

        .product-details {
            text-align: center;
            margin-top: 20px;
            width: 100%; /* للحفاظ على العرض الأصلي */
        }

        .product-name {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .product-description {
            font-size: 17px;
            color: black    ;
            margin-bottom: 15px;
        }

        .product-price {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .original-price {
            text-decoration: line-through;
            color: #999;
        }

        .discounted-price {
            color: #e74c3c;
            font-weight: bold;
        }

        .discount-rate {
            font-size: 17px;
            color: #27ae60;
            margin-bottom: 10px;
        }

        .product-quantity, .product-weight {
            font-size: 17px;
            color: #333;
            margin-bottom: 10px;
        }

        .quantity-type, .weight {
            font-size : 18px ;
        }

        .quantity-and-cart {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
        }

        .quantity-button {
            background-color: #3498db;
            color: #fff;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }

        .quantity-button:hover {
            background-color: #2980b9;
        }

        .quantity {
            font-size: 18px;
            font-weight: bold;
            margin: 0 10px;
        }

        .add-to-cart {
            font-family: "Cairo", serif;
            background-color: #2ecc71;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
            max-width: 200px;
        }

        .add-to-cart:hover {
            background-color: #27ae60;
        }
        .bx-heart{
            color: #ffbb00;
            font-size:30px;
        }

        /* التجاوب مع الشاشات المتوسطة والكبيرة */
        @media (min-width: 768px) {
            .product-container {
                flex-direction: row;
                align-items: flex-start;
                max-width: 800px;
            }

            .product-image {
                flex: 1;
                margin-right: 20px;
                min-height: 400px; /* ارتفاع أدنى أكبر للشاشات الكبيرة */
            }

            .product-image img {
                max-height: 400px;
            }

            .product-details {
                flex: 2;
                text-align: right;
            }

            .quantity-and-cart {
                flex-direction: row;
                justify-content: flex-start;
            }

            .add-to-cart {
                width: auto;
            }
        }
    </style>
</head>
<body>
    <?php 
    $product_id = intval($_GET["product_id"]); // اخذ رقم المنتج من رابط الصفحة
    // جلب المنتجات التي عليها تخفيض
    $product_query = "SELECT *
              FROM products, product_unit 
              WHERE product_unit.p_unit_id = products.p_unit_id
              AND P_id = $product_id";
                          
    $product_result = mysqli_query($conn, $product_query);
    if($product_result){
        $product_row = mysqli_fetch_assoc($product_result);
        if($product_row ["discount"] > 0){           
    ?>
    <div class="product-container"><!--استدعاء المنتج الذي لدية خصم-->

        <a href="javascript:history.back()" class="back-button">X</a> <!-- زر العودة إلى الصفحة السابقة -->
        <a href="#" class="add-to-prefer"><i class='bx bx-heart'></i></a>

        <div class="product-image">
            <!----------------------------------->
            <?php
                //كود يقوم بجلب صور المنتج 
                $product_images_query = "SELECT * FROM product_images WHERE P_id = " . $product_row['P_id'];
                $product_image_reuslt = mysqli_query($conn, $product_images_query);
                if ($product_image_reuslt) {
                    $first = true;
                    while ($product_images_row = mysqli_fetch_assoc($product_image_reuslt)) {
                ?>
                        <img src="<?php echo "images/product_images/" . $product_images_row['P_img']; ?>" <?php echo $first ? 'class="active"' : 'style="display:none"'; ?>>
                <?php
                        $first = false;
                    }}
                ?>
            <!----------------------------------->       
        </div>
        <div class="product-details">
            <h1 class="product-name"><?php echo $product_row ["P_name"]; ?></h1>
            <p class="product-description"> <b>وصف المنتج: </b><?php echo $product_row ["p_description"];?>.</p>
            <p class="product-price"> <b>السعر : </b><span class="original-price"><?php echo $product_row ["P_old_price"]; ?> ريال</span> <span class="discounted-price"><?php echo $product_row["P_new_price"]; ?> ريال</span></p>
            <p class="discount-rate"><b>نسبة التخفيض : </b><?php echo $product_row ["discount"]; ?>%</p>
            <p class="product-quantity"><b>نوع الكمية : </b><?php echo $product_row ["product_unit"]; ?></p>
            <p class="product-weight"><b>وزن المنتج: </b> <span class="weight"><?php echo  $product_row ["p_weight"];?></span></p>

            <!-- أزرار زيادة وتنقيص الكمية مع زر إضافة إلى السلة -->
            <div class="quantity-and-cart">
                <div class="quantity-controls">
                    <button class="quantity-button" id="decrease">-</button>
                    <span class="quantity" id="quantity">1</span>
                    <button class="quantity-button" id="increase">+</button>
                </div>
                   <!-- زر الإضافة إلى السلة -->
                   <div class="cart_button">
                    <form method="POST" action="">
                        <input type="hidden" name="product_id" value="<?php echo $product_row['P_id']; ?>">
                        <input type="hidden" name="quantity" class="hidden-quantity" value="1"> <!-- الكمية الافتراضية -->
                        <button type="submit" name="add-to-cart" class="add-to-cart">إضافة إلى السلة</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php } else { ?>
        <div class="product-container"><!--استدعاء المنتج الذي بدون خصم-->
        <!-- زر العودة إلى الصفحة السابقة -->
        <a href="javascript:history.back()" class="back-button">X</a>
        <a href="#" class="add-to-prefer"><i class='bx bx-heart'></i></a>
        <div class="product-image">
            <!----------------------------------->
            <?php
                //كود يقوم بجلب صور المنتج 
                $product_images_query = "SELECT * FROM product_images WHERE P_id = " . $product_row['P_id'];
                $product_image_reuslt = mysqli_query($conn, $product_images_query);
                if ($product_image_reuslt) {
                    $first = true;
                    while ($product_images_row = mysqli_fetch_assoc($product_image_reuslt)) {
                ?>
                        <img src="<?php echo "images/product_images/" . $product_images_row['P_img']; ?>" <?php echo $first ? 'class="active"' : 'style="display:none"'; ?>>
                <?php
                        $first = false;
                    }}
                ?>
            <!----------------------------------->       
        </div>
        <div class="product-details">
            <h1 class="product-name"><?php echo $product_row ["P_name"]; ?></h1>
            <p class="product-description"> <b>وصف المنتج: </b><?php echo $product_row["p_description"];?>.</p>
            <p class="product-price"><b>السعر : </b><span class="discounted-price"><?php echo $product_row["P_new_price"]; ?> ريال</span></p>
            <p class="product-quantity"><b>نوع الكمية : </b><?php echo $product_row ["product_unit"]; ?></p>
            <p class="product-weight"><b>وزن المنتج: </b> <span class="weight"><?php echo  $product_row["p_weight"];?></span></p>

            <!-- أزرار زيادة وتنقيص الكمية مع زر إضافة إلى السلة -->
            <div class="quantity-and-cart">

                <div class="quantity-controls">
                    <button class="quantity-button" id="decrease">-</button>
                    <span class="quantity" id="quantity">1</span>
                    <button class="quantity-button" id="increase">+</button>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="product_id" value="<?php echo $product_row['P_id']; ?>">
                    <input type="hidden" name="quantity" class="hidden-quantity" value="1"> <!-- الكمية الافتراضية -->
                    <button type="submit" name="add-to-cart" class="add-to-cart">إضافة إلى السلة</button>
                </form>
            </div>
        </div>
    </div>
    <?php }
    } ?>
    <script>
        // JavaScript لإدارة زيادة وتنقيص الكمية
        const quantityElement = document.getElementById('quantity');
        const increaseButton = document.getElementById('increase');
        const decreaseButton = document.getElementById('decrease');
        const hiddenQuantityInput = document.querySelector('.hidden-quantity');

        let quantity = 1;

        increaseButton.addEventListener('click', () => {
            quantity++;
            quantityElement.textContent = quantity;
            hiddenQuantityInput.value = quantity;
        });

        decreaseButton.addEventListener('click', () => {
            if (quantity > 1) {
                quantity--;
                quantityElement.textContent = quantity;
                hiddenQuantityInput.value = quantity;
                
            }
        });

        // كود تغيير الصور تلقائيًا (نسخة معدلة للحفاظ على التنسيق الأصلي)
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.product-image img');
            if (images.length > 1) {
                let currentIndex = 0;
                
                function showNextImage() {
                    // إخفاء الصورة الحالية
                    images[currentIndex].classList.remove('active');
                    images[currentIndex].style.display = 'none';
                    
                    // حساب مؤشر الصورة التالية
                    currentIndex = (currentIndex + 1) % images.length;
                    
                    // إظهار الصورة التالية
                    images[currentIndex].classList.add('active');
                    images[currentIndex].style.display = 'block';
                    
                    // إضافة تأثير الانتقال
                    images[currentIndex].style.opacity = '0';
                    setTimeout(() => {
                        images[currentIndex].style.opacity = '1';
                    }, 10);
                }
                
                // تغيير الصورة كل 3 ثواني
                setInterval(showNextImage, 3500);
            }
        });
    </script>
</body>
</html>