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
                <label for="comment" class="form-label">التعليق</label>
                <textarea class="form-control" id="comment" name="comment" rows="5"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">إرسال التعليق</button>
        </form>
    </div>
    <div id="comments" class="mt-4">
        <!-- سيتم إضافة التعليقات هنا ديناميكياً -->
    </div>
</div>