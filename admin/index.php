<?php
    require("../admin acount/admin_session.php"); // التحقق من صحة الجلسة
    require('../database/connect_to_database.php'); // استدعاء ملف الاتصال بقاعدة البيانات

    //لاستدعاء المنتجات الجدد وعرضها في رسالة عدد الطلبات الجدد
    $SELECT_1 = ("SELECT COUNT(*) as total from pending_orders");
    $result_SELECT_1 = mysqli_query($conn, $SELECT_1);
    $count_orders = mysqli_fetch_assoc($result_SELECT_1)['total'];

    //لاستدعاء عدد رسائل العملاء وطباعتها في رسالة
    $SELECT_2 = ("SELECT COUNT(*) as total_messages from User_comments");
    $result_SELECT_2 = mysqli_query($conn, $SELECT_2);
    $count_users_messages = mysqli_fetch_assoc($result_SELECT_2)['total_messages'];
    

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type = "image/png"href="../images/home_image/logo4.png"><!--رابط ايقونت الموقع-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>لوحة التحكم - السوق الإلكتروني</title>
    <style>
        :root {
            --primary-color: #218838;
            --secondary-color: #005713;
            --hover-color: #00731b;
            --accent-color: #28a745;
            --text-color: #333;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }
        
        body {
            font-family: 'Tajawal', Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            background-color: #f5f5f5;
            color: var(--text-color);
        }
        
        .sidebar {
            width: 280px;
            background-color: var(--primary-color);
            color: white;
            height: 100vh;
            padding: 20px 0;
            box-sizing: border-box;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            z-index: 100;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar h2 {
            margin: 0;
            padding: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .sidebar h2 i {
            margin-left: 10px;
        }
        
        .sidebar-menu {
            padding: 0 15px;
            overflow-y: auto;
            height: calc(100vh - 100px);
        }
        
        .sidebar a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            margin: 8px 0;
            padding: 12px 15px;
            border-radius: 5px;
            background-color: var(--secondary-color);
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .sidebar a:hover {
            background-color: var(--hover-color);
            transform: translateX(-5px);
        }
        
        .sidebar a i {
            margin-left: 10px;
            width: 20px;
            text-align: center;
        }
        
        .content {
            flex: 1;
            padding: 30px;
            box-sizing: border-box;
            margin-right: 280px;
            background-color: white;
            min-height: 100vh;
            border-radius: 10px 0 0 10px;
            box-shadow: -2px 0 10px rgba(0,0,0,0.05);
        }
        
        .badge {
            background-color: white;
            color: var(--dark-color);
            padding: 3px 8px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-right: auto;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .dashboard-title h1 {
            margin: 0;
            color: var(--primary-color);
            font-size: 1.8rem;
        }
        
        .dashboard-title p {
            margin: 5px 0 0;
            color: #666;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            text-align: center;
        }
        
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        
        .stat-card h3 {
            margin: 0 0 5px;
            color: var(--dark-color);
            font-size: 1.2rem;
        }
        
        .stat-card p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .stat-card .value {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 10px 0;
        }
        
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .report-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            transition: all 0.3s ease;
            cursor: pointer;
            text-align: center;
        }
        
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }
        
        .report-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        
        .report-card h3 {
            margin: 0 0 10px;
            color: var(--dark-color);
        }
        
        .report-card p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .card-sales {
            border-top: 3px solid var(--accent-color);
        }
        
        .card-daily {
            border-top: 3px solid #20c997;
        }
        
        .card-inventory {
            border-top: 3px solid var(--info-color);
        }
        
        .card-pending {
            border-top: 3px solid var(--warning-color);
        }
        
        .card-idle {
            border-top: 3px solid var(--danger-color);
        }
        
        .card-regions {
            border-top: 3px solid #6f42c1;
        }
        
        .card-customers {
            border-top: 3px solid #fd7e14;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .content {
                margin-right: 0;
            }
            
            .stats-cards,
            .reports-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-cogs"></i> لوحة التحكم</h2>
        </div>
        <div class="sidebar-menu">
            <a href="../index.php" target="_blank"><i class="fas fa-home"></i> الصفحة الرئيسية</a>
            <a href="add_section.php" ><i class="fas fa-folder-plus"></i> إضافة قسم</a>
            <a href="add_product.php" ><i class="fas fa-plus-circle"></i> إضافة منتج</a>
            <a href="products.php" ><i class="fas fa-boxes"></i> المنتجات</a>
            <?php if($count_orders > 0) {   //لطباعة عدد الطلبات?>
                 <a href="orders.php" ><i class="fas fa-shopping-cart"></i> طلبات الزبائن <span class="badge"><?php echo $count_orders ?></span></a>
            <?php }else{?>
                <a href="orders.php" ><i class="fas fa-shopping-cart"></i> طلبات الزبائن</a>
            <?php }?>
            <a href="messages.php" ><i class="fas fa-envelope"></i> رسائل العملاء <span class="badge"><?php echo $count_users_messages ?></span></a>
            <a href="../admin acount/signup.php" ><i class="fas fa-user-plus"></i> إنشاء حساب موظف</a>
            <a href="../admin acount/logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
        </div>
    </div>
    <div class="content">
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>مرحبًا بك في لوحة التحكم</h1>
                <p>يمكنك إدارة المتجر وعرض التقارير من هنا</p>
            </div>
        </div>
        
        <div class="reports-grid">
            <div class="report-card card-sales" onclick="window.location.href='../reports/get_more_products_sales.php'">
                <i class="fas fa-chart-line"></i>
                <h3>تقرير المنتجات الأكثر مبيعاً</h3>
                <p>عرض تقرير بالمنتجات الأكثر مبيعاً خلال فترة محددة</p>
            </div>
            
            <div class="report-card card-daily" onclick="window.location.href='../reports/get_daily_sales.php'">
                <i class="fas fa-calendar-check"></i>
                <h3>تقرير المبيعات اليومية</h3>
                <p>عرض تفصيلي للمبيعات اليومية ومقارنتها مع الأيام السابقة</p>
            </div>
            
            <div class="report-card card-inventory" onclick="window.location.href='../reports/get_inventory_stats.php'">
                <i class="fas fa-box-open"></i>
                <h3>تقرير المخزون والمنتجات</h3>
                <p>عرض تقرير شامل بحالة المخزون والمنتجات المتاحة</p>
            </div>
            
            <div class="report-card card-pending" onclick="window.location.href='../reports/pending_orders_report.php'">
                <i class="fas fa-clock"></i>
                <h3>تقرير الطلبات المعلقة</h3>
                <p>عرض جميع الطلبات المعلقة التي تحتاج إلى معالجة</p>
            </div>
            
            <div class="report-card card-idle" onclick="window.location.href='../reports/Slow_Moving_Items.php'">
                <i class="fas fa-stopwatch"></i>
                <h3>تقرير المنتجات الراكدة</h3>
                <p>عرض المنتجات التي لم يتم بيعها منذ فترة طويلة</p>
            </div>
            
            <div class="report-card card-regions" onclick="window.location.href='../reports/top_regions_report.php'">
                <i class="fas fa-map-marked-alt"></i>
                <h3>تقرير المناطق الأكثر شراء</h3>
                <p>عرض المناطق الجغرافية الأكثر طلباً للمنتجات</p>
            </div>
            
            <div class="report-card card-customers" onclick="window.location.href='../reports/customers_report.php'">
                <i class="fas fa-users"></i>
                <h3>تقرير العملاء الأكثر شراء</h3>
                <p>عرض قائمة بأفضل العملاء من حيث عدد المشتريات</p>
            </div>
        </div>
    </div>
</body>
</html>