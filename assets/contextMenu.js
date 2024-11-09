class ContextMenu {
    constructor() {
        this.init();
    }

    init() {
        // إنشاء عنصر القائمة
        this.createMenuElement();

        // إضافة مستمعي الأحداث
        this.addEventListeners();
    }

    createMenuElement() {
        // إزالة أي قائمة موجودة
        const existingMenu = document.querySelector('.context-menu');
        if (existingMenu) {
            existingMenu.remove();
        }

        // إنشاء القائمة الجديدة
        const menu = document.createElement('div');
        menu.className = 'context-menu';
        menu.innerHTML = `
            <a href="${window.location.origin}/you/index.php" class="context-menu-item">
                <i class="fas fa-plus"></i>إضافة كورس
            </a>
            <a href="${window.location.origin}/you/courses.php" class="context-menu-item">
                <i class="fas fa-book"></i>الكورسات
            </a>
            <a href="${window.location.origin}/you/search.php" class="context-menu-item">
                <i class="fas fa-search"></i>البحث
            </a>
            <div class="context-menu-divider"></div>
            <a href="http://localhost/home/" class="context-menu-item">
                <i class="fas fa-home"></i>الرئيسية
            </a>
            <a href="http://localhost/ask/" class="context-menu-item">
                <i class="fas fa-question-circle"></i>الأسئلة
            </a>
        `;

        document.body.appendChild(menu);
        this.menu = menu;
    }

    addEventListeners() {
        // إظهار القائمة عند النقر بزر الماوس الأيمن
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            this.showMenu(e.pageX, e.pageY);
        });

        // إخفاء القائمة عند النقر في أي مكان آخر
        document.addEventListener('click', () => {
            this.hideMenu();
        });

        // إخفاء القائمة عند الضغط على ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideMenu();
            }
        });
    }

    showMenu(x, y) {
        this.menu.style.display = 'block';

        // تعديل موقع القائمة إذا كانت ستتجاوز حدود النافذة
        const menuRect = this.menu.getBoundingClientRect();
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        if (x + menuRect.width > windowWidth) {
            x = windowWidth - menuRect.width;
        }

        if (y + menuRect.height > windowHeight) {
            y = windowHeight - menuRect.height;
        }

        this.menu.style.left = `${x}px`;
        this.menu.style.top = `${y}px`;
    }

    hideMenu() {
        this.menu.style.display = 'none';
    }
}

// تهيئة القائمة عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', () => {
    window.contextMenu = new ContextMenu();
});