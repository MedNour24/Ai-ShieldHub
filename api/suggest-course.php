<?php
header('Content-Type: application/json; charset=utf-8');
// Simple course suggester endpoint
include_once __DIR__ . '/../config/Database.php';

$method = $_SERVER['REQUEST_METHOD'];

// Read input (support JSON body or form)
$input = '';
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        // fallback to POST form fields
        $data = $_POST;
    }
    $query = isset($data['query']) ? trim($data['query']) : '';
} else {
    $query = isset($_GET['query']) ? trim($_GET['query']) : '';
}

if (empty($query)) {
    echo json_encode(['success' => false, 'message' => 'Empty query']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Expand the result set and compute a relevance score in PHP to improve variety and relevance.
// Strategy:
// 1. Fetch a larger candidate set (LIMIT 50) using LIKE on terms.
// 2. Score each candidate: title matches weighted higher than description.
// 3. Boost exact phrase matches.
// 4. Add small random jitter to scores to break ties and create variety.
// 5. Return top 6 results.

$terms = preg_split('/\\s+/', $query);
$likes = [];
$params = [];
$i = 0;
foreach ($terms as $t) {
    $t = trim($t);
    if ($t === '') continue;
    $i++;
    $likes[] = "(title LIKE :t{$i} OR description LIKE :t{$i})";
    $params[":t{$i}"] = '%' . $t . '%';
}

if (count($likes) === 0) {
    echo json_encode(['success' => false, 'message' => 'No searchable terms found']);
    exit;
}

$where = implode(' OR ', $likes);

// Fetch candidates
try {
    $sql = "SELECT id, title, description, license_type, price, created_at FROM courses WHERE {$where} ORDER BY created_at DESC LIMIT 50";
    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    $stmt->execute();
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fallback: if no candidates, try a broader single LIKE
    if (!$candidates || count($candidates) === 0) {
        $fallback = $db->prepare("SELECT id, title, description, license_type, price, created_at FROM courses WHERE title LIKE :q OR description LIKE :q ORDER BY created_at DESC LIMIT 50");
        $fallback->bindValue(':q', '%' . $query . '%', PDO::PARAM_STR);
        $fallback->execute();
        $candidates = $fallback->fetchAll(PDO::FETCH_ASSOC);
    }

    // Score candidates
    $scored = [];
    $qLower = mb_strtolower($query);
    foreach ($candidates as $row) {
        $title = mb_strtolower($row['title']);
        $desc = mb_strtolower($row['description'] ?? '');

        $score = 0;

        // Phrase match (higher weight)
        if ($qLower !== '' && mb_strpos($title, $qLower) !== false) {
            $score += 12;
        }
        if ($qLower !== '' && mb_strpos($desc, $qLower) !== false) {
            $score += 6;
        }

        // Term matches
        foreach ($terms as $t) {
            $term = mb_strtolower(trim($t));
            if ($term === '') continue;
            if (mb_strpos($title, $term) !== false) $score += 5;
            if (mb_strpos($desc, $term) !== false) $score += 2;
        }

        // Recency bonus
        $createdAt = isset($row['created_at']) ? strtotime($row['created_at']) : 0;
        if ($createdAt) {
            $ageDays = (time() - $createdAt) / 86400;
            // Slight boost for newer courses
            $score += max(0, 3 - min(3, floor($ageDays / 30)));
        }

        // Small random jitter to break ties and rotate suggestions
        $score += mt_rand(0, 3) / 10.0;

        $scored[$row['id']] = ['row' => $row, 'score' => $score];
    }

    // Sort by score desc
    usort($scored, function($a, $b) {
        if ($a['score'] == $b['score']) return 0;
        return ($a['score'] > $b['score']) ? -1 : 1;
    });

    // Take top N and return
    $topN = 6;
    $results = [];
    $count = 0;
    foreach ($scored as $s) {
        $results[] = $s['row'];
        $count++;
        if ($count >= $topN) break;
    }

    echo json_encode(['success' => true, 'query' => $query, 'data' => $results], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}

?>
