<?php
// Seed script to add relevant course content into `courses` table.
// Usage: open in browser: http://localhost/courses/api/seed-courses.php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../config/database.php';

$db = (new Database())->getConnection();
if (!$db) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

$courses = [
    [
        'title' => 'Cybersecurity Foundations',
        'description' => "A beginner-friendly course introducing cybersecurity fundamentals: threat models, attack surfaces, authentication, authorization, network basics, and defensive strategies. Includes hands-on labs to practice safe configuration and basic incident response.",
        'license_type' => 'free',
        'price' => 0.00,
        'duration' => '6'
    ],
    [
        'title' => 'Web Application Security',
        'description' => "Learn to find and fix common web vulnerabilities (OWASP Top 10), secure session management, input validation, secure authentication, safe file handling, and secure deployment practices. Labs with vulnerable web apps for safe testing.",
        'license_type' => 'paid',
        'price' => 49.99,
        'duration' => '12'
    ],
    [
        'title' => 'Network Security Fundamentals',
        'description' => "Covers network architecture, firewalls, VPNs, intrusion detection systems (IDS/IPS), segmentation, and basic packet analysis using Wireshark. Practical exercises include building secure network configurations and analyzing traffic.",
        'license_type' => 'paid',
        'price' => 39.99,
        'duration' => '10'
    ],
    [
        'title' => 'Ethical Hacking & Penetration Testing',
        'description' => "A hands-on course teaching reconnaissance, scanning, exploitation, post-exploitation, and reporting. Learn safe, legal penetration testing methodologies, Kali tools, and how to responsibly disclose vulnerabilities.",
        'license_type' => 'paid',
        'price' => 69.99,
        'duration' => '18'
    ],
    [
        'title' => 'Python for Security Engineers',
        'description' => "Learn Python scripting for security tasks: log parsing, automation, building simple scanners, interacting with APIs, and quick utilities to support red-team/blue-team operations.",
        'license_type' => 'free',
        'price' => 0.00,
        'duration' => '8'
    ],
    [
        'title' => 'Incident Response & Forensics',
        'description' => "Designed for defenders: incident lifecycle, evidence collection, memory and disk forensics basics, log analysis, containment strategies, and building an incident response playbook.",
        'license_type' => 'paid',
        'price' => 59.99,
        'duration' => '14'
    ],
    [
        'title' => 'Secure DevOps (DevSecOps) Practices',
        'description' => "Integrate security into the software lifecycle: CI/CD hardening, container security, infrastructure as code checks, automated scanning, and policy-as-code. Practical labs with common CI tools.",
        'license_type' => 'paid',
        'price' => 54.99,
        'duration' => '11'
    ],
    [
        'title' => 'Introduction to Cryptography',
        'description' => "Understand symmetric/asymmetric encryption, hashing, digital signatures, TLS fundamentals, and how cryptography is used to protect data in transit and at rest.",
        'license_type' => 'free',
        'price' => 0.00,
        'duration' => '5'
    ]
];

$added = [];
foreach ($courses as $c) {
    // Check if course with same title exists
    $check = $db->prepare('SELECT id FROM courses WHERE title = :title LIMIT 1');
    $check->bindValue(':title', $c['title'], PDO::PARAM_STR);
    $check->execute();
    if ($check->fetch(PDO::FETCH_ASSOC)) continue;

    $ins = $db->prepare('INSERT INTO courses (title, description, license_type, price, duration, status, created_at) VALUES (:title, :description, :license_type, :price, :duration, :status, :created_at)');
    $ins->bindValue(':title', $c['title'], PDO::PARAM_STR);
    $ins->bindValue(':description', $c['description'], PDO::PARAM_STR);
    $ins->bindValue(':license_type', $c['license_type'], PDO::PARAM_STR);
    $ins->bindValue(':price', $c['price']);
    $ins->bindValue(':duration', $c['duration'], PDO::PARAM_STR);
    $ins->bindValue(':status', 'active', PDO::PARAM_STR);
    $ins->bindValue(':created_at', date('Y-m-d H:i:s'));
    if ($ins->execute()) {
        $added[] = $c['title'];
    }
}

echo json_encode(['success' => true, 'added' => $added, 'message' => count($added) > 0 ? 'Seed complete' : 'No new courses added']);

?>
