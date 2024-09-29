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