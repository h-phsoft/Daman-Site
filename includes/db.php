<?php
/**
 * اتصال قاعدة البيانات عبر PDO — Prepared Statements حصريًا.
 * لا يُكشف أي خطأ PDO للزائر في بيئة الإنتاج.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ]);
    } catch (PDOException $e) {
        // تسجيل داخلي فقط — عدم تسريب التفاصيل للزائر
        error_log('DB connection failed: ' . $e->getMessage());
        http_response_code(503);
        if (APP_ENV === 'production') {
            exit('حدث خطأ مؤقت في الخدمة. نعتذر عن الإزعاج.');
        }
        throw $e;
    }

    return $pdo;
}

/** تنفيذ استعلام مع prepared statements وإرجاع Statement */
function q(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/** إرجاع صف واحد أو null */
function fetchOne(string $sql, array $params = []): ?array
{
    $row = q($sql, $params)->fetch();
    return $row === false ? null : $row;
}

/** إرجاع جميع الصفوف */
function fetchAll(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

/** إرجاع قيمة عمود أول في الصف الأول */
function fetchValue(string $sql, array $params = [])
{
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}
