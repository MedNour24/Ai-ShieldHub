<?php
// Seed short HTML content for courses. Matches course titles seeded by seed-courses.php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../config/database.php';

$db = (new Database())->getConnection();
if (!$db) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

$map = [
    'Cybersecurity Foundations' => '<h2>Overview</h2><p>This course covers fundamentals: threat models, attack surfaces, authentication, authorization, and defensive strategies.</p><h3>Module 1: Introduction</h3><p>Understand what cybersecurity is and why it matters.</p><h3>Module 2: Basics</h3><p>Learn about passwords, authentication, and secure configuration.</p>',
    'Web Application Security' => '<h2>Overview</h2><p>Hands-on introduction to web vulnerabilities and how to fix them (OWASP Top 10).</p><h3>Lesson: Input Validation</h3><p>Sanitize inputs, use parameterized queries, and validate on server-side.</p><h3>Lesson: Sessions</h3><p>Secure session cookies, same-site flags, and proper logout handling.</p>',
    'Network Security Fundamentals' => '<h2>Overview</h2><p>Learn network architecture, firewalls, VPNs, and basic packet analysis.</p><h3>Exercise</h3><p>Capture and analyze a simple HTTP request with Wireshark.</p>',
    'Ethical Hacking & Penetration Testing' => '<h2>Overview</h2><p>Learn safe, legal penetration testing: recon, scanning, exploitation, and reporting.</p><h3>Getting Started</h3><p>Use virtual labs (no real targets) and follow legal guidelines.</p>',
    'Python for Security Engineers' => '<h2>Overview</h2><p>Scripting for automation: parsing logs, calling APIs, and writing simple scanners.</p><h3>Example</h3><pre>import requests
resp = requests.get(\'https://example.com\')
print(resp.status_code)</pre>',
    'Incident Response & Forensics' => '<h2>Overview</h2><p>Incident lifecycle: prepare, detect, analyze, contain, eradicate, and recover.</p><h3>Exercise</h3><p>Collect logs and identify suspicious activity patterns.</p>',
    'Secure DevOps (DevSecOps) Practices' => '<h2>Overview</h2><p>Integrate security into CI/CD: automated scans, container hardening, and policy-as-code.</p><h3>Lab</h3><p>Add a static analysis step to your pipeline.</p>',
    'Introduction to Cryptography' => '<h2>Overview</h2><p>Learn basic cryptographic primitives: encryption, hashing, and TLS basics.</p><h3>Mini-Lab</h3><p>Use OpenSSL to generate keys and encrypt a small message.</p>'
];

$added = [];
try {
    // Ensure table exists
    $db->exec("CREATE TABLE IF NOT EXISTS `course_contents` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `course_id` INT NOT NULL,
        `content_html` LONGTEXT NOT NULL,
        `created_at` DATETIME NOT NULL,
        UNIQUE KEY `ux_course` (`course_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    foreach ($map as $title => $html) {
        // Find course id
        $s = $db->prepare('SELECT id FROM courses WHERE title = :title LIMIT 1');
        $s->bindValue(':title', $title, PDO::PARAM_STR);
        $s->execute();
        $row = $s->fetch(PDO::FETCH_ASSOC);
        if (!$row) continue;
        $cid = intval($row['id']);

        // Skip if content exists
        $chk = $db->prepare('SELECT id FROM course_contents WHERE course_id = :cid LIMIT 1');
        $chk->bindValue(':cid', $cid, PDO::PARAM_INT);
        $chk->execute();
        if ($chk->fetch(PDO::FETCH_ASSOC)) continue;

        $ins = $db->prepare('INSERT INTO course_contents (course_id, content_html, created_at) VALUES (:cid, :html, :at)');
        $ins->bindValue(':cid', $cid, PDO::PARAM_INT);
        $ins->bindValue(':html', $html, PDO::PARAM_STR);
        $ins->bindValue(':at', date('Y-m-d H:i:s'));
        if ($ins->execute()) $added[] = $title;
    }

    echo json_encode(['success' => true, 'added' => $added, 'message' => count($added) > 0 ? 'Contents seeded' : 'No new content added']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>
