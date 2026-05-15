
<?php
require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقواعد البيانات
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type = "image/png"href="../images/home_image/logo4.png"><!--رابط ايقونت الموقع-->

    <title>صفحة الطلبات المؤقتة</title>

    <!--==================(كود التأكيد من  رفض الطلب)==================-->
    <script>
        function confirm_reject_order(order_id) {
            let reject_order = confirm("هل متأكد من رفض هذا الطلب ؟");
            if (reject_order == true) {
                //انشاء رابط يرجع الى صفحة رفض الطلب ونشاء متغير في ارابط ثم اسناد رقم الطلب الى المتغير الذي في الرابط
                window.location.href = "reject_order.php?____reject_order___=" + order_id;
            }
        }
    </script>
    <!--==================================================================-->  

    <link rel="stylesheet" href="styles.css">
<style>
    body {
        font-family: 'Tajawal', Arial, sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
        direction: rtl;
    }

    .container {
        width: 90%;
        margin: 20px auto;
        background-color: #fff;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    h1 {
        text-align: center;
        color: #4CAF50;
        font-size: 28px;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        padding: 15px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #4CAF50;
        color: white;
        font-weight: bold;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    img {
        width: 60px;
        height: 60px;
        border-radius: 5px;
        object-fit: cover;
    }

    tfoot td {
        font-weight: bold;
        background-color: #f8f9fa;
    }

    .actions {
        text-align: center;
        padding: 20px 0;
    }

    button {
        padding: 12px 24px;
        margin: 5px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    button.confirm {
        background-color: #4CAF50;
        color: white;
    }

    button.reject {
        background-color: #f44336;
        color: white;
    }

    button.confirm:hover {
        background-color: #45a049;
    }

    button.reject:hover {
        background-color: #e53935;
    }

    /* تنسيق خاص بجدول معلومات الطلب */
    table.order-info {
        margin-bottom: 30px;
    }

    /* تنسيق خاص بجدول المنتجات */
    table.products {
        margin-bottom: 20px;
    }

    /* تحسين الظلال والتأثيرات */
    .container {
        transition: box-shadow 0.3s ease;
    }

    .container:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    /* تحسين الخطوط */
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap');    
</style>

</head>
<body>
    <?php
    $SELECT_1 = ("SELECT * from pending_orders");
    $result_SELECT_1 = mysqli_query($conn, $SELECT_1);
    if ($result_SELECT_1 && mysqli_num_rows($result_SELECT_1) > 0) {
        while ($row_1 = mysqli_fetch_assoc($result_SELECT_1)) {
    ?>
    <div class="container">
        <h1> رقم الطلب: <span><?php echo $row_1['Or_id']; ?></span></h1>

        <!-------------------جدول معلومات الطلب ------------------------->
        <table class="order-info">
            <thead>
                <tr>
                    <th>اسم الطالب</th>
                    <th>رقم الهاتف</th>
                    <th>محافظة التوصيل</th>
                    <th>المديرية</th>
                    <th>الشارع</th>
                    <th>الحي</th>
                    <th>رقم المنزل</th>
                    <th>رقم الشقة</th>
                    <th>طريقة الدفع</th>
                    <th>تاريخ الطلب</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $SELECT_2 = ("SELECT * from pending_orders_address , type_payment where pending_orders_address.OD_id = '$row_1[OD_id]' and pending_orders_address.Type_payment_id = type_payment.Type_payment_id");
                $result_SELECT_2 = mysqli_query($conn, $SELECT_2);
                if ($result_SELECT_2) {
                    $row_2 = mysqli_fetch_assoc($result_SELECT_2);
                ?>
                <tr>
                    <td><?php echo $row_2['Client_name']; ?></td>
                    <td><?php echo $row_2['Client_phone']; ?></td>
                    <td><?php echo $row_2['Client_state']; ?></td>
                    <td><?php echo $row_2['Client_city']; ?></td>
                    <td><?php echo $row_2['Client_street']; ?></td>
                    <td><?php echo $row_2['Client_neb']; ?></td>
                    <td><?php echo $row_2['Billing_id']; ?></td>
                    <td><?php echo $row_2['Dep_id']; ?></td>
                    <td><?php echo $row_2['Type_payment']; ?></td>
                    <td><?php echo $row_1['Or_date']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!------------------------------ جدول المنتجات ----------------------------->
        <table class="products">
            <thead>
                <tr>
                    <th>اسم المنتج</th>
                    <th>صورة المنتج</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                </tr>
            </thead>
            <?php
            $SELECT_3 = ("SELECT * from pending_order_items , products where pending_order_items.P_id = products.P_id and pending_order_items.or_id = '$row_1[Or_id]'");
            $result_SELECT_3 = mysqli_query($conn, $SELECT_3);
            if ($result_SELECT_3) {
                while ($row_3 = mysqli_fetch_assoc($result_SELECT_3)) {
            ?>
            <tr>
                <td><?php echo $row_3['P_name']; ?></td>
                <td><img src="<?php echo "../images/product_images/" . $row_3['P_img']; ?>" alt=""></td>
                <td><?php echo $row_3['quantity'] ?></td>
                <td><?php echo $row_3['price'] * $row_3['quantity'] ?></td>
            </tr>
            <?php }
            } ?>
            <tfoot>
                <tr>
                    <td colspan="4">الإجمالي : <span> <?php echo $row_1['Total_price']; ?></span> ريال</td>
                </tr>
            </tfoot>
        </table>

        <!-- أزرار التأكيد والرفض -->
        <div class="actions">
            <form method="POST" action="">
                <input type="hidden" name="order_id" value="<?php echo $row_1['Or_id']; ?>">
                <button class="confirm" name="confirm_order"><a href="confirm_order.php?____confirm_order___=<?php echo $row_1['Or_id']; ?>">تأكيد الطلب</a></button>
                <button class="reject_order" name="reject_order" onclick="confirm_reject_order(<?php echo $row_1['Or_id']; ?>)">رفض الطلب</button>
            </form>
        </div>
    </div>
    <?php
        }
    }else{
       echo "<h1> لا توجد هناك طلبات حاليا </h1>";
    }
    ?>
</body>
</html>