<?php
    include('‏‏parts_of_main_pages/header.php');

    // استدعاء صفحة الهيدر

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

<section class="section_Offers" id="section_Offers_id" style="margin-top: 63px;">
    <!-- قسم العروض الخاصة -->
    <div class="items_and_text_headr"><!--items_and_text_headr start-->
        <h2>نتيجة البحث</h2>
        <!-- المنتجات -->
        <div class="Products"><!--Products start-->

        <?php
        if (isset($_GET['button_search'])) {//اذا تم الضغط على زر البحث
            $search = trim(strip_tags($_GET['search_input'])); // تنظيف المدخلات واذ القيمة من حقل البحث واسنادها لمتغير 
            //كود يقوم بجراء استعلام حول  البحث
            $product_query = ("
                SELECT 
                    products.P_id, 
                    products.P_name,
                    products.P_old_price,
                    products.P_new_price, 
                    products.P_quantity,
                    products.discount,
                    product_unit.product_unit 
                FROM 
                    products 
                JOIN 
                    product_unit 
                ON 
                    products.p_unit_id = product_unit.p_unit_id
                WHERE 
                    products.P_name LIKE '%$search%'
            ");
            $product_result = mysqli_query($conn, $product_query);//تنفيذ الاستعلام

        if (mysqli_num_rows($product_result)) {//اذا كانت هناك نتيجة
             while ($product_row = mysqli_fetch_assoc($product_result)) {//خذ صف لك دورة
                    // دوارة تقوم بأخذ صف واحد كل مرة من قاعدة البيانات ثم تقوم بإنشاء شريحة منتج ووضع البيانات فيها
                if($product_row['P_quantity'] > 0 ){//يتحقق هل مازال كمية للمنتج في المخزن يعرضة غير ذالك لا يعرض المنتجات الذي خلصة في المخزن

                    if ($product_row["discount"] > 0) {
            //====================================(كود يقوم بستدعاء المنتج الذي لية خصم)================================================>>
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
                                        while ($product_images_row = mysqli_fetch_assoc($product_image_reuslt)) {
                                ?>
                                            <img src="<?php echo "images/product_images/" . $product_images_row['P_img']; ?>">
                                <?php
                                    }}
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
                //====================================(كود يقوم بجلب المنتجات الذي بدون خصم)================================================>>
                    } else {
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
                                        while ($product_images_row = mysqli_fetch_assoc($product_image_reuslt)) {
                                ?>
                                            <img src="<?php echo "images/product_images/" . $product_images_row['P_img']; ?>">
                                <?php
                                    }}
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
                                </s> <?php echo $product_row["P_new_price"]; ?>
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
                    </div><!--item_product end-->
        <?php
                    } // نهاية if-else
                }
                } // نهاية while
            } else {//اذا لم يكن هناك نتيجة
                echo '<script> alert("لا يوجد منتج بهذا الاسم"); window.location.href = "index.php"; // إعادة التوجيه بعد عرض الرسالة</script>';
            }
        } // نهاية if isset
        ?>

        </div><!--Products end-->
    </div><!--items_and_text_headr end-->
</section>

<!--=======================(footer استدعاء صفحة)========================-->
<?php
    include('‏‏parts_of_main_pages/footer.php');
?>