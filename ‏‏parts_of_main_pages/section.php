   <!--=======================(header استدعاء صفحة)==============================-->
<?php
    require('database/connect_to_database.php'); // استدعاء ملف الاتصال بقواعد البيانات 

?>

   <!--=======================( قسم الاقسام)========================-->
   <section class="section_itms" id="section_itms">
            <!-- قسم العناصر والعنوان -->
            <div class="items_and_text_headr">
                <h2>تسوق حسب القسم الذي تريد</h2>
                <!-- قسم الشعارات -->
                <div class="section_logos">
                    <?php
                        $query = ("SELECT * from section");
                        $result = mysqli_query($conn, $query);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)){
                    ?>
                    <!-- عنصر فردي يمثل قسماً معيناً -->
                    <a href="products.php?s_name=<?php echo $row['Section_name'];?>">
                        <div class="item">
                            <img src="<?php echo "images/‏‏section_images/".$row['section_image'];?>" alt="">
                            <h3><?php echo $row['Section_name'];?></h3>
                        </div>
                    </a>

                    <?php 
                        }
                    }
                ?>
                </div>
            </div>
        </section>