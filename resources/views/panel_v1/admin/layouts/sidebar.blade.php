<aside class="panel-v1-sidebar" aria-label="قائمة الإدارة">
    <div class="panel-v1-sidebar__brand">
        <span class="panel-v1-sidebar__logo-mark">Q&amp;E</span>
        <span class="panel-v1-sidebar__logo-text">QUALITY &amp; EXCELLENCE</span>
    </div>

    <nav class="panel-v1-sidebar__nav">
        <p class="panel-v1-sidebar__group">عام</p>
        <a class="is-active" href="{{ route('panel.v1.admin.home') }}">لوحة التحكم الرئيسية</a>

        <p class="panel-v1-sidebar__group">المستخدمون</p>
        <a href="{{ getAdminPanelUrl('/students') }}">المتدربون</a>
        <a href="{{ getAdminPanelUrl('/instructors') }}">المدربون</a>
        <a href="{{ getAdminPanelUrl('/organizations') }}">المنظمات</a>
        <a href="{{ getAdminPanelUrl('/roles') }}">الأدوار</a>

        <p class="panel-v1-sidebar__group">إدارة أكاديمية</p>
        <a href="{{ getAdminPanelUrl('/webinars') }}">الدورات</a>
        <a href="{{ getAdminPanelUrl('/reviews') }}">التقييمات</a>
        <a href="{{ getAdminPanelUrl('/quizzes') }}">الاختبارات</a>
        <a href="{{ getAdminPanelUrl('/certificates') }}">الشهادات</a>

        <p class="panel-v1-sidebar__group">التواصل</p>
        <a href="{{ getAdminPanelUrl('/supports') }}">الدعم</a>
        <a href="{{ getAdminPanelUrl('/notifications') }}">الإشعارات</a>

        <p class="panel-v1-sidebar__group">المالية والإعدادات</p>
        <a href="{{ getAdminPanelUrl('/financial/sales') }}">المبيعات</a>
        <a href="{{ getAdminPanelUrl('/settings/general') }}">الإعدادات</a>
    </nav>

    <a class="panel-v1-sidebar__logout" href="/logout">تسجيل الخروج</a>
</aside>
