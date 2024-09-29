<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Material Design Icons -->
<link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css" rel="stylesheet">

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

<!-- Google Fonts: Cairo and Changa -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Changa:wght@200..800&display=swap" rel="stylesheet">

<!-- Toast JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">

<!-- Highlight.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.1/styles/atom-one-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.1/highlight.min.js"></script>

<style>
    body {
        font-family: 'Cairo', sans-serif;
        transition: margin-right 0.3s ease-in-out;
    }
    pre code {
        direction: ltr;
        text-align: left;
        display: block;
    }
    .code-block {
        background-color: #282c34;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        position: relative;
    }
    .sidebar {
        position: fixed;
        top: 0;
        right: -400px;
        width: 400px;
        height: 100%;
        background-color: #fff;
        transition: right 0.3s ease-in-out;
        z-index: 1000;
        overflow-y: auto;
        box-shadow: -2px 0 5px rgba(0,0,0,0.1);
    }
    .sidebar.open {
        right: 0;
    }
    .sidebar-toggle {
        position: fixed;
        top: 10px;
        right: 10px;
        z-index: 1001;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #007bff;
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: right 0.3s ease-in-out;
    }
    .sidebar-toggle.open {
        right: 410px;
    }
    .comment-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        background-color: #fff;
        position: relative;
        display: flex;
        align-items: center;
    }
    .comment-card .delete-comment {
        position: absolute;
        top: 10px;
        left: 10px;
    }
    .comment-image {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 50%;
        margin-left: 16px;
    }
    .comment-content {
        flex-grow: 1;
        margin-right: 16px;
    }
    .comment-author {
        font-weight: bold;
        color: #2d3748;
    }
    .comment-text {
        color: #4a5568;
        margin-top: 8px;
    }
    .comment-date {
        color: #718096;
        font-size: 0.875rem;
        margin-top: 8px;
    }
    h1.text-3xl.font-bold.mb-4 {
        text-align: center;
    }
    /* تنعيم الاسكرول */
    html {
        scroll-behavior: smooth;
    }
    body, textarea, .tox-edit-area__iframe {
        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
    }
    body::-webkit-scrollbar, textarea::-webkit-scrollbar, .tox-edit-area__iframe::-webkit-scrollbar {
        width: 8px;
    }
    body::-webkit-scrollbar-track, textarea::-webkit-scrollbar-track, .tox-edit-area__iframe::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    body::-webkit-scrollbar-thumb, textarea::-webkit-scrollbar-thumb, .tox-edit-area__iframe::-webkit-scrollbar-thumb {
        background-color: #888;
        border-radius: 4px;
    }
    body::-webkit-scrollbar-thumb:hover, textarea::-webkit-scrollbar-thumb:hover, .tox-edit-area__iframe::-webkit-scrollbar-thumb:hover {
        background-color: #555;
    }
    /* إزاحة الصفحة عند فتح الشريط الجانبي */
    body.sidebar-open {
        margin-right: 400px;
    }
    /* تنسيق زر حذف ونسخ الكود */
    .code-block .delete-code, .code-block .copy-code {
        margin-top: 10px;
        margin-right: 5px;
    }
    .code-block .copy-code {
        margin-left: 5px;
    }
    /* اتجاه النص في قائمة التشغيل */
    #playlist li {
        direction: ltr;
        text-align: justify;
    }
    /* تنسيق الدرس المكتمل */
    .completed {
        text-decoration: line-through;
        font-weight: bold;
    }
    /* تكبير القائمة */
    #playlist {
        font-size: 1.1em;
    }
    #playlist .list-group-item {
        padding: 12px 20px;
    }
    /* تنسيق الإحصائيات */
    #playlistStatistics {
        margin-top: 20px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 5px;
    }
    #playlistStatistics p {
        margin-bottom: 10px;
    }
</style>