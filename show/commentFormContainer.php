<div class="bg-white shadow-sm rounded p-4 mb-4">
    <h3 class="text-xl font-bold mb-3">
        التعليقات
        <button id="toggleCommentForm" class="btn btn-sm btn-outline-primary float-left">
            <i class="fas fa-chevron-up"></i>
        </button>
    </h3>
    <div id="commentFormContainer">
        <form id="commentForm" class="comment-form-with-avatar">
            <!-- إضافة صورة المستخدم الثابتة -->
            <div class="d-flex align-items-start mb-3">
                <img src="show/assets/images/default-avatar.png" 
                     alt="User Avatar" 
                     class="comment-avatar">
                <div class="flex-grow-1 ms-3">
                    <label for="comment" class="form-label">التعليق</label>
                    <textarea class="form-control" id="comment" name="comment" rows="5"></textarea>
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-paper-plane me-2"></i>
                    إرسال التعليق
                </button>
            </div>
        </form>
    </div>
    <div id="comments" class="mt-4">
        <!-- قالب التعليق الذي سيتم استخدامه للتعليقات الديناميكية -->
        <template id="commentTemplate">
            <div class="comment-item d-flex mb-3 p-3 bg-light rounded">
                <img src="show/assets/images/HarryPotter_WB_F4_HarryPotterMidshot_Promo_080615_Port.jpg" 
                     alt="User Avatar" 
                     class="comment-avatar me-3">
                <div class="flex-grow-1">
                    <div class="comment-header d-flex justify-content-between align-items-center mb-2">
                        <div class="comment-meta">
                            <span class="comment-date text-muted"></span>
                        </div>
                        <button class="btn btn-sm btn-danger delete-comment">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="comment-content"></div>
                </div>
            </div>
        </template>
    </div>
</div>

<style>
/* تنسيق صورة المستخدم */
.comment-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #3498db;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    background-color: #f8f9fa; /* لون خلفية احتياطي */
}

.comment-avatar:hover {
    transform: scale(1.05);
    border-color: #2c3e50;
}

/* تنسيق نموذج التعليق */
.comment-form-with-avatar {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

#comment {
    border-radius: 10px;
    resize: none;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

/* تنسيق التعليقات */
.comment-item {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid rgba(0,0,0,0.1);
}

.comment-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.comment-header {
    border-bottom: 1px solid rgba(0,0,0,0.1);
    padding-bottom: 8px;
}

.comment-meta {
    font-size: 0.9rem;
}

.comment-date {
    color: #6c757d;
}

.comment-content {
    margin-top: 10px;
    line-height: 1.5;
}

/* تنسيق الأزرار */
.btn-primary {
    background: linear-gradient(45deg, #2c3e50, #3498db);
    border: none;
    padding: 10px 25px;
    border-radius: 10px;
    font-weight: bold;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    background: linear-gradient(45deg, #3498db, #2c3e50);
}

.delete-comment {
    opacity: 0.7;
    transition: all 0.3s ease;
}

.delete-comment:hover {
    opacity: 1;
    transform: scale(1.1);
}

/* تنسيق زر التبديل */
#toggleCommentForm {
    transition: all 0.3s ease;
}

#toggleCommentForm:hover {
    transform: rotate(180deg);
}

/* تحسينات للأجهزة المحمولة */
@media (max-width: 768px) {
    .comment-avatar {
        width: 40px;
        height: 40px;
    }
    
    .comment-form-with-avatar {
        padding: 15px;
    }
    
    .btn-primary {
        padding: 8px 20px;
    }
}
</style>

<script>
/**
 * تحديث عرض التعليقات
 * @param {number} lessonId - معرف الدرس
 */
function updateComments(lessonId) {
    $.ajax({
        url: 'show/ajax_handler.php',
        method: 'GET',
        data: {
            action: 'get_comments',
            lesson_id: lessonId
        },
        success: function(comments) {
            const commentsContainer = $('#comments');
            commentsContainer.empty();
            
            comments.forEach(comment => {
                const template = document.getElementById('commentTemplate');
                const commentElement = template.content.cloneNode(true);
                
                // تحديث محتوى التعليق
                commentElement.querySelector('.comment-date').textContent = new Date(comment.created_at).toLocaleString('ar-SA');
                commentElement.querySelector('.comment-content').textContent = comment.content;
                
                // إضافة معرف التعليق لزر الحذف
                const deleteButton = commentElement.querySelector('.delete-comment');
                deleteButton.setAttribute('data-comment-id', comment.id);
                
                commentsContainer.append(commentElement);
            });
            
            // تفعيل أزرار الحذف
            initializeDeleteButtons();
        },
        error: function(xhr, status, error) {
            console.error('Error fetching comments:', error);
            Swal.fire('خطأ!', 'حدث خطأ أثناء تحميل التعليقات', 'error');
        }
    });
}

// تهيئة أزرار حذف التعليقات
function initializeDeleteButtons() {
    $('.delete-comment').click(function() {
        const commentId = $(this).data('comment-id');
        deleteComment(commentId);
    });
}

// حذف تعليق
function deleteComment(commentId) {
    Swal.fire({
        title: 'هل أنت متأكد؟',
        text: 'سيتم حذف التعليق نهائياً',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'show/ajax_handler.php',
                method: 'POST',
                data: {
                    action: 'delete_comment',
                    comment_id: commentId
                },
                success: function(response) {
                    if (response.success) {
                        const lessonId = new URLSearchParams(window.location.search).get('lesson_id');
                        updateComments(lessonId);
                        Swal.fire('تم!', 'تم حذف التعليق بنجاح', 'success');
                    } else {
                        Swal.fire('خطأ!', 'فشل حذف التعليق', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting comment:', error);
                    Swal.fire('خطأ!', 'حدث خطأ أثناء حذف التعليق', 'error');
                }
            });
        }
    });
}

// تحديث التعليقات عند تحميل الصفحة
$(document).ready(function() {
    const lessonId = new URLSearchParams(window.location.search).get('lesson_id');
    if (lessonId) {
        updateComments(lessonId);
    }
});
</script>