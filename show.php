<?php include_once("show/header.php"); ?>

<div id="sidebarToggle">
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
            <?php
            if (isset($_GET['lesson_id'])) {
                $lessonId = $_GET['lesson_id'];
                include_once("show/mainContent.php");
                
                // إضافة أزرار التنقل مع تصميم محسن وترتيب معكوس
                echo '<div class="navigation-buttons mt-4 d-flex justify-content-between align-items-center">';
                echo '<button id="nextLesson" class="btn btn-navigation btn-next px-4 py-3">
                        <i class="fas fa-arrow-right ml-2"></i> 
                        <span>الدرس التالي</span>
                      </button>';
                echo '<button id="prevLesson" class="btn btn-navigation btn-prev px-4 py-3">
                        <span>الدرس السابق</span>
                        <i class="fas fa-arrow-left mr-2"></i>
                      </button>';
                echo '</div>';
            } else {
                echo "<p>لم يتم تحديد درس للعرض.</p>";
            }
            ?>
            
            <!-- التعليقات -->
            <?php include_once("show/commentFormContainer.php");?>
            <?php include_once("show/codeForm.php");?>
        </div>
    </div>
</div>

<!-- إضافة CSS مخصص لأزرار التنقل -->
<style>
.btn-navigation {
    font-size: 1.2rem;
    font-weight: bold;
    transition: all 0.3s ease;
    border-radius: 15px;
    min-width: 200px;
    background: linear-gradient(45deg, #2c3e50, #3498db);
    color: white;
    border: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.btn-navigation:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    background: linear-gradient(45deg, #3498db, #2c3e50);
    color: white;
}

.btn-navigation:disabled {
    background: #6c757d;
    transform: none;
    cursor: not-allowed;
}

.btn-navigation i {
    transition: transform 0.3s ease;
}

.btn-next:hover i {
    transform: translateX(-5px);
}

.btn-prev:hover i {
    transform: translateX(5px);
}

.lesson-duration {
    font-size: 0.8rem;
    color: #6c757d;
    background-color: rgba(0,0,0,0.05);
    padding: 2px 6px;
    border-radius: 4px;
    margin-right: 8px;
    display: inline-block;
}

.playlist-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.playlist-item:hover {
    background-color: rgba(0,0,0,0.02);
}

.playlist-item-title {
    flex-grow: 1;
    margin-left: 8px;
}

.playlist-item-meta {
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>

<!-- إضافة JavaScript لتحديث طريقة عرض قائمة التشغيل -->
<script>
function formatDuration(duration) {
    if (!duration) return '00:00';
    
    const minutes = Math.floor(duration / 60);
    const seconds = duration % 60;
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

function updatePlaylist(items) {
    const playlistContainer = $('#playlist');
    playlistContainer.empty();
    
    items.forEach(item => {
        const formattedDuration = formatDuration(item.duration);
        const statusClass = item.status ? `status-${item.status}` : '';
        
        const listItem = $(`
            <li class="playlist-item ${statusClass}">
                <div class="playlist-item-title">
                    <a href="show.php?lesson_id=${item.id}" class="lesson-link">
                        ${item.title}
                    </a>
                </div>
                <div class="playlist-item-meta">
                    <span class="lesson-duration">
                        <i class="far fa-clock"></i>
                        ${formattedDuration}
                    </span>
                    ${item.status ? `<span class="status-badge">${getStatusLabel(item.status)}</span>` : ''}
                </div>
            </li>
        `);
        
        playlistContainer.append(listItem);
    });
}

function getStatusLabel(status) {
    switch (status) {
        case 'completed': return 'مكتمل';
        case 'watch': return 'مشاهدة';
        case 'problem': return 'مشكلة';
        case 'discussion': return 'نقاش';
        case 'search': return 'بحث';
        case 'retry': return 'إعادة';
        case 'retry_again': return 'إعادة مرة أخرى';
        case 'review': return 'مراجعة';
        case 'excluded': return 'مستبعد';
        case 'project': return 'مشروع';
        default: return 'غير محدد';
    }
}

// تحميل قائمة التشغيل عند تحميل الصفحة
$(document).ready(function() {
    // الحصول على معرف الدرس من URL
    const urlParams = new URLSearchParams(window.location.search);
    const lessonId = urlParams.get('lesson_id');
    
    if (lessonId) {
        // الحصول على معرف الكورس من خلال AJAX
        $.ajax({
            url: 'show/ajax_handler.php',
            method: 'GET',
            data: {
                action: 'get_lesson_details',
                lesson_id: lessonId
            },
            success: function(response) {
                if (response && response.course_id) {
                    // تحميل قائمة التشغيل بعد الحصول على معرف الكورس
                    $.ajax({
                        url: 'show/ajax_handler.php',
                        method: 'GET',
                        data: {
                            action: 'get_playlist',
                            course_id: response.course_id
                        },
                        success: function(playlistResponse) {
                            if (playlistResponse.success && playlistResponse.playlistItems) {
                                updatePlaylist(playlistResponse.playlistItems);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading playlist:', error);
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error getting lesson details:', error);
            }
        });
    }
});
</script>

<?php include_once("show/footer.php"); ?>