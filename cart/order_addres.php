<?php
session_start(); // بدء الجلسة للتحقق من بيانات المستخدم وتخزين المعلومات عبر الصفحات
require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقاعدة البيانات لإجراء العمليات اللازمة

// التحقق من وجود سلة التسوق، إذا كانت فارغة سيتم إعادة التوجيه إلى صفحة السلة
if (empty($_SESSION['cart'])) {
    header("Location: cart.php"); // إعادة توجيه المستخدم إلى صفحة سلة التسوق إذا كانت فارغة
    exit(); // إنهاء الكود في هذه المرحلة
}

// التحقق مما إذا كان المستخدم قد قام بإرسال النموذج
if (isset($_POST['submit_address'])) {
    // تنظيف البيانات المدخلة من قبل المستخدم لتجنب الهجمات عبر الحقن
    @$client_name =  htmlspecialchars($_POST['client-name']) ; // اسم العميل
    @$phone_number =  htmlspecialchars($_POST['client-number']); // رقم هاتف العميل
    @$governorate =  htmlspecialchars($_POST['governorate']); // المحافظة
    @$client_city = htmlspecialchars($_POST['client-city']) ; // المدينة
    @$client_street =  htmlspecialchars($_POST['client-street']); // الشارع
    @$client_nebrheed =  htmlspecialchars($_POST['client-nebrheed']); // الحي
    @$client_build_number =  intval($_POST['client-build-number']); //رقم المبناء
    //دالة intval() في PHP تُستخدم لتحويل قيمة إلى عدد صحيح (integer)
    @$client_department_number =  intval($_POST['client-department-numbe']); // رقم الشقة
    @$pay_money =  intval($_POST['pay-money']); // المبلغ المدفوع

    // التحقق من أن جميع الحقول قد تم ملؤها
    if(!empty($client_name) && !empty($phone_number) && !empty($governorate) && !empty($client_city) && !empty($client_street) && !empty($client_nebrheed) && !empty($client_build_number) && !empty($pay_money)){
        
        // التحقق من وجود المستخدم في قاعدة البيانات باستخدام رقم الهاتف
        $check_query = "SELECT * FROM users WHERE User_phone = '$phone_number'";
        $check_result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($check_result) > 0) { // إذا تم العثور على المستخدم

            // إضافة العنوان إلى جدول الطلبات المعلقة (pending_orders_address)
            $query = "INSERT INTO pending_orders_address (Client_name, Client_phone, Client_state, Client_city, Client_street, Client_neb, Billing_id, Dep_id, Type_payment_id) 
                    VALUES ('$client_name', '$phone_number', '$governorate', '$client_city', '$client_street', '$client_nebrheed', '$client_build_number', '$client_department_number', '$pay_money')";

            if (mysqli_query($conn, $query)) { // إذا تم إضافة العنوان بنجاح
                $OD_id = mysqli_insert_id($conn); // الحصول على معرف العنوان المضاف

                // حساب السعر الكلي للطلبية
                $total_price = 0;
                foreach ($_SESSION['cart'] as $product_id => $quantity) {
                    // استعلام للحصول على السعر الجديد للمنتج من جدول المنتجات
                    $query = "SELECT P_new_price FROM products WHERE P_id = '$product_id'";
                    $result = mysqli_query($conn, $query);
                    if ($result && mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        $price = $row['P_new_price']; // تخزين السعر
                        $total_price += $price * $quantity; // حساب المجموع
                    }
                    mysqli_free_result($result); // تحرير الذاكرة بعد الاستعلام
                }

                // إضافة الطلب إلى جدول الطلبات المعلقة (pending_orders)
                $query = "INSERT INTO pending_orders (OD_id, total_price) 
                        VALUES ('$OD_id', '$total_price')";

                if (mysqli_query($conn, $query)) { // إذا تم إضافة الطلب بنجاح
                    $order_id = mysqli_insert_id($conn); // الحصول على معرف الطلب

                    // إضافة عناصر الطلب إلى جدول pending_order_items
                    foreach ($_SESSION['cart'] as $product_id => $quantity) {
                        $query = "SELECT P_new_price FROM products WHERE P_id = '$product_id'";
                        $result = mysqli_query($conn, $query);
                        if ($result && mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                            $price = $row['P_new_price']; // تخزين سعر المنتج

                            // إضافة كل منتج إلى جدول pending_order_items
                            $query = "INSERT INTO pending_order_items (or_id, P_id, quantity, price) 
                                    VALUES ('$order_id', '$product_id', '$quantity', '$price')";
                            mysqli_query($conn, $query);
                        }
                        mysqli_free_result($result); // تحرير الذاكرة
                    }

                    // تفريغ السلة بعد إتمام الطلب
                    $_SESSION['cart'] = []; 
                    echo '<script>alert("تم ارسال طلبك"); window.location.href = "cart.php?success=1";</script>';
                    exit(); // إنهاء السكربت بعد إرسال الطلب
                } else {
                    echo '<script>alert("حدث خطأ أثناء إضافة الطلب.");</script>'; // إذا فشل إضافة الطلب
                }
            } else {
                echo '<script>alert("حدث خطأ أثناء إضافة العنوان.");</script>'; // إذا فشل إضافة العنوان
            }
        } else {
            echo '<script>alert("عفوا: انت لم تسجل برقم الهاتف هذا في موقعنا. يجب إنشاء حساب أولاً."); window.location.href = "signup.php";</script>';
            exit(); // إذا لم يتم العثور على المستخدم
        }
    } else {
        echo '<script>alert("يجب ملء كل الحقول");</script>'; // إذا كانت هناك حقول غير مملوءة
    }
}
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <link rel="icon" type = "image/png"href="../images/home_image/logo4.png"><!--رابط ايقونت الموقع-->

    <title>عنوان التوصيل</title>
</head>
<body onload="document.getElementById('submit-button').disabled = true;">
    <div class="content">
        <h1>عنوان توصيل الطلب</h1>
        <form action="#" method="post" name="Register" id="reg">
            <label for="client-name">اسم العميل:</label>
            <input type="text" id="client-name" name="client-name" maxlength="35" minlength="10"  title="يرجى ادخال الاسم بحيث لا يزيد عن 30 حرف" required >

            <label for="client-number">رقم الهاتف:</label>
            <input type="number" id="client-number" name="client-number" minlength="9" maxlength="14" pattern="[0-9+]{9,14}" title="يرجى إدخال رقم هاتف صحيح (من 9 إلى 14 رقمًا)" required>

            <label for="governorate">المحافظة:</label>
            <select id="governorate" name="governorate">
                <option value="صنعاء">صنعاء</option>
            </select>

            <label for="client-city">المديرية:</label>
            <select name="client-city" id="client-city">
                <option value="الأمانة">الأمانة</option>
            </select>

            <label for="client-street">الشارع:</label>
            <select name="client-street" id="client-street">
                <option value="">أختر الشارع</option>
                <option value="الزبيري">شارع الزبيري</option>
                <option value="الستين">شارع الستين</option>
                <option value="حدة">شارع حدة</option>
                <option value="خولان">شارع خولان</option>
                <option value="تعز">شارع تعز</option>
                <option value="القيادة">شارع القيادة</option>
                <option value="الجامعة">شارع الجامعة</option>
                <option value="الوحدة">شارع الوحدة</option>
                <option value="45 شارع">شارع 45</option>
                <option value="المطار">شارع المطار</option>
                <option value="الحصبة">شارع الحصبة</option>
                <option value="شملان">شارع شملان</option>
                <option value="المذبح">شارع المذبح</option>
                <option value="الجراف">شارع الجراف</option>
                <option value="صافر">شارع صافر</option>
                <option value="الزراعة">شارع الزراعة</option>
                <option value="سعوان">شارع سعوان</option>
                <option value="22 مايو">شارع 22 مايو</option>
                <option value="بغداد">شارع بغداد</option>
                <option value="التحرير">شارع التحرير</option>
            </select>

            <label for="client-nebrheed">الحي:</label>
            <input type="text" id="client-nebrheed" name="client-nebrheed" required>

            <label for="client-build-number">رقم المنزل : (إختياري)</label>
            <input type="number" id="client-build-number" name="client-build-number" value = "1">

            <label for="client-department-number">رقم الشقة : (إختياري)</label>
            <input type="number" id="client-department-numbe" name="client-department-numbe" value = "1">

            <label for="pay-money">طريقة الدفع:</label>
            <select id="pay-money" name="pay-money">
                <?php
                $query = "SELECT * from type_payment";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='" . $row['Type_payment_id'] . "'>" . $row['Type_payment'] . "</option>";
                }
                ?>
            </select>

            <label for="confirm-order">تأكيد الطلب:</label>
            <input type="checkbox" id="confirm-order" name="confirm-order" onclick="theChecker();">

            <button type="submit" name="submit_address" id="submit-button">إرسال الطلب</button>
        </form>
    </div>
    <script>
    // تعريف دالة theChecker
    function theChecker() {
        // الحصول على العنصر الذي يمثل مربع الاختيار (checkbox) باستخدام معرّف (ID) 'confirm-order'
        const confirmCheckbox = document.getElementById('confirm-order');
        
        // الحصول على العنصر الذي يمثل زر الإرسال (submit button) باستخدام معرّف (ID) 'submit-button'
        const submitButton = document.getElementById('submit-button');

        // التحقق مما إذا كان مربع الاختيار (checkbox) قد تم تحديده
        if (confirmCheckbox.checked) {
            // إذا كان مربع الاختيار مفعلاً (تم تحديده)، يتم تفعيل زر الإرسال
            submitButton.disabled = false;  // تفعيل الزر بحيث يصبح قابلاً للنقر
            submitButton.style.backgroundColor = "#00b31e"; // تغيير لون خلفية الزر إلى الأخضر
        } else {
            // إذا كان مربع الاختيار غير مفعّل (غير محدد)، يتم تعطيل زر الإرسال
            submitButton.disabled = true;  // تعطيل الزر بحيث لا يمكن النقر عليه
        }
    }
</script>

</body>
</html>