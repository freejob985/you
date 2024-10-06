<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دروس الكورس: <?php echo htmlspecialchars($course['title']); ?></title>
    
    <!-- روابط البوتستراب -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- رابط ماتريال ديزاين -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    
    <!-- رابط الفونت أوسم -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    
    <!-- الخط المطلوب -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Changa:wght@200..800&display=swap" rel="stylesheet">
    
    <!-- مكتبة توست -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- مكتبة Tagify -->
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />

    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
            padding-top: 160px; /* Increased top padding for header */
            padding-bottom: 100px; /* Increased bottom padding for footer */
        }
        /* أنماط الهيدر */
        .course-header {
            background: linear-gradient(45deg, #007bff, #6610f2);
            color: white;
            padding: 20px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }
        .course-header h1 {
            margin-bottom: 10px;
            font-weight: bold;
        }
        .course-stats {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin-bottom: 15px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-item .value {
            font-size: 1.5em;
            font-weight: bold;
        }
        .header-progress {
            height: 20px;
            margin-top: 10px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            overflow: hidden;
        }
        .header-progress .progress-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            font-weight: bold;
            transition: width 0.5s ease;
        }
        .header-progress .progress-bar-remaining {
            background-color: rgba(255, 255, 255, 0.5);
            color: #333;
        }
        /* أنماط إضافية للصفحة */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 15px;
            transition: background-color 0.3s ease;
        }
        .completed-card .card-header {
            background-color: #000000 !important;
        }
        .lesson-title {
            font-weight: bold;
            color: #FFFFFF;
        }
        .completed {
            text-decoration: line-through;
            color: #FFFFFF;
        }
        .card-body {
            padding: 20px;
        }
        .progress {
            height: 25px;
            border-radius: 15px;
        }
        .progress-bar {
            line-height: 25px;
        }
        .badge {
            font-size: 0.9em;
            padding: 5px 10px;
        }
        .btn-group .btn {
            margin-right: 5px;
        }
        .form-check-input {
            cursor: pointer;
        }
        .completed-row {
            background-color: #e8f5e9 !important;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
        .pagination .page-link {
            color: #007bff;
        }
        .grayscale {
            filter: grayscale(100%);
        }
        .pagination .page-link {
            width: 40px;
            height: 40px;
            text-align: center;
            line-height: 28px;
            font-weight: bold;
            border: 2px solid #007bff;
            color: #007bff;
            background-color: #fff;
            transition: all 0.3s ease;
        }
        .pagination .page-item.active .page-link,
        .pagination .page-link:hover {
            background-color: #007bff;
            color: #fff;
        }
        .pagination .page-item {
            margin: 0 5px;
        }
        .card.h-100.completed-card {
            background: #D4D4D4FF;
        }
        
        /* New styles for footer buttons */
        .footer-links .btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            margin: 5px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .footer-links .btn i {
            font-size: 1.5em;
            margin-bottom: 5px;
        }
        .footer-links .btn span {
            font-size: 0.8em;
            text-align: center;
            line-height: 1;
        }
    </style>
</head>
<body>
    <!-- هيدر الكورس -->
    <header class="course-header">
        <div class="container">
            <h1><?php echo htmlspecialchars($course['title']); ?></h1>
            <div class="course-stats">
                <div class="stat-item">
                    <div class="value"><?php echo $totalLessons; ?></div>
                    <div>إجمالي الدروس</div>
                </div>
                <div class="stat-item">
                    <div class="value"><?php echo $completedLessons; ?></div>
                    <div>الدروس المكتملة</div>
                </div>
                <div class="stat-item">
                    <div class="value"><?php echo $totalLessons - $completedLessons; ?></div>
                    <div>الدروس المتبقية</div>
                </div>
                <div class="stat-item">
                    <div class="value"><?php echo $completionPercentage; ?>%</div>
                    <div>نسبة الإكمال</div>
                </div>
            </div>
            <!-- تحديث شريط التقدم في الهيدر -->
            <div class="progress header-progress">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completionPercentage; ?>%;" aria-valuenow="<?php echo $completionPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                    <?php echo $completionPercentage; ?>% مكتمل
                </div>
                <div class="progress-bar progress-bar-remaining" role="progressbar" style="width: <?php echo 100 - $completionPercentage; ?>%;" aria-valuenow="<?php echo 100 - $completionPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                    <?php echo 100 - $completionPercentage; ?>% متبقي
                </div>
            </div>
        </div>
    </header>