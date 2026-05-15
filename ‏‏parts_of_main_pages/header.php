<!--- ملف header.php -->

<?php
ob_start();//
require("users acount/user_session.php"); // التحقق من صحة الجلسة
require('database/connect_to_database.php'); // الاتصال بقاعدة البيانات
// حساب عدد المنتجات في السلة
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
// إذا كانت السلة موجودة في الجلسة، نحسب عدد المنتجات فيها، وإلا نعطي القيمة 0
?>
<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصفحة الرئيسية</title>
    <link rel="stylesheet" href="styles.css">

    <!---ارابط استدعاء الخط -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

    <!---ارابط استدعاء مكاتب -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!---ارابط استدعاء الايقونات -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">

    <!----------(روابط استدعاء  مكتبة (swiper)-------------->
   <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <link rel="icon" type = "image/png"href="images/home_image/logo4.png"><!--رابط ايقونت الموقع-->
</head>

<body>
  
        <!--=======================(headr)==============================-->
        <header>
            <section class="header_seaction">
                <!--=======================(frist headr start)====================-->
                <div class="search_filed_and_icons">
                    <div class="menue_button">
                        <i class='bx bx-menu'></i>
                    </div>
                    <!-- قسم الشعار: يحتوي على اسم وشعار الموقع -->
                    <a href="./index.php">
                        <div class="logo">
                           <h2><span>سلة </span>الخير</h2>
                            <img src="images/home_image/logo4.png" alt="شعار سلة الخير">
                        </div>
                    </a>
                    <div class="search_icon">
                        <i class='bx bx-search-alt'></i>                
                    </div>
                    <!-- قسم البحث: يحتوي على حقل البحث وأيقونة البحث -->
                    <div class="search_filed">
                        <form action="search.php" method="get">
                            <!-- أيقونة البحث -->
                            <a href=""><i class='bx bx-search-alt'></i></a>
                            <!-- حقل إدخال النص للبحث -->
                            <input type="text" class="search_input" name="search_input" placeholder="ابحث الان في سوبر ماركة سلة الخير">
                            <!-- زر إرسال البحث -->
                            <button type="submit" class="button_search" name = "button_search">بـحـث</button>
                        </form>
                    </div>
        
                    <!-- قسم الأيقونات: يتضمن أيقونات المستخدم، السلة، والمفضلة -->
                    <div class="user_and_cart_heart_icons">
                        <!-- أيقونة المستخدم -->
                        <a href="users acount/login.php"><i class='bx bxs-user-circle'></i></a>
                        <!-- أيقونة السلة مع عداد المنتجات -->
                        <a href="./cart/cart.php" target="_blank">
                            <span><?php echo $cart_count; ?></span><!--لاظهار عداد المنتجات الذي تم اضافتها في السلة-->
                        <i class='bx bxs-cart'></i></a>
                        <!-- أيقونة المفضلة مع عداد العناصر -->
                        <a href=""><span>0</span><i class='bx bx-heart'></i></a>
                    </div>
        
                    <!-- قسم الوضع الليلي/النهاري -->
                    <div class="light_and_dark">
                        <a href=""><i class='bx bxs-sun'></i></a>
                    </div>
                </div>
                <!--=======================(frist headr end)=====================-->
        
                <!--=======================(second headr start)====================-->
                <div class="contents">
                    <!-- روابط التنقل الرئيسية -->
                    <a href="index.php"><i class='bx bxs-home'></i>الرئيسية</a>
        
                    <!-- القائمة المنسدلة للأقسام -->
                    <select name="sections" id="sections_id" onchange="location = this.value;">
                        <option>الاقسام</option>
                        <?php
                            $query = ("SELECT * from section");
                            $result = mysqli_query($conn , $query);
                            while ($row = mysqli_fetch_assoc($result)){
                            echo "<option value='products.php?s_name=".$row['Section_name']."'>" . $row['Section_name'] . "</option>";
                        }
                        ?>
                    </select>  

                    <!-- روابط إضافية -->
                    <a href="#section_Offers_id"><i class='bx bxs-gift'></i>افضل العروض</a>
                    <!--<a href=""><i class='bx bx-shopping-bag'></i> المنتجات الحديثة</a>-->
                    <a href="#more_payment_id"><i class='bx bx-trending-up'></i> المنتجات الاكثر مبيعا</a>
                    <a href="#footer"><i class='bx bxs-phone-call'></i>تواصل معنا</a>
                </div>
                <!--=======================(second headr end)=====================-->
            </section>
        </header>