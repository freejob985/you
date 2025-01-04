    <footer class="bg-gray-800 text-white py-4 mt-8">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 منصة التعلم الإلكتروني. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

    <!-- المكتبات الأساسية -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/7e1mldkbut3yp4tyeob9lt5s57pb8wrb5fqbh11d6n782gm7/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    
    <!-- Tagify -->
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
    
    <?php include_once("script.php"); ?>
    <script src="show/assets/js/sections.js"></script>
    <script src="show/assets/js/navigation.js"></script>

    <style>
        /* تنسيق Tagify */
        .tagify {
            width: 100%;
            max-width: 100%;
            background: white;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }
        
        .tagify:hover {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .tagify__tag {
            background: linear-gradient(45deg, #4b6cb7, #182848);
            color: white;
            border-radius: 15px;
            padding: 5px 10px;
            transition: all 0.3s ease;
        }
        
        .tagify__tag:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .tagify__tag__removeBtn {
            color: white;
            opacity: 0.7;
        }
        
        .tagify__tag__removeBtn:hover {
            opacity: 1;
            background: rgba(255,255,255,0.1);
        }
        
        .tagify__input {
            color: #495057;
        }
        
        /* تنسيق قائمة الأقسام */
        .sections-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .section-item {
            background: linear-gradient(45deg, #4b6cb7, #182848);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .section-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        /* تنعيم الأسكرول */
        :root {
            scroll-behavior: smooth;
        }
        
        /* تخصيص شريط التمرير */
        ::-webkit-scrollbar {
            width: 12px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, #4b6cb7, #182848);
            border-radius: 10px;
            border: 3px solid #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(45deg, #182848, #4b6cb7);
        }
        
        /* تنسيق النماذج */
        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #4b6cb7;
            box-shadow: 0 0 0 0.25rem rgba(75, 108, 183, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #4b6cb7, #182848);
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            background: linear-gradient(45deg, #182848, #4b6cb7);
        }

        /* تنسيقات Modal */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .modal-header {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1rem 1.5rem;
            display: flex;
            flex-direction: row-reverse;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header .btn-close {
            margin: 0 !important;
            padding: 0.5rem 0.5rem;
            background-color: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .modal-header .btn-close:hover {
            background-color: rgba(255, 255, 255, 0.6);
            transform: rotate(90deg);
        }

        .modal-title {
            font-weight: bold;
            margin: 0;
            color: white;
        }

        .modal-body {
            padding: 1.5rem;
            background-color: #f8f9fa;
        }

        .modal-footer {
            border-top: 1px solid #eee;
            padding: 1rem 1.5rem;
            background-color: #f8f9fa;
            border-radius: 0 0 15px 15px;
        }

        /* تنسيقات الأزرار */
        .modal-footer .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .modal-footer .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .modal-footer .btn-primary {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .modal-footer .btn-primary:hover {
            background: linear-gradient(45deg, #3498db, #2c3e50);
            transform: translateY(-2px);
        }

        /* تنسيقات الأقسام داخل Modal */
        .sections-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 10px;
            background: white;
            border-radius: 8px;
            margin-top: 10px;
            border: 1px solid #e9ecef;
        }

        .section-item {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.9rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .section-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.15);
            background: linear-gradient(45deg, #3498db, #2c3e50);
        }

        /* تنسيقات Tagify داخل Modal */
        .tagify {
            --tags-border-color: #ced4da;
            --tags-hover-border-color: #3498db;
            --tags-focus-border-color: #2c3e50;
            background: white;
            border-radius: 8px;
            padding: 5px;
        }

        .tagify__tag {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            color: white;
            border-radius: 15px;
        }

        .tagify__tag__removeBtn {
            color: white;
            opacity: 0.7;
            background: none;
        }

        .tagify__tag__removeBtn:hover {
            opacity: 1;
            background: rgba(255,255,255,0.2);
        }

        .tagify__input::before {
            color: #6c757d;
            font-style: italic;
        }

        /* تنسيق حقل الإدخال */
        .form-control {
            background: white;
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        /* تنسيق التسميات */
        .form-label {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
    </style>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="show/assets/favicon.svg">
</body>
</html>