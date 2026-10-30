<?php
require_once __DIR__ . '/../config/db.php';

// <<Entity>> UserProfile
// A profile is a row in the `roles` table plus its permissions in `profile_permissions`.
class UserProfile {

    // saveProfile(str profileName, array permissionIds): bool
    // Returns false on a duplicate name or any database problem (nothing is saved).
    public function saveProfile($profileName, $permissionIds) {
        $conn = (new Database())->connect();

        // Alternative flow: duplicate profile name
        $check = $conn->prepare("SELECT role_id FROM roles WHERE role_name = ?");
        $check->bind_param("s", $profileName);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return false;
        }

        try {
            $conn->begin_transaction();

            $ins = $conn->prepare("INSERT INTO roles (role_name) VALUES (?)");
            $ins->bind_param("s", $profileName);
            if (!$ins->execute()) {
                $conn->rollback();
                return false;
            }
            $roleId = (int)$conn->insert_id;

            foreach ($permissionIds as $pid) {
                $pid = (int)$pid;
                // only links permissions that really exist
                $link = $conn->prepare(
                    "INSERT INTO profile_permissions (role_id, permission_id)
                     SELECT ?, permission_id FROM permissions WHERE permission_id = ?");
                $link->bind_param("ii", $roleId, $pid);
                if (!$link->execute()) {
                    $conn->rollback();
                    return false;
                }
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    // GetAllPermissions(): array of ['permission_id', 'permission_name']  (for the checkboxes)
    public function GetAllPermissions() {
        $conn = (new Database())->connect();
        $res = $conn->query("SELECT permission_id, permission_name FROM permissions ORDER BY permission_id");
        $rows = array();
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    // GetAllProfileNames(): array of strings  (feeds the role dropdowns when assigning profiles)
    public function GetAllProfileNames() {
        $conn = (new Database())->connect();
        $res = $conn->query("SELECT role_name FROM roles ORDER BY role_id");
        $names = array();
        while ($row = $res->fetch_assoc()) {
            $names[] = $row['role_name'];
        }
        return $names;
    }

    // ---------- Update User Profile ----------

    // GetAllProfiles(): array of ['role_id', 'role_name']  (for the dropdown)
    public function GetAllProfiles() {
        $conn = (new Database())->connect();
        $res = $conn->query("SELECT role_id, role_name, is_active FROM roles ORDER BY role_id");
        $rows = array();
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    // GetProfile(profileId): ['role_id', 'role_name', 'permission_ids' => [...]] | null
    public function GetProfile($profileId) {
        $conn = (new Database())->connect();
        $rid = (int)$profileId;

        $stmt = $conn->prepare("SELECT role_id, role_name FROM roles WHERE role_id = ?");
        $stmt->bind_param("i", $rid);
        $stmt->execute();
        $profile = $stmt->get_result()->fetch_assoc();
        if (!$profile) {
            return null;
        }

        $p = $conn->prepare("SELECT permission_id FROM profile_permissions WHERE role_id = ?");
        $p->bind_param("i", $rid);
        $p->execute();
        $res = $p->get_result();
        $ids = array();
        while ($row = $res->fetch_assoc()) {
            $ids[] = (int)$row['permission_id'];
        }
        $profile['permission_ids'] = $ids;
        return $profile;
    }

    // saveProfileSettings(profileId, newName, permissionIds): bool
    // Renames the profile and replaces its permission set. All or nothing.
    public function saveProfileSettings($profileId, $newName, $permissionIds) {
        $conn = (new Database())->connect();
        $rid = (int)$profileId;

        // profile must exist
        $find = $conn->prepare("SELECT role_id FROM roles WHERE role_id = ?");
        $find->bind_param("i", $rid);
        $find->execute();
        if ($find->get_result()->num_rows === 0) {
            return false;
        }

        // duplicate name (another profile already uses it)
        $dup = $conn->prepare("SELECT role_id FROM roles WHERE role_name = ? AND role_id <> ?");
        $dup->bind_param("si", $newName, $rid);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) {
            return false;
        }

        try {
            $conn->begin_transaction();

            $upd = $conn->prepare("UPDATE roles SET role_name = ? WHERE role_id = ?");
            $upd->bind_param("si", $newName, $rid);
            if (!$upd->execute()) {
                $conn->rollback();
                return false;
            }

            $del = $conn->prepare("DELETE FROM profile_permissions WHERE role_id = ?");
            $del->bind_param("i", $rid);
            if (!$del->execute()) {
                $conn->rollback();
                return false;
            }

            foreach ($permissionIds as $pid) {
                $pid = (int)$pid;
                $link = $conn->prepare(
                    "INSERT INTO profile_permissions (role_id, permission_id)
                     SELECT ?, permission_id FROM permissions WHERE permission_id = ?");
                $link->bind_param("ii", $rid, $pid);
                if (!$link->execute()) {
                    $conn->rollback();
                    return false;
                }
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    // ---------- View User Profile ----------

    // getProfileDetails(userId): array | null
    // Returns the user's account info, assigned profile and that profile's permissions.
    // Read-only: never changes the database.
    public function getProfileDetails($userId) {
        $conn = (new Database())->connect();
        $uid = (int)$userId;

        $stmt = $conn->prepare(
            "SELECT ua.user_id, ua.username, ua.email, ua.is_active, r.role_id, r.role_name
             FROM user_accounts ua
             JOIN roles r ON ua.role_id = r.role_id
             WHERE ua.user_id = ?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) {
            return null;
        }

        $rid = (int)$row['role_id'];
        $p = $conn->prepare(
            "SELECT p.permission_name
             FROM profile_permissions pp
             JOIN permissions p ON pp.permission_id = p.permission_id
             WHERE pp.role_id = ?
             ORDER BY p.permission_id");
        $p->bind_param("i", $rid);
        $p->execute();
        $res = $p->get_result();
        $names = array();
        while ($r = $res->fetch_assoc()) {
            $names[] = $r['permission_name'];
        }
        $row['permissions'] = $names;
        return $row;
    }

    // ---------- Suspend User Profile ----------

    // getProfileDetail(profileId): ['role_id', 'role_name', 'is_active'] | null
    public function getProfileDetail($profileId) {
        $conn = (new Database())->connect();
        $rid = (int)$profileId;
        $stmt = $conn->prepare("SELECT role_id, role_name, is_active FROM roles WHERE role_id = ?");
        $stmt->bind_param("i", $rid);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row : null;
    }

    // updateStatus(profileId, newStatus): bool   ("Suspended" or "Active")
    // Changes only the status flag. No accounts or profiles are deleted.
    public function updateStatus($profileId, $newStatus) {
        if ($newStatus === 'Suspended') {
            $flag = 0;
        } elseif ($newStatus === 'Active') {
            $flag = 1;
        } else {
            return false;
        }
        $rid = (int)$profileId;
        $conn = (new Database())->connect();
        $stmt = $conn->prepare("UPDATE roles SET is_active = ? WHERE role_id = ?");
        $stmt->bind_param("ii", $flag, $rid);
        if (!$stmt->execute()) {
            return false;
        }
        return $stmt->affected_rows === 1;
    }

    // ---------- Search User Profile ----------

    // searchRoles(keyword): array of profiles (empty array when nothing matches)
    // Partial match on the profile name, with each profile's permissions. Read-only.
    public function searchRoles($keyword) {
        $conn = (new Database())->connect();
        $escaped = str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $keyword);
        $like = '%' . $escaped . '%';

        $stmt = $conn->prepare(
            "SELECT r.role_id, r.role_name, r.is_active,
                    GROUP_CONCAT(p.permission_name ORDER BY p.permission_id SEPARATOR ', ') AS permissions
             FROM roles r
             LEFT JOIN profile_permissions pp ON pp.role_id = r.role_id
             LEFT JOIN permissions p ON p.permission_id = pp.permission_id
             WHERE r.role_name LIKE ?
             GROUP BY r.role_id, r.role_name, r.is_active
             ORDER BY r.role_id");
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $res = $stmt->get_result();

        $rows = array();
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
}
