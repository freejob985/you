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

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
            padding-top: 80px; /* إضافة مساحة للهيدر الثابت */
            padding-bottom: 60px; /* تعديل المساحة للفوتر الثابت */
        }
        /* الأنماط السابقة */
        
        /* أنماط الهيدر الثابت */
        .fixed-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 15px 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .fixed-header h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        .course-info {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .info-item {
            text-align: center;
        }
        .info-item i {
            font-size: 1.2rem;
            margin-right: 5px;
        }
        
        /* أنماط الفوتر الثابت */
        .fixed-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #2c3e50;
            color: white;
            padding: 10px 0;
            z-index: 1000;
        }
        .footer-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: nowrap;
            overflow-x: auto;
            white-space: nowrap;
        }
        .footer-link {
            color: white;
            text-decoration: none;
            font-size: 0.8rem;
            transition: color 0.3s ease;
            padding: 5px 10px;
        }
        .footer-link:hover {
            color: #3498db;
        }
        .footer-link i {
            margin-right: 5px;
        }
    </style>
</head>