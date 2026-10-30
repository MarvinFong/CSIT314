<?php
require_once __DIR__ . '/../config/db.php';

// <<Entity>> UserAcc
class UserAcc {
    private $role = '';

    // Authenticate(str email, str pw): bool
    public function Authenticate($email, $pw) {
        $conn = (new Database())->connect();
        $stmt = $conn->prepare(
            "SELECT ua.password_hash, ua.is_active, r.is_active AS profile_active, r.role_name
             FROM user_accounts ua
             JOIN roles r ON ua.role_id = r.role_id
             WHERE ua.email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row && $row['is_active'] && $row['profile_active'] && password_verify($pw, $row['password_hash'])) {
            $this->role = $row['role_name'];
            return true;
        }
        return false;
    }

    // getRole(): str
    public function getRole() {
        return $this->role;
    }

    // SaveAccount(str username, str password, str email, str role): bool
    public function SaveAccount($username, $password, $email, $role) {
        $conn = (new Database())->connect();

        $check = $conn->prepare("SELECT user_id FROM user_accounts WHERE email = ? OR username = ?");
        $check->bind_param("ss", $email, $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            "INSERT INTO user_accounts (username, email, password_hash, role_id)
             SELECT ?, ?, ?, role_id FROM roles WHERE role_name = ?");
        $stmt->bind_param("ssss", $username, $email, $hash, $role);
        if (!$stmt->execute()) {
            return false;
        }
        return $stmt->affected_rows === 1;
    }

    // ---------- Update User Account ----------

    // FindAccount(str username): array|null   (used to pre-fill the update form, and for A2)
    public function FindAccount($username) {
        $conn = (new Database())->connect();
        $stmt = $conn->prepare(
            "SELECT ua.username, ua.email, r.role_name
             FROM user_accounts ua
             JOIN roles r ON ua.role_id = r.role_id
             WHERE ua.username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row : null;
    }

    // GetAllAccounts(): array   (simple list so the admin can pick an account to update)
    public function GetAllAccounts() {
        $conn = (new Database())->connect();
        $res = $conn->query(
            "SELECT ua.user_id, ua.username, ua.email, r.role_name, ua.is_active
             FROM user_accounts ua
             JOIN roles r ON ua.role_id = r.role_id
             ORDER BY ua.user_id");
        $rows = array();
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    // ModifyAccount(str username, str new_email, str new_role, str new_password): bool
    // new_password is optional: '' means "leave the password unchanged".
    public function ModifyAccount($username, $new_email, $new_role, $new_password = '') {
        $conn = (new Database())->connect();

        // account must exist
        $find = $conn->prepare("SELECT user_id FROM user_accounts WHERE username = ?");
        $find->bind_param("s", $username);
        $find->execute();
        $acc = $find->get_result()->fetch_assoc();
        if (!$acc) {
            return false;
        }
        $uid = (int)$acc['user_id'];

        // new email must not belong to someone else
        $dup = $conn->prepare("SELECT user_id FROM user_accounts WHERE email = ? AND user_id <> ?");
        $dup->bind_param("si", $new_email, $uid);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) {
            return false;
        }

        // role must exist
        $r = $conn->prepare("SELECT role_id FROM roles WHERE role_name = ?");
        $r->bind_param("s", $new_role);
        $r->execute();
        $roleRow = $r->get_result()->fetch_assoc();
        if (!$roleRow) {
            return false;
        }
        $rid = (int)$roleRow['role_id'];

        if ($new_password !== '') {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE user_accounts SET email = ?, role_id = ?, password_hash = ? WHERE user_id = ?");
            $stmt->bind_param("sisi", $new_email, $rid, $hash, $uid);
        } else {
            $stmt = $conn->prepare("UPDATE user_accounts SET email = ?, role_id = ? WHERE user_id = ?");
            $stmt->bind_param("sii", $new_email, $rid, $uid);
        }
        return $stmt->execute();
    }

    // ---------- View User Account ----------

    // FindUser(userId): userData | null
    // userId may be the numeric ID or the username. Read-only: never changes the database.
    public function FindUser($userId) {
        $conn = (new Database())->connect();
        $id = ctype_digit((string)$userId) ? (int)$userId : -1;
        $stmt = $conn->prepare(
            "SELECT ua.user_id, ua.username, ua.email, r.role_name, ua.is_active
             FROM user_accounts ua
             JOIN roles r ON ua.role_id = r.role_id
             WHERE ua.user_id = ? OR ua.username = ?");
        $stmt->bind_param("is", $id, $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row : null;
    }

    // ---------- Suspend User Account ----------

    // UpdateStatus(userId, newStatus): bool
    // newStatus is "Suspended" or "Active" (stored in the is_active column as 0 / 1)
    public function UpdateStatus($userId, $newStatus) {
        if ($newStatus === 'Suspended') {
            $flag = 0;
        } elseif ($newStatus === 'Active') {
            $flag = 1;
        } else {
            return false;
        }
        $uid = (int)$userId;
        $conn = (new Database())->connect();
        $stmt = $conn->prepare("UPDATE user_accounts SET is_active = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $flag, $uid);
        if (!$stmt->execute()) {
            return false;
        }
        return $stmt->affected_rows === 1;
    }

    // ---------- Search User Account ----------

    // SearchUser(criteria): userList  (empty array when nothing matches)
    // Partial match on username or email, exact match on numeric ID. Read-only.
    public function SearchUser($criteria) {
        $conn = (new Database())->connect();
        $escaped = str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $criteria);
        $like = '%' . $escaped . '%';
        $id = ctype_digit((string)$criteria) ? (int)$criteria : -1;

        $stmt = $conn->prepare(
            "SELECT ua.user_id, ua.username, ua.email, r.role_name, ua.is_active
             FROM user_accounts ua
             JOIN roles r ON ua.role_id = r.role_id
             WHERE ua.username LIKE ? OR ua.email LIKE ? OR ua.user_id = ?
             ORDER BY ua.user_id");
        $stmt->bind_param("ssi", $like, $like, $id);
        $stmt->execute();
        $res = $stmt->get_result();

        $rows = array();
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
}
