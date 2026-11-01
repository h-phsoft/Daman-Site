<?php
/**
 * حماية لوحة التحكم: جلسات، تحقق الدخول، صلاحيات.
 */

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

emit_security_headers();

/** هل المستخدم مسجّل دخوله؟ */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) return null;
    static $u = null;
    if ($u === null) {
        $u = fetchOne('SELECT id, username, role FROM users WHERE id = ?', [(int)$_SESSION['user_id']]);
    }
    return $u ?: null;
}

/** فرض تسجيل الدخول — يحوّل لصفحة الدخول أو ينهي الطلب */
function require_admin(): void
{
    if (!is_logged_in()) {
        $target = $_SERVER['REQUEST_URI'] ?? '';
        header('Location: ' . url('admin/login.php?next=' . urlencode(basename($target))));
        exit;
    }
    // منع تسهيل هجمات الجلسة: مدة صلاحية 4 ساعات
    if (!isset($_SESSION['login_time'])) {
        $_SESSION['login_time'] = time();
    } elseif (time() - (int)$_SESSION['login_time'] > 4 * 3600) {
        session_unset();
        session_destroy();
        header('Location: ' . url('admin/login.php?expired=1'));
        exit;
    }
}

/** محاولة تسجيل دخول مع rate-limiting بسيط */
function attempt_login(string $username, string $password): bool
{
    // حد المحاولات: 5 محاولات / 10 دقائق لكل IP
    $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? [];
    $now = time();
    $_SESSION['login_attempts'] = array_values(array_filter(
        $_SESSION['login_attempts'],
        fn($t) => $t > $now - 600
    ));
    if (count($_SESSION['login_attempts']) >= 5) {
        return false;
    }
    $_SESSION['login_attempts'][] = $now + 600;

    $user = fetchOne('SELECT * FROM users WHERE username = ?', [$username]);
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']     = (int)$user['id'];
        $_SESSION['username']    = $user['username'];
        $_SESSION['role']        = $user['role'];
        $_SESSION['login_time']  = time();
        unset($_SESSION['login_attempts']);
        // إعادة توليد توكن CSRF بعد رفع الصلاحيات
        unset($_SESSION['csrf_token']);
        csrf_token();
        return true;
    }
    return false;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** تغيير كلمة مرور المستخدم الحالي */
function change_own_password(int $userId, string $newPass): void
{
    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    q('UPDATE users SET password_hash = ? WHERE id = ?', [$hash, $userId]);
}
