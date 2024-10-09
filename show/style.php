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

<!-- Toastr CSS for notifications -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- SweetAlert2 CSS for alert modals -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">

<!-- Highlight.js CSS for code highlighting -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.1/styles/atom-one-dark.min.css">

<!-- Highlight.js JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.1/highlight.min.js"></script>

<!-- Custom Styles -->
<style>
    /* Base body styling */
    body {
        font-family: 'Cairo', sans-serif;
        transition: margin-right 0.3s ease-in-out;
    }

    /* Styling for preformatted code blocks */
    pre code {
        direction: ltr;
        text-align: left;
        display: block;
    }

    /* Code block container styling */
    .code-block {
        background-color: #282c34;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        position: relative;
    }

    /* Sidebar styling */
    .sidebar {
        position: fixed;
        top: 0;
        right: -400px; /* Changed from left to right */
        width: 400px;
        height: 100%;
        background-color: #fff;
        transition: right 0.3s ease-in-out; /* Changed from left to right */
        z-index: 1000;
        overflow-y: auto;
        box-shadow: -2px 0 5px rgba(0,0,0,0.1);
    }

    /* Sidebar open state */
    .sidebar.open {
        right: 0; /* Changed from left to right */
    }

    /* Sidebar toggle button */
    #sidebarToggle {
        position: fixed;
        top: 10px;
        right: 10px; /* Changed from left to right */
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
        transition: right 0.3s ease-in-out; /* Changed from left to right */
    }

    /* Sidebar toggle button when sidebar is open */
    #sidebarToggle.open {
        right: 410px; /* Changed from left to right */
    }

    /* Comment card styling */
    .comment-card {
        border: none;
        border-radius: 15px;
        padding: 25px; /* زيادة التباعد الداخلي */
        margin-bottom: 30px; /* زيادة التباعد بين الكروت */
        background: linear-gradient(145deg, #ffffff, #f8f8f8);
        box-shadow: 3px 3px 10px #d1d1d1, -3px -3px 10px #ffffff; /* تقليل الظل */
        position: relative;
        display: flex;
        flex-direction: column; /* تغيير اتجاه العرض إلى عمودي */
        align-items: flex-start;
    }

    /* Delete comment button positioning */
    .comment-card .delete-comment {
        position: absolute;
        bottom: 10px; /* تغيير الموضع إلى الأسفل */
        left: 10px; /* تغيير الموضع إلى اليسار */
        background-color: transparent;
        border: none;
        color: #dc3545;
        font-size: 1.2rem;
    }

    /* Commenter profile image */
    .comment-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
        margin-bottom: 15px; /* إضافة هامش سفلي */
    }

    /* Comment content container */
    .comment-content {
        width: 100%; /* جعل المحتوى يأخذ العرض الكامل */
    }

    /* Comment author styling */
    .comment-author {
        font-weight: bold;
        color: #333;
        font-size: 1.2rem;
        margin-bottom: 10px;
    }

    /* Comment text styling */
    .comment-text {
        color: #555;
        margin-top: 10px;
        line-height: 1.6;
        margin-bottom: 20px; /* إضافة هامش سفلي */
    }

    /* Comment date styling */
    .comment-date {
        color: #888;
        font-size: 0.9rem;
        display: block;
        margin-top: auto; /* دفع التاريخ إلى الأسفل */
    }

    /* Center alignment for main heading */
    h1.text-3xl.font-bold.mb-4 {
        text-align: center;
    }

    /* Smooth scrolling */
    html {
        scroll-behavior: smooth;
    }

    /* Custom scrollbar styling */
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

    /* Adjust body margin when sidebar is open */
    body.sidebar-open {
        margin-right: 400px; /* Add margin when sidebar is open */
        transition: margin-right 0.3s ease-in-out;
    }

    /* Styling for code delete and copy buttons */
    .code-block .delete-code, .code-block .copy-code {
        margin-top: 10px;
        margin-right: 5px;
    }

    .code-block .copy-code {
        margin-left: 5px;
    }

    /* Text direction in playlist items */
    #playlist li {
        direction: ltr;
        text-align: justify;
    }

    /* Styling for completed lessons */
    .completed {
        text-decoration: line-through;
        font-weight: bold;
    }

    /* Increase font size for playlist */
    #playlist {
        font-size: 1.1em;
    }

    /* Playlist item padding */
    #playlist .list-group-item {
        padding: 12px 20px;
    }

    /* Styling for playlist statistics */
    #playlistStatistics {
        margin-top: 20px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 5px;
    }

    #playlistStatistics p {
        margin-bottom: 10px;
    }

    /* Media query for responsiveness */
    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
        }
        #sidebarToggle.open {
            right: calc(100% - 50px);
        }
        body.sidebar-open {
            margin-right: 0;
        }
    }

    /* Status Modal Styles */
    .status-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
    }

    .status-modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        border-radius: 5px;
    }

    .status-option {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        padding: 5px;
        border-radius: 5px;
        cursor: pointer;
    }

    .status-option:hover {
        background-color: #f0f0f0;
    }

    .status-color {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-right: 10px;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    /* Gradient background for Lesson Information section */
    .lesson-info-section {
        background: linear-gradient(45deg, #f3ec78, #af4261);
        color: white;
    }

    /* Adjust spacing between navigation buttons */
    nav ul li {
        margin: 0.5rem;
    }

    /* Styling for the gradient header in modals */
    .gradient-header {
        background: linear-gradient(45deg, #4a90e2, #63b3ed);
        color: white;
    }

    /* Custom styles for the modals */
    .modal-content {
        border-radius: 8px;
    }

    /* Styles for form labels and inputs */
    .form-label {
        font-weight: bold;
    }

    /* Button styling */
    .btn {
        border-radius: 5px;
    }

    /* Responsive video iframe */
    .embed-responsive iframe {
        border-radius: 8px;
    }

.form-check-input:checked {
    background-color: #0a8431;
    border-color: #7eef55;
}
li.list-group-item.cursor-pointer.active {
    background: white;
    color: black;
}
.status-modal-content {
    padding: 0px;
}
.close:hover, .close:focus {
    color: #ffffff !i
    text-decoration: none;
    padding: 5px;
    cursor: pointer;
}

</style>