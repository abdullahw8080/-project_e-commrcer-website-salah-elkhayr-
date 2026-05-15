

<!--=================كود عملية ارسال تعليق==================-->
<?php
  //preg_replace('/\s+/', ' ') يحذف المسافات الاكبر من واحد من الوسط
        //trim()يحذف المسافة من البداية ونهاية
    @$input_user_email = preg_replace('/\s+/', ' ', trim($_POST['email'])); // إزالة المسافات الزائدة والمسافات من البداية والنهاية
    @$comments_input = preg_replace('/\s+/', ' ', trim(strip_tags($_POST['comment']))); // إزالة المسافات الزائدة مع حذف الأكواد والمسافات من البداية والنهاية
    @$send_comment = $_POST['send'];//زر الارسال

    if(isset($send_comment)){//اتاكد اذا تم الضغط على الزر
       if(!empty($input_user_email) && !empty($comments_input )){//اتاكد ما ذا كان احد الحقول فارغ
        //كود يقوم بلاستعلام والتحقق اذا كان المستخدم مسجل في جدول المستخدمين
        $check_email_and_name =("SELECT * from users where User_email ='$input_user_email' ");
        $result = mysqli_query ($conn , $check_email_and_name);//تنفيذ الاستعلام
        if ($result && mysqli_num_rows($result) > 0) { // كود يتحقق إذا كان الاستعلام صحيحًا
            //كود يتحقق اذا كان الاستعلام صحيح 
                $row = mysqli_fetch_assoc($result);//كود يقوم باخذ قيمة الاستعلام
                $query = ("INSERT into user_comments (user_id , comment) values ('{$row['User_id']}' ,' $comments_input')");//ادخال التعليق ورقم المستخدم الذي في جدول المستخدمين
                $result_2 = mysqli_query($conn , $query);//يقوم بنتفيذ الادخال
                    if($result_2){
                        echo '<script> alert("تم ارسال تعليقك");</script>';//اظهار رسالة
                    }else {
                        echo '<script> alert ("حدث خطا في عملية الارسال");</script>';//اظهار رسالة
                    }
            }else{
                echo '<script> alert("عفوا هذا الحساب غير مسجل في موقعنا يجب اتحقق من صحة الحساب او قم بإنشا حساب جديد"); window.location.href = "users acount/signup.php";</script>';
            // إعادة التوجيه إلى الصفحة تسجيل الدخول
             exit();//خروج
         }
     }else{   
        echo '<script> alert("يجب ملء جميع الحقول")</script>';         
    }  
}
mysqli_close($conn); // إغلاق الاتصال بقاعدة البيانات

?>
<!--=======================( footer )========================-->
<footer id="footer">
            <!-- أيقونة واتساب -->
            <div class="whatsapp">
                <a href="https://wa.me/qr/O7TJJCCZBPGXD1"><i class='bx bxl-whatsapp'></i></a>
            </div>
            <!-- زر الانتقال إلى الأعلى -->
            <div class="arrow_up">
                <a href="#"><i class='bx bx-chevrons-up'></i></a>
            </div>
            <!-- قسم المعلومات الرئيسية -->
            <div class="content_info">
                <div class="frist_info">
                    <!-- شعار الموقع -->
                    <div class="logo">
                        <h2><span>سلة </span>الخير</h2>
                        <img src="images/home_image/logo4.png" alt="شعار الموقع">
                    </div>
                    <!-- وصف الموقع -->
                    <div class="descraip_our_web">
                        <p>متجر سلة الخير هو أول بقالة إلكترونية
                            للمواد الغذائية والأدوات المنزلية في اليمن حيث تضم مئات المنتجات
                            الغذائية والأدوات المنزلية التي تلبي احتياجك لتحصل عليها بسهولة
                            وبتوصيل إلى باب بيتك.
                        </p>
                    </div>
                </div>
                <!-- روابط الحساب -->
                <div class="your_count">
                    <h2>الحساب</h2>
                    <a href="#">إعدادات الحساب</a>
                    <a href="#">سلة التسوق</a>
                    <a href="#">طلباتي</a>
                </div>
        
                <!-- خدمات الدفع -->
                <div class="services_pay">
                    <h2>خدمة الدفع</h2>
                    <div class="services_pay_logoes">
                        <img src="images/serves_pay_images/cash.jpg" alt="تطبيق كاش">
                        <img src="images/serves_pay_images/jwaly.jpg" alt="تطبيق جوالي">
                        <img src="images/serves_pay_images/kramey.jpg" alt="تطبيق الكريمي">
                        <img src="images/serves_pay_images/one_cash.jpg" alt="تطبيق ون كاش">
                        <img src="images/serves_pay_images/hande_cash.jpg" alt="دفع كاش">
                    </div>
                </div>
        
                <!-- نموذج إرسال التعليقات -->
                <div class="send_comments">
                    <form action="" method="post">
                        <input type="email" name="email" id="email_input" placeholder="ادخل البريد الإلكتروني" required>
                        <textarea name="comment" id="comments_input" placeholder=" أكتب تعليقك هنا . يجب الايتعداء 100  حرف." maxlength="150" required></textarea>
                        <button type="submit" class="send_comments_button" id="send_comments_button" name="send">إرسال</button>
                    </form>
                </div>
            </div>
            <!-- روابط التطبيقات الاجتماعية -->
            <div class="connects_app">
                <a href="#"><i class='bx bxl-facebook'></i></a>
                <a href="#"><i class='bx bxl-snapchat'></i></a>
                <a href="#"><i class='bx bx-envelope'></i></a>
                <a href="#"><i class='bx bxl-whatsapp'></i></a>
                <a href="#"><i class='bx bxs-location-plus'></i></a>
            </div>
            <!-- نص حقوق الملكية -->
            <div class="text_owner">
                <h5>© جميع حقوق الملكية محفوظة لدى متجر سلة الخير 2024</h5>
            </div>
        </footer>

        <div class="botton_header">
            <!--أيقونة الرئيسية  -->
            <a href="#main">الرئيسية<br><i class='bx bxs-home'></i></a>
            <!--أيقونة الاقسام  -->
            <a href="#section_itms">الاقسام<br><i class='bx bxs-grid-alt'></i></a>
             <!--أيقونة العروض  -->
            <a href="#section_Offers_id">العروض<br><i class='bx bxs-gift'></i></a>
             <!-- أيقونة السلة مع عداد المنتجات -->
             <?php 
                if($cart_count > 0){
                    //<!--لاظهار عداد المنتجات الذي تم اضافتها في السلة-->
                    // (header)في ال (cart_count)تم تعريف المتغير
                    echo '<a href="cart/cart.php">السلة<span>' . $cart_count . '</span><br><i class="bx bxs-cart"></i></a>';
                } else {
                    echo '<a href="cart/cart.php">السلة<i class="bx bxs-cart"></i></a>';
                }
                ?>
             <!-- أيقونة المستخدم -->
             <a href="users acount/signup.php "target="_blank">المستخدم<br><i class='bx bxs-user-circle'></i></a>

        </div>
    </div><!--(content end)-->


        <script src="myscript.js"></script>
</body>

</html>
<?php ob_end_flush(); ?>