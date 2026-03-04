<?php
require_once __DIR__ . '/db.php';

// Login user
function loginUser($username, $password) {
    $pdo = connect();

    $stmt = $pdo->prepare("SELECT * FROM administrators WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) return false;

    if (password_verify($password, $admin['password_hash'])) {
        return $admin;
    }

    // fallback for old plaintext
    if ($password === $admin['password_hash']) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE administrators SET password_hash = :hash WHERE admin_id = :id");
        $update->execute([
            'hash' => $hashed,
            'id' => $admin['admin_id']
        ]);
        return $admin;
    }

    return false;
}

function requireLogin() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }
}

function logoutUser() {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}


// Update username and password
function updateAdminProfile($adminId, $username, $newPassword = null) {
    $pdo = connect();

    // Check if username already exists (except current user)
    $check = $pdo->prepare("
        SELECT admin_id 
        FROM administrators 
        WHERE username = :username 
        AND admin_id != :id
        LIMIT 1
    ");
    $check->execute([
        'username' => $username,
        'id' => $adminId
    ]);

    if ($check->fetch()) {
        return ['success' => false, 'message' => 'Username already taken.'];
    }

    // If password is provided
    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            UPDATE administrators
            SET username = :username,
                password_hash = :password
            WHERE admin_id = :id
        ");

        $stmt->execute([
            'username' => $username,
            'password' => $hashedPassword,
            'id' => $adminId
        ]);
    } else {
        // Update only username
        $stmt = $pdo->prepare("
            UPDATE administrators
            SET username = :username
            WHERE admin_id = :id
        ");

        $stmt->execute([
            'username' => $username,
            'id' => $adminId
        ]);
    }

    // Update session username
    $_SESSION['username'] = $username;

    return ['success' => true, 'message' => 'Profile updated successfully.'];
}


// CREATE ADMIN / REGISTRAR
function createAdministrator($data) {
    $pdo = connect();

    // Check duplicate username
    $checkUser = $pdo->prepare("SELECT admin_id FROM administrators WHERE username = ? LIMIT 1");
    $checkUser->execute([$data['username']]);
    if ($checkUser->fetch()) {
        return ['success' => false, 'message' => 'Username already exists.'];
    }

    // Check duplicate email
    $checkEmail = $pdo->prepare("SELECT admin_id FROM administrators WHERE email = ? LIMIT 1");
    $checkEmail->execute([$data['email']]);
    if ($checkEmail->fetch()) {
        return ['success' => false, 'message' => 'Email already exists.'];
    }

    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO administrators (
            username,
            password_hash,
            first_name,
            middle_name,
            last_name,
            suffix,
            email,
            contact_number,
            role,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");

    $stmt->execute([
        $data['username'],
        $hashedPassword,
        $data['first_name'],
        $data['middle_name'],
        $data['last_name'],
        $data['suffix'],
        $data['email'],
        $data['contact_number'],
        $data['role']
    ]);

    return ['success' => true, 'message' => 'User created successfully.'];
}


// GET ALL ADMINISTRATORS
function getAllAdministrators() {
    $pdo = connect();

    $stmt = $pdo->query("
        SELECT * FROM administrators
        ORDER BY created_at ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// SEARCH ADMINISTRATORS
function searchAdministrators($keyword) {
    $pdo = connect();

    $stmt = $pdo->prepare("
        SELECT * FROM administrators
        WHERE admin_id LIKE :keyword
        OR last_name LIKE :keyword
        ORDER BY created_at DESC
    ");

    $stmt->execute([
        'keyword' => "%$keyword%"
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Get Single Administrator
function getAdministratorById($id) {
    $pdo = connect();

    $stmt = $pdo->prepare("
        SELECT * FROM administrators
        WHERE admin_id = :id
        LIMIT 1
    ");

    $stmt->execute(['id' => $id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}




// Update Administrator
function updateAdministrator($id, $data) {
    $pdo = connect();

    // Check duplicate email (except current admin)
    $checkEmail = $pdo->prepare("
        SELECT admin_id FROM administrators
        WHERE email = :email
        AND admin_id != :id
        LIMIT 1
    ");

    $checkEmail->execute([
        'email' => $data['email'],
        'id'    => $id
    ]);

    if ($checkEmail->fetch()) {
        return ['success' => false, 'message' => 'Email already exists.'];
    }

    $stmt = $pdo->prepare("
        UPDATE administrators
        SET first_name = :first_name,
            middle_name = :middle_name,
            last_name = :last_name,
            suffix = :suffix,
            email = :email,
            contact_number = :contact_number,
            role = :role,
            status = :status
        WHERE admin_id = :id
    ");

    $stmt->execute([
        'first_name'     => $data['first_name'],
        'middle_name'    => $data['middle_name'],
        'last_name'      => $data['last_name'],
        'suffix'         => $data['suffix'],
        'email'          => $data['email'],
        'contact_number' => $data['contact_number'],
        'role'           => $data['role'],
        'status'         => $data['status'],
        'id'             => $id
    ]);

    return ['success' => true, 'message' => 'Administrator updated successfully.'];
}




// Delete Administrator
function deleteAdministrator($id) {
    $pdo = connect();

    $stmt = $pdo->prepare("
        DELETE FROM administrators
        WHERE admin_id = :id
        LIMIT 1
    ");

    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() > 0) {
        return ['success' => true, 'message' => 'Administrator deleted successfully.'];
    }

    return ['success' => false, 'message' => 'Administrator not found.'];
}



