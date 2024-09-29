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