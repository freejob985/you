<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نموذج إضافة كورسات</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- روابط البوتستراب -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- رابط ماتريال ديزاين -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    
    <!-- رابط تيلويند -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- رابط الفونت أوسم -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    
    <!-- الخط المطلوب -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Changa:wght@200..800&display=swap" rel="stylesheet">
    
    <!-- مكتبة توست -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- مكتبة SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- مكتبة Tagify -->
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-image: url('https://r4.wallpaperflare.com/wallpaper/504/416/967/youtube-geek-science-wallpaper-7816bd8810304c38c0dce17e6862d4ca.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.9);
        }
        .modal-content {
            background-color: rgba(255, 255, 255, 0.95);
        }
        .w-48 {
            width: 48%;
        }
        .container {
            max-width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }
    </style>
<style>
.tagify {
    width: 100%;
    max-width: 100%;
}
.tagify__tag {
    max-width: calc(100% - 10px);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
</head>