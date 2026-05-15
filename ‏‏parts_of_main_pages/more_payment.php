<?php
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

        // عرض رسالة SweetAlert مباشرة دون إعادة التوجيه
        echo '<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>';
        echo '<script>
                swal("تمت الإضافة!", "تمت إضافة المنتج إلى السلة بنجاح!", "success").then(() => {
                window.location.href = "' . $_SERVER['HTTP_REFERER'] . '"; });
             </script>';
    } 
}
?>

<!--=======================( قسم المنتجات الاكثر مبيعا)========================-->
<section class="section_Offers" id="more_payment_id" style="margin-top: 63px;">
    <div class="items_and_text_headr">
         <h2>المنتجات الاكثر مبيعا</h2>
        <div class="Products">
            <?php
                // جلب المنتجات الأكثر مبيعًا
                $orders_query = "SELECT P_id FROM order_items GROUP BY P_id HAVING COUNT(*) >= 5";
                $orders_result = mysqli_query($conn, $orders_query);
                
                if ($orders_result  && mysqli_num_rows($orders_result ) > 0) {
                    while ($orders_row = mysqli_fetch_assoc($orders_result)) {
                        // جلب التفاصيل الخاصة بالمنتجات
                        $product_query ="SELECT *
                                        FROM 
                                            products, product_unit 
                                        WHERE 
                                            product_unit.p_unit_id = products.p_unit_id 
                                        AND 
                                            products.P_id = '" . $orders_row['P_id'] . "'";
                             
                        $product_reuslt = mysqli_query($conn, $product_query);

                        if ($product_reuslt) {
                            while ($product_row = mysqli_fetch_assoc($product_reuslt)) {
                                if($product_row['P_quantity'] > 0 ){//يتحقق هل مازال كمية للمنتج في المخزن يعرضة غير ذالك لا يعرض المنتجات الذي خلصة في المخزن

                                    // عرض المنتج حسب وجود الخصم
                                    if ($product_row['discount'] > 0) {
                                        // عرض المنتج مع خصم
                                        ?>
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
                                            <div class="quantity-control">
                                                <button class="expand-quantity">+</button>
                                                <div class="quantity-input-container">
                                                    <button class="decrease">-</button>
                                                    <form action="" method="POST">
                                                        <input type="number" value="1" min="1" class="quantity" name="quantity" />
                                                        <input type="hidden" name="count_quantity" class="count_quantity" />
                                                    </form>
                                                    <button class="increase">+</button>
                                                </div>
                                            </div>
                                            <div class="descraip">
                                                <p><?php echo $product_row["P_name"]; ?></p>
                                                <p><s><?php echo $product_row["P_old_price"]; ?></s> <?php echo $product_row["P_new_price"]; ?> <span>ر.ي</span>
                                                <span class="span_2"><?php echo $product_row["product_unit"]; ?></span></p>
                                            </div>
                                            <div class="cart_button">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="product_id" value="<?php echo $product_row['P_id']; ?>">
                                                    <input type="hidden" name="quantity" class="hidden-quantity" value="1">
                                                    <button type="submit">أضف إلى السلة <i class='bx bx-cart'></i></button>
                                                </form>
                                            </div>
                                            <div class="heart">
                                                <i class='bx bx-heart'></i>
                                            </div>
                                            <div class="discount">
                                                <p><?php echo $product_row["discount"]; ?>%-</p>
                                            </div>
                                        </div>
                                        <?php
                                    } else {
                                        // عرض المنتج بدون خصم
                                        ?>
                                        <div class="item_product">
                                                <a href="prodict_details.php?product_id=<?php echo $product_row['P_id']; ?>"><!--رابط فتح تفاصيل المنتج-->
                                                    <!----------------------------------->
                                                        <?php
                                                            //كود يقوم بجلب صور المنتج 
                                                            $product_images_query = "SELECT * FROM product_images WHERE P_id = " . $product_row['P_id'];
                                                            $product_image_reuslt = mysqli_query($conn,$product_images_query);
                                                            if ($product_image_reuslt) {
                                                                $product_images_row = mysqli_fetch_assoc($product_image_reuslt);
                                                        ?>
                                                                    <img src="<?php echo "images/product_images/" . $product_images_row['P_img']; ?>">
                                                        <?php
                                                            }
                                                        ?>
                                                    <!----------------------------------->
                                                </a>              
                                                <div class="quantity-control">
                                                <button class="expand-quantity">+</button>
                                                <div class="quantity-input-container">
                                                    <button class="decrease">-</button>
                                                    <form action="" method="POST">
                                                        <input type="number" value="1" min="1" class="quantity" name="quantity" />
                                                        <input type="hidden" name="count_quantity" class="count_quantity" />
                                                    </form>
                                                    <button class="increase">+</button>
                                                </div>
                                            </div>
                                            <div class="descraip">
                                                <p><?php echo $product_row["P_name"]; ?></p>
                                                <p><?php echo $product_row["P_new_price"]; ?> <span>ر.ي</span>
                                                <span class="span_2"><?php echo $product_row["product_unit"]; ?></span></p>
                                            </div>
                                            <div class="cart_button">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="product_id" value="<?php echo $product_row['P_id']; ?>">
                                                    <input type="hidden" name="quantity" class="hidden-quantity" value="1">
                                                    <button type="submit">أضف إلى السلة <i class='bx bx-cart'></i></button>
                                                </form>
                                            </div>
                                            <div class="heart">
                                                <i class='bx bx-heart'></i>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                            }
                        }
                    }
                }
            ?>
        </div>
    </div>
</section>
