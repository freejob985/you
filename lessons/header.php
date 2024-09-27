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
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />

    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

<!-- <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script> -->

    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }
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
    </style>
</head>