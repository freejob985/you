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
                <textarea class="form-control" id="comment" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">إرسال التعليق</button>
        </form>
    </div>
    <div id="comments" class="mt-4">
        <!-- سيتم إضافة التعليقات هنا ديناميكياً -->
        <!-- مثال على هيكل التعليق -->
        <!--
        <div class="bg-gray-50 p-3 rounded mb-2 flex comment-card" data-comment-id="{comment_id}">
            <img src="{profile_image_url}" alt="Profile" class="w-12 h-12 rounded-full mr-3">
            <div class="flex-grow">
                <p>{comment_content}</p>
                <small class="text-muted">{comment_date}</small>
            </div>
            <button class="btn btn-danger btn-sm delete-comment">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
        -->
    </div>
</div>
