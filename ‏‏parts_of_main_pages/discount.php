

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عروض نهاية الأسبوع</title>
 
</head>
<body>

<!--=======================( قسم العروض)========================-->
<section class="section_Offers" id="section_Offers_id">
    <!-- قسم العروض الخاصة -->
    <div class="items_and_text_headr"><!--items_and_text_headr start-->
        <h2>تخفيضات نهاية الأسبوع</h2>
        <!--  المنتجات -->
        <div class="Products"><!--Products start-->

        <?php
        // كود يقوم بجلب المنتجات التي عليها تخفيض
        $product_query = "SELECT * FROM products, product_unit WHERE product_unit.p_unit_id = products.p_unit_id AND products.discount > 0";
        $product_reuslt = mysqli_query($conn, $product_query);

        while ($product_row = mysqli_fetch_assoc($product_reuslt)) { // دوارة تقوم بجلب صف واحد كل مرة من قاعدة البيانات ثم إنشاء شريحة منتج ووضع البيانات فيها
            if($product_row['P_quantity'] > 0 ){//يتحقق هل مازال كمية للمنتج في المخزن يعرضة غير ذالك لا يعرض المنتجات الذي خلصة في المخزن
        ?>
            <!-- شريحة المنتج -->
            <div class="item_product">
                <a href="prodict_details.php?product_id=<?php echo $product_row['P_id']; ?>"><!--رابط فتح تفاصيل المنتج-->
                    <!----------------------------------->
                        <?php
                            //كود يقوم بجلب صور المنتج 
                            $product_images_query = "SELECT * FROM product_images WHERE P_id = " . $product_row['P_id'];
                            $product_image_reuslt = mysqli_query($conn, $product_images_query);
                            if ($product_image_reuslt) {
                                $product_images_row = mysqli_fetch_assoc($product_image_reuslt);
                        ?>
                                    <img src="<?php echo "images/product_images/" . $product_images_row['P_img']; ?>">
                        <?php
                            }
                        ?>
                    <!----------------------------------->
                </a>
                <!-- وحدة التحكم في الكمية -->
                <div class="quantity-control">
                    <button class="expand-quantity">+</button>
                    <!-- حاوية إدخال الكمية -->
                    <div class="quantity-input-container">
                        <button class="decrease">-</button>
                            <form action="" method="POST">
                                <input type="number" value="1" min="1" class="quantity" name="quantity" />
                                <input type="hidden" name="count_quantity" class="count_quantity" />
                            </form>
                        <button class="increase">+</button>
                    </div>
                </div>
                <!-- وصف المنتج -->
                <div class="descraip">
                    <p><?php echo $product_row["P_name"]; ?></p>
                    <p>
                        <s><?php echo $product_row["P_old_price"]; ?></s> <?php echo $product_row["P_new_price"]; ?>
                        <span>ر.ي</span>
                        <span class="span_2"><?php echo $product_row["product_unit"]; ?></span>
                    </p>
                </div>
                <!-- زر الإضافة إلى السلة -->
                <div class="cart_button">
                    <form method="POST" action="">
                        <input type="hidden" name="product_id" value="<?php echo $product_row['P_id']; ?>">
                        <input type="hidden" name="quantity" class="hidden-quantity" value="1"> <!-- الكمية الافتراضية -->
                        <button type="submit"> أضف إلى السلة <i class='bx bx-cart'></i></button>
                    </form>
                </div>
                <!-- زر المفضلة -->
                <div class="heart">
                    <i class='bx bx-heart'></i>
                </div>
                <!-- خصم المنتج -->
                <div class="discount">
                    <p><?php echo $product_row["discount"]; ?>%-</p>
                </div><!--item_product end-->
            </div>

        <?php
            }
        } // إغلاق دوارة ال while
        ?>
        </div><!--Products end-->
    </div><!--items_and_text_headr end-->
</section>



</body>
</html>