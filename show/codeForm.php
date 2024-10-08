<div class="bg-white shadow-sm rounded p-4 mt-4">
    <h3 class="text-xl font-bold mb-3">
        إضافة كود برمجي
        <button id="toggleCodeForm" class="btn btn-sm btn-outline-primary float-left">
            <i class="fas fa-chevron-up"></i>
        </button>
    </h3>
    <form id="codeForm">
        <input type="hidden" id="language" value="<?php echo htmlspecialchars($lesson['language_id']); ?>">
        <div class="mb-3">
            <label for="code" class="form-label">الكود</label>
            <textarea class="form-control" id="code" rows="10" required></textarea>
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