<?php
/**
 * NullCastle Systems — api/users.php
 * REST endpoint: GET /api/users.php
 *
 * Authentication: valid admin session required.
 * Returns JSON — never HTML.
 *
 * Optional query-string filters (all combinable):
 *   ?status=active|suspended
 *   ?search=<string>   — matches name, role, department (case-insensitive)
 *   ?clearance=OMEGA|ALPHA|SIGMA|DELTA|GHOST
 *   ?page=<int>&per_page=<int>  — defaults: page 1, per_page 100
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
// Prevent browsers / proxies from caching sensitive data
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

require_once __DIR__ . '/../db.php';

$pdo = get_pdo();
if (!$pdo) {
    http_response_code(503);
    echo json_encode(['error' => 'Database connection failed. Check environment variables.']);
    exit;
}

$allowed_statuses    = ['active', 'suspended'];
$allowed_clearances  = ['OMEGA', 'ALPHA', 'SIGMA', 'DELTA', 'GHOST'];

$filter_status    = isset($_GET['status'])    ? strtolower(trim($_GET['status']))    : null;
$filter_clearance = isset($_GET['clearance']) ? strtoupper(trim($_GET['clearance'])) : null;
$search           = isset($_GET['search'])    ? trim($_GET['search'])                : null;
$page             = max(1, (int)($_GET['page']     ?? 1));
$per_page         = max(1, min(500, (int)($_GET['per_page'] ?? 100)));

if ($filter_status    && !in_array($filter_status,    $allowed_statuses,   true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status filter. Use: active, suspended.']);
    exit;
}
if ($filter_clearance && !in_array($filter_clearance, $allowed_clearances, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid clearance filter. Use: OMEGA, ALPHA, SIGMA, DELTA, GHOST.']);
    exit;
}
$conditions = [];
$params     = [];

if ($filter_status) {
    $conditions[] = 'UPPER(status) = :status';
    $params[':status'] = strtoupper($filter_status);
}
if ($filter_clearance) {
    $conditions[] = 'UPPER(clearance) = :clearance';
    $params[':clearance'] = $filter_clearance;
}
if ($search !== null && $search !== '') {
    $like = '%' . $search . '%';
    $conditions[] = '(name ILIKE :s1 OR role ILIKE :s2 OR department ILIKE :s3)';
    $params[':s1'] = $like;
    $params[':s2'] = $like;
    $params[':s3'] = $like;
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

try {
    // Total count (for pagination metadata)
    $count_sql  = "SELECT COUNT(*) FROM site_users $where";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total = (int)$count_stmt->fetchColumn();

    // Paginated data
    $offset  = ($page - 1) * $per_page;
    $data_sql = "SELECT id, name, email, role, department, clearance, status,
                        joined, last_login
                 FROM site_users
                 $where
                 ORDER BY id ASC
                 LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($data_sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    exit;
}

$response = [
    'meta' => [
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $per_page,
        'total_pages' => $per_page > 0 ? (int)ceil($total / $per_page) : 1,
        'generated_at'=> gmdate('Y-m-d\TH:i:s\Z'),
    ],
    'users' => array_map(function ($u) {
        return [
            'id'         => (int)$u['id'],
            'name'       => $u['name'],
            'email'      => $u['email'],
            'role'       => $u['role'],
            'department' => $u['department'],
            'clearance'  => strtoupper($u['clearance']),
            'status'     => strtoupper($u['status']),
            'joined'     => $u['joined'] ?? null,
            'last_login' => $u['last_login'] ?? null,
        ];
    }, $users),
];

http_response_code(200);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);