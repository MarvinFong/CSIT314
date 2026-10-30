<?php
// Run ONCE in the browser: http://localhost/csit314_login/config/seed.php
// Then delete this file. Creates one test account per role.
require_once __DIR__ . '/db.php';
$conn = (new Database())->connect();

$accounts = [
    ['admin',    'admin@test.com',    'User Admin'],
    ['customer', 'customer@test.com', 'Customer'],
    ['designer', 'designer@test.com', 'Interior Designer'],
    ['manager',  'manager@test.com',  'Platform Manager'],
];
$password = 'Password123!';

foreach ($accounts as $a) {
    list($username, $email, $role) = $a;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
        "INSERT IGNORE INTO user_accounts (username, email, password_hash, role_id)
         SELECT ?, ?, ?, role_id FROM roles WHERE role_name = ?");
    $stmt->bind_param("ssss", $username, $email, $hash, $role);
    $stmt->execute();
    echo "Created $username / $email ($role)<br>";
}
echo "<br>Password for all: $password";
