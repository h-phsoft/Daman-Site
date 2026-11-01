<?php
/** تسجيل الخروج */
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/auth.php';
logout_user();
header('Location: ' . url('admin/login.php'));
exit;
