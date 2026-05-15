<?php

require('database/connect_to_database.php');//استدعاء ملف الاتصال بقواعد البيانات 
     
    //يتم وضع نقطتين في بداية المساراذا كان الملف في مجلد اخر فقط
?>

 <!--=======================(headrاستدعاء صفحة )==============================-->
<?php
    include('‏‏parts_of_main_pages/header.php');
?>
 
<!--=======================(home  استدعاء صفحة )========================-->
<?php
    include('‏‏parts_of_main_pages/home.php');
?>

<!--=======================(section  استدعاء صفحة )========================-->

<?php
    include('‏‏parts_of_main_pages/section.php');
?> 

<!--=======================(discount  استدعاء صفحة )========================-->
<?php
    include('‏‏parts_of_main_pages/discount.php');
?> 

<!--=======================(more_payment  استدعاء صفحة )========================-->
<?php
    include('‏‏parts_of_main_pages/more_payment.php');
?> 

<!--=======================(footer  استدعاء صفحة )========================-->
<?php
    include('‏‏parts_of_main_pages/footer.php');
?>