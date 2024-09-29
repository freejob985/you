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