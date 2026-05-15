<?php
    require("../admin acount/admin_session.php");
    require('../database/connect_to_database.php');

    $product_id = $_GET["upatat_product_id"];
    if (isset($_GET["upatat_product_id"])) {
        $query = "SELECT 
            products.P_name,
            products.P_old_price,
            products.P_new_price,
            products.P_quantity,
            products.discount,
            products.p_description,
            products.p_weight,
            product_unit.product_unit,
            section.Section_name
        FROM 
            products,
            product_unit,
            section
        WHERE
            products.P_id = $product_id AND 
            product_unit.p_unit_id = products.p_unit_id AND 
            section.Section_id = products.Section_id";

        $result = mysqli_query($conn, $query);
        if ($result) {
            $row_1 = mysqli_fetch_assoc($result);
        }
    }

    if (isset($_POST['update-product-button'])) {
        @$id = $_GET['upatat_product_id'];
        if (isset($id)) {
            $product_name = trim(strip_tags($_POST['product-name']));
            $product_descrption = strip_tags($_POST['p_descrption']);
            $old_price = trim(strip_tags($_POST['old-price']));
            $discount = trim(strip_tags($_POST['discount']));
            $new_price = $old_price - ($old_price * $discount / 100);
            $product_quantity = strip_tags($_POST['P_quantity']);
            $unit = $_POST['unit'];
            $product_weight = ($_POST['p_weight']);
            $section = $_POST['Pr_section'];

            $product_image_1 = ($_FILES['product-image_1']['name']);
            $tmp_name_1 = ($_FILES['product-image_1']['tmp_name']);

            $product_image_2 = ($_FILES['product-image_2']['name']);
            $tmp_name_2 = ($_FILES['product-image_2']['tmp_name']);

            $product_image_3 = ($_FILES['product-image_3']['name']);
            $tmp_name_3 = ($_FILES['product-image_3']['tmp_name']);

            $allowed_types = ['image/jpeg', 'image/png', 'image/PNG', 'image/jpg'];

            if (!empty($product_name) && !empty($product_descrption) && !empty($old_price) && $discount>=0 && !empty($unit) && !empty($product_weight) && !empty($section)) {
                try {
                    $product_query = "UPDATE products SET 
                        P_name = '$product_name',
                        p_description = '$product_descrption',
                        P_old_price = '$old_price',
                        P_new_price = '$new_price',
                        P_quantity = '$product_quantity',
                        discount = '$discount',
                        p_unit_id = '$unit',
                        p_weight = '$product_weight',
                        Section_id = '$section'
                    WHERE 
                        products.P_id = '$id'";

                    $product_result = mysqli_query($conn, $product_query);
                    
                    if (!empty($product_image_1) || !empty($product_image_2) || !empty($product_image_3)) {
                        $delete_images = "DELETE FROM product_images WHERE P_id = '$id'";
                        mysqli_query($conn, $delete_images);
                        
                        if (!empty($product_image_1)) {
                            $image_type_1 = mime_content_type($tmp_name_1);
                            if (in_array($image_type_1, $allowed_types)) {
                                $new_image_name_1 = rand(0, 1000000) . "_" . $product_image_1;
                                move_uploaded_file($tmp_name_1, "../images/product_images/" . $new_image_name_1);
                                $images_query_1 = "INSERT INTO product_images (P_id, P_img) VALUES ('$id', '$new_image_name_1')";
                                mysqli_query($conn, $images_query_1);
                            } else {
                                echo '<script>alert("هذة الصورة غير مسموح بها");</script>';
                            }
                        }
                        
                        if (!empty($product_image_2)) {
                            $image_type_2 = mime_content_type($tmp_name_2);
                            if (in_array($image_type_2, $allowed_types)) {
                                $new_image_name_2 = rand(0, 1000000) . "_" . $product_image_2;
                                move_uploaded_file($tmp_name_2, "../images/product_images/" . $new_image_name_2);
                                $images_query_2 = "INSERT INTO product_images (P_id, P_img) VALUES ('$id', '$new_image_name_2')";
                                mysqli_query($conn, $images_query_2);
                            } else {
                                echo '<script>alert("هذة الصورة غير مسموح بها");</script>';
                            }
                        }
                        
                        if (!empty($product_image_3)) {
                            $image_type_3 = mime_content_type($tmp_name_3);
                            if (in_array($image_type_3, $allowed_types)) {
                                $new_image_name_3 = rand(0, 1000000) . "_" . $product_image_3;
                                move_uploaded_file($tmp_name_3, "../images/product_images/" . $new_image_name_3);
                                $images_query_3 = "INSERT INTO product_images (P_id, P_img) VALUES ('$id', '$new_image_name_3')";
                                mysqli_query($conn, $images_query_3);
                            } else {
                                echo '<script>alert("هذة الصورة غير مسموح بها");</script>';
                            }
                        }
                    }
                    
                    echo '<script> alert("تم تعديل المنتج بنجاح"); window.location.href = "products.php";</script>';
                    exit();
                } catch (mysqli_sql_exception $e) {
                    if ($e->getCode() == 1062) {
                        echo '<script>alert(" المنتج موجود مسبقًا");</script>';
                    } else {
                        echo '<script>alert(" حدث خطأ أثناء حفظ البيانات يرجى التحقق من صحة البيانات المدخلة");</script>';
                    }
                }
            } elseif (!empty($product_name) && !empty($old_price) && $discount>=0 && !empty($unit) && !empty($section)) {
                try {
                    $query = "UPDATE products SET 
                        P_name = '$product_name',
                        p_description = '$product_descrption',
                        P_old_price = '$old_price',
                        P_new_price = '$new_price',
                        P_quantity = '$product_quantity',
                        discount = '$discount',
                        p_unit_id = '$unit',
                        p_weight = '$product_weight',
                        Section_id = '$section'
                    WHERE 
                        products.P_id = '$id'";

                    $result = mysqli_query($conn, $query);
                    if ($result) {
                        echo '<script> alert("تم تعديل المنتج بنجاح"); window.location.href = "products.php";</script>';
                        exit();
                    } else {
                        throw new mysqli_sql_exception(mysqli_error($conn));
                    }
                } catch (mysqli_sql_exception $e) {
                    if ($e->getCode() == 1062) {
                        echo '<script>alert(" المنتج موجود مسبقًا");</script>';
                    } else {
                        echo '<script>alert(" حدث خطأ أثناء حفظ البيانات يرجى التحقق من صحة البيانات المدخلة");</script>';
                    }
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../images/home_image/logo4.png">
    <title>تعديل منتج</title>
</head>
<body>
    <div class="content">
        <h1>تعديل منتج</h1>
        <form action="#" method="post" enctype="multipart/form-data">
            <label for="product-name">اسم المنتج:</label>
            <input type="text" id="product-name" name="product-name" value="<?php echo $row_1['P_name']; ?>" required>

            <div class="images_input">
                <div>
                    <label for="product-image">صورة المنتج :</label>
                    <input type="file" id="product-image" name="product-image_1">
                </div>
                <div>
                    <label for="product-image">صورة المنتج:</label>
                    <input type="file" id="product-image" name="product-image_2">
                </div>
                <div>
                    <label for="product-image">صورة المنتج:</label>
                    <input type="file" id="product-image" name="product-image_3">
                </div>
            </div>

            <label for="p_descrption"> وصف المنتج</label>
            <input type="text" id="p_descrption" name="p_descrption" value="<?php echo $row_1['p_description']; ?>" required>

            <label for="old-price">السعر القديم:</label>
            <input type="number" id="old-price" name="old-price" value="<?php echo $row_1['P_old_price']; ?>" required>

            <label for="p_quantity"> كمية المنتج في المخزن:</label>
            <input type="number" id="P_quantity" name="P_quantity" value="<?php echo $row_1['P_quantity']; ?>" required>

            <label for="discount">نسبة التخفيض:</label>
            <input type="number" id="discount" name="discount" value="<?php echo $row_1['discount']; ?>" required>

            <label for="unit">الوحدة:</label>
            <select id="unit" name="unit">
                <?php
                $query = "SELECT * FROM product_unit";
                $result = mysqli_query($conn, $query);
                while ($row_2 = mysqli_fetch_assoc($result)) {
                    $selected = ($row_2['product_unit'] == $row_1['product_unit']) ? 'selected' : '';
                    echo "<option value='" . $row_2['p_unit_id'] . "' $selected>" . $row_2['product_unit'] . "</option>";
                }
                ?>
            </select>

            <label for="p_weight"> وزن المنتج</label>
            <input type="text" id="p_weight" name="p_weight" value="<?php echo $row_1['p_weight']; ?>" required>

            <label for="section">القسم:</label>
            <select id="section" name="Pr_section">
                <?php
                $query = "SELECT * FROM section";
                $result = mysqli_query($conn, $query);
                while ($row_3 = mysqli_fetch_assoc($result)) {
                    $selected = ($row_3['Section_name'] == $row_1['Section_name']) ? 'selected' : '';
                    echo "<option value='" . $row_3['Section_id'] . "' $selected>" . $row_3['Section_name'] . "</option>";
                }
                mysqli_close($conn);
                ?>
            </select>
            <button type="submit" name="update-product-button">تعديل المنتج</button>
        </form>
    </div>
</body>
</html>