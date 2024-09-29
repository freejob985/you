<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض الدرس</title>
    
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
        }
        .sidebar {
            position: fixed;
            top: 0;
            right: -300px;
            width: 300px;
            height: 100%;
            background-color: #fff;
            transition: right 0.3s ease-in-out;
            z-index: 1000;
            overflow-y: auto;
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
            right: 310px;
        }
        .comment-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            background-color: #f8fafc;
        }
        .comment-image {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 8px;
            margin-left: 16px;
        }
        .comment-content {
            flex: 1;
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
    </style>
</head>
<body class="bg-gray-100">
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>
    <div class="sidebar bg-white shadow-sm p-3" id="sidebar">
        <h3 class="text-xl font-bold mb-3">قائمة التشغيل</h3>
        <ul class="list-group" id="playlist">
            <!-- سيتم إضافة عناصر القائمة هنا ديناميكياً -->
        </ul>
    </div>
    
    <div class="container-fluid" id="mainContent">
        <div class="row">
            <!-- المحتوى الرئيسي -->
            <div class="col-md-12 p-4">
                <h1 class="text-3xl font-bold mb-4">عنوان الدرس</h1>
                
                <!-- مشغل الفيديو -->
                <div class="embed-responsive embed-responsive-16by9 mb-4">
                    <iframe class="embed-responsive-item w-full h-96" src="https://www.youtube.com/embed/VIDEO_ID" allowfullscreen></iframe>
                </div>
                
                <!-- معلومات الدرس -->
                <div class="bg-white shadow-sm rounded p-4 mb-4">
                    <h3 class="text-xl font-bold mb-3">معلومات الدرس</h3>
                    <p><strong>اللغة:</strong> <span id="lessonLanguage">العربية</span></p>
                    <p><strong>التاجات:</strong> <span id="lessonTags">HTML, CSS, JavaScript</span></p>
                    <p><strong>معلومات إضافية:</strong> <span id="lessonInfo">هذا الدرس يغطي أساسيات تطوير الويب</span></p>
                    <div class="mt-3">
                        <button class="btn btn-primary me-2" id="editLesson">تعديل</button>
                        <button class="btn btn-secondary me-2" id="changeStatus">تغيير الحالة</button>
                        <button class="btn btn-info" id="watchLesson">مشاهدة</button>
                    </div>
                </div>
                
                <!-- التعليقات -->
                <div class="bg-white shadow-sm rounded p-4 mb-4">
                    <h3 class="text-xl font-bold mb-3">
                        التعليقات
                        <button id="toggleCommentForm" class="btn btn-sm btn-outline-primary float-left">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                    </h3>
                    <div id="commentFormContainer">
                        <form id="commentForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">الاسم</label>
                                <input type="text" class="form-control" id="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">التعليق</label>
                                <textarea class="form-control" id="comment" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">إرسال التعليق</button>
                        </form>
                    </div>
                    <div id="comments" class="mt-4">
                        <!-- سيتم إضافة التعليقات هنا ديناميكياً -->
                    </div>
                </div>
                
                <!-- التعليقات مع الصور -->
                <div class="bg-white shadow-sm rounded p-4 mb-4">
                    <h3 class="text-xl font-bold mb-3">التعليقات مع الصور</h3>
                    <div id="commentsWithImages">
                        <!-- سيتم إضافة التعليقات هنا ديناميكياً -->
                    </div>
                </div>

                <!-- نموذج إضافة كود برمجي -->
                <div class="bg-white shadow-sm rounded p-4 mt-4">
                    <h3 class="text-xl font-bold mb-3">
                        إضافة كود برمجي
                        <button id="toggleCodeForm" class="btn btn-sm btn-outline-primary float-left">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                    </h3>
                    <form id="codeForm">
                        <div class="mb-3">
                            <label for="language" class="form-label">لغة البرمجة</label>
                            <select class="form-select" id="language" required>
                                <option value="javascript">JavaScript</option>
                                <option value="python">Python</option>
                                <option value="php">PHP</option>
                                <option value="java">Java</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="code" class="form-label">الكود</label>
                            <textarea class="form-control" id="code" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">إضافة الكود</button>
                    </form>
                </div>

                <!-- عرض الأكواد البرمجية -->
                <div class="bg-white shadow-sm rounded p-4 mt-4">
                    <h3 class="text-xl font-bold mb-3">الأكواد البرمجية</h3>
                    <div id="codeExamples">
                        <!-- سيتم إضافة الأكواد هنا ديناميكياً -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (مطلوب لـ Toast JS) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Toast JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // تهيئة Toast
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

            // دالة لإضافة عناصر لقائمة التشغيل
            function addPlaylistItem(title, videoId) {
                $('#playlist').append(`
                    <li class="list-group-item cursor-pointer" data-video-id="${videoId}">
                        ${title}
                    </li>
                `);
            }

            // دالة لإضافة تعليق
            function addComment(name, comment) {
                $('#comments').append(`
                    <div class="bg-gray-50 p-3 rounded mb-2">
                        <strong>${name}</strong>
                        <p>${comment}</p>
                    </div>
                `);
            }

            // دالة لإضافة كود برمجي
            function addCodeExample(language, code) {
                const codeId = 'code-' + Math.random().toString(36).substr(2, 9);
                $('#codeExamples').append(`
                    <div class="code-block mb-4">
                        <h4 class="text-lg font-semibold mb-2 text-white">${language}</h4>
                        <pre><code class="language-${language}" id="${codeId}">${code}</code></pre>
                    </div>
                `);
                hljs.highlightElement(document.getElementById(codeId));
            }

            // دالة لإضافة تعليق مع صورة
            function addCommentWithImage(name, comment, date, imageUrl) {
                $('#commentsWithImages').append(`
                    <div class="comment-card">
                        <div class="d-flex">
                            <img src="${imageUrl}" alt="${name}" class="comment-image">
                            <div class="comment-content">
                                <h4 class="comment-author">${name}</h4>
                                <p class="comment-text">${comment}</p>
                                <span class="comment-date">${date}</span>
                            </div>
                        </div>
                    </div>
                `);
            }

            // إضافة بعض العناصر لقائمة التشغيل (يمكن استبدالها بطلب API حقيقي)
            addPlaylistItem("الدرس الأول: مقدمة", "VIDEO_ID_1");
            addPlaylistItem("الدرس الثاني: أساسيات", "VIDEO_ID_2");
            addPlaylistItem("الدرس الثالث: تطبيقات عملية", "VIDEO_ID_3");

            // إضافة أمثلة للأكواد البرمجية
            addCodeExample('javascript', 'function greet(name) {\n    console.log(`Hello, ${name}!`);\n}');
            addCodeExample('python', 'def greet(name):\n    print(f"Hello, {name}!")');
            addCodeExample('php', '<?php\nfunction greet($name) {\n    echo "Hello, $name!";\n}\n?>');
            addCodeExample('java', 'public class Greeter {\n    public static void greet(String name) {\n        System.out.println("Hello, " + name + "!");\n    }\n}');

            // إضافة أمثلة للتعليقات مع الصور
            addCommentWithImage('أحمد محمد', 'شرح رائع! شكرًا لك على هذا الدرس المفيد.', '2023-04-15', 'https://i.pravatar.cc04-15', 'https://i.pravatar.cc/150?img=1');
            addCommentWithImage('سارة أحمد', 'هل يمكنك توضيح الجزء الخاص بـ async/await بشكل أكثر تفصيلاً؟', '2023-04-16', 'https://i.pravatar.cc/150?img=5');
            addCommentWithImage('محمد علي', 'أحب طريقة شرحك للمفاهيم المعقدة بأسلوب بسيط.', '2023-04-17', 'https://i.pravatar.cc/150?img=8');
            addCommentWithImage('فاطمة حسن', 'هل هناك مصادر إضافية تنصح بها لمزيد من التعمق في هذا الموضوع؟', '2023-04-18', 'https://i.pravatar.cc/150?img=10');

            // التعامل مع النقر على عناصر قائمة التشغيل
            $('#playlist').on('click', 'li', function() {
                const videoId = $(this).data('video-id');
                $('iframe').attr('src', `https://www.youtube.com/embed/${videoId}`);
            });

            // التعامل مع إرسال نموذج التعليق
            $('#commentForm').submit(function(e) {
                e.preventDefault();
                const name = $('#name').val();
                const comment = $('#comment').val();

                // التحقق من صحة البيانات
                if (name.trim() === '' || comment.trim() === '') {
                    Swal.fire({
                        title: 'خطأ!',
                        text: 'يرجى ملء جميع الحقول المطلوبة.',
                        icon: 'error',
                        confirmButtonText: 'حسناً'
                    });
                    return;
                }

                // إضافة التعليق
                addComment(name, comment);

                // إظهار رسالة نجاح
                toastr.success('تم إضافة التعليق بنجاح');

                // مسح النموذج
                this.reset();
            });

            // التعامل مع إرسال نموذج الكود
            $('#codeForm').submit(function(e) {
                e.preventDefault();
                const language = $('#language').val();
                const code = $('#code').val();

                // التحقق من صحة البيانات
                if (language.trim() === '' || code.trim() === '') {
                    Swal.fire({
                        title: 'خطأ!',
                        text: 'يرجى ملء جميع الحقول المطلوبة.',
                        icon: 'error',
                        confirmButtonText: 'حسناً'
                    });
                    return;
                }

                // إضافة الكود
                addCodeExample(language, code);

                // إظهار رسالة نجاح
                toastr.success('تم إضافة الكود بنجاح');

                // مسح النموذج
                this.reset();
            });

            // التعامل مع زر إخفاء/إظهار نموذج إضافة الكود
            $('#toggleCodeForm').click(function() {
                $('#codeForm').toggle();
                $(this).find('i').toggleClass('fa-chevron-up fa-chevron-down');
            });

            // التعامل مع زر إخفاء/إظهار نموذج التعليقات
            $('#toggleCommentForm').click(function() {
                $('#commentFormContainer').toggle();
                $(this).find('i').toggleClass('fa-chevron-up fa-chevron-down');
            });

            // التعامل مع زر إخفاء/إظهار القائمة الجانبية
            $('#sidebarToggle').click(function() {
                $('#sidebar').toggleClass('open');
                $(this).toggleClass('open');
                $('body').toggleClass('sidebar-open');
                if ($('body').hasClass('sidebar-open')) {
                    $('body').css('margin-right', '300px');
                } else {
                    $('body').css('margin-right', '0');
                }
            });

            // التعامل مع أزرار معلومات الدرس
            $('#editLesson').click(function() {
                Swal.fire('تعديل الدرس', 'هنا يمكنك إضافة نموذج لتعديل معلومات الدرس', 'info');
            });

            $('#changeStatus').click(function() {
                Swal.fire('تغيير الحالة', 'هنا يمكنك إضافة خيارات لتغيير حالة الدرس', 'info');
            });

            $('#watchLesson').click(function() {
                Swal.fire('مشاهدة الدرس', 'هنا يمكنك إضافة إجراءات إضافية لمشاهدة الدرس', 'info');
            });

            // تهيئة highlight.js
            hljs.highlightAll();
        });
    </script>
</body>
</html>