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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.1/styles/default.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.1/highlight.min.js"></script>
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container-fluid">
        <div class="row">
            <!-- القائمة الجانبية -->
            <div class="col-md-3 bg-white shadow-sm p-3 h-screen overflow-auto">
                <h3 class="text-xl font-bold mb-3">قائمة التشغيل</h3>
                <ul class="list-group" id="playlist">
                    <!-- سيتم إضافة عناصر القائمة هنا ديناميكياً -->
                </ul>
            </div>
            
            <!-- المحتوى الرئيسي -->
            <div class="col-md-9 p-4">
                <h1 class="text-3xl font-bold mb-4">عنوان الدرس</h1>
                
                <!-- مشغل الفيديو -->
                <div class="embed-responsive embed-responsive-16by9 mb-4">
                    <iframe class="embed-responsive-item w-full h-96" src="https://www.youtube.com/embed/VIDEO_ID" allowfullscreen></iframe>
                </div>
                
                <!-- التعليقات -->
                <div class="bg-white shadow-sm rounded p-4 mb-4">
                    <h3 class="text-xl font-bold mb-3">التعليقات</h3>
                    <div id="comments">
                        <!-- سيتم إضافة التعليقات هنا ديناميكياً -->
                    </div>
                </div>
                
                <!-- نموذج إضافة تعليق -->
                <div class="bg-white shadow-sm rounded p-4">
                    <h3 class="text-xl font-bold mb-3">إضافة تعليق</h3>
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

                <!-- نموذج إضافة كود برمجي -->
                <div class="bg-white shadow-sm rounded p-4 mt-4">
                    <h3 class="text-xl font-bold mb-3">إضافة كود برمجي</h3>
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

                <!-- عرض التعليقات مع الصور -->
                <div class="bg-white shadow-sm rounded p-4 mt-4">
                    <h3 class="text-xl font-bold mb-3">التعليقات مع الصور</h3>
                    <div id="commentsWithImages">
                        <!-- سيتم إضافة التعليقات هنا ديناميكياً -->
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
                    <div class="mb-4">
                        <h4 class="text-lg font-semibold mb-2">${language}</h4>
                        <pre><code class="language-${language}" id="${codeId}">${code}</code></pre>
                    </div>
                `);
                hljs.highlightElement(document.getElementById(codeId));
            }

            // دالة لإضافة تعليق مع صورة
            function addCommentWithImage(name, comment, date, imageUrl) {
                $('#commentsWithImages').append(`
                    <div class="flex items-start space-x-4 mb-4">
                        <img src="${imageUrl}" alt="${name}" class="w-12 h-12 rounded-full">
                        <div>
                            <h4 class="font-semibold">${name}</h4>
                            <p class="text-gray-600">${comment}</p>
                            <span class="text-sm text-gray-500">${date}</span>
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
            addCommentWithImage('أحمد محمد', 'شرح رائع! شكرًا لك على هذا الدرس المفيد.', '2023-04-15', 'https://i.pravatar.cc/150?img=1');
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
                if (code.trim() === '') {
                    Swal.fire({
                        title: 'خطأ!',
                        text: 'يرجى إدخال الكود البرمجي.',
                        icon: 'error',
                        confirmButtonText: 'حسناً'
                    });
                    return;
                }

                // إضافة الكود
                addCodeExample(language, code);

                // إظهار رسالة نجاح
                toastr.success('تم إضافة الكود البرمجي بنجاح');

                // مسح النموذج
                this.reset();
            });

            // تهيئة highlight.js
            hljs.highlightAll();
        });
    </script>
</body>
</html>