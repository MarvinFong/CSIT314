<?php
require_once __DIR__ . '/../config/db.php';

// <<Entity>> CompanyInfo
class CompanyInfo {

    // CompanySearch(str companyName): List<CompanyInfo>
    // Partial match on the company name; an empty name returns every company.
    // Empty array when nothing matches. Read-only.
    public function CompanySearch($companyName) {
        $conn = (new Database())->connect();
        $escaped = str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $companyName);
        $like = '%' . $escaped . '%';

        $stmt = $conn->prepare(
            "SELECT company_id, company_name, address
             FROM companies
             WHERE company_name LIKE ?
             ORDER BY company_name
             LIMIT 200");
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $res = $stmt->get_result();

        $rows = array();
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    // getCompanyInfo(int companyId): CompanyInfo | null
    public function getCompanyInfo($companyId) {
        $conn = (new Database())->connect();
        $id = (int)$companyId;
        $stmt = $conn->prepare(
            "SELECT company_id, company_name, address, contact_no, created_at
             FROM companies
             WHERE company_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row : null;
    }

    // ---------- Delete Company Information ----------

    // getInfoDetails(infoID): company row | null
    public function getInfoDetails($infoID) {
        $conn = (new Database())->connect();
        $id = (int)$infoID;
        $stmt = $conn->prepare("SELECT company_id, company_name FROM companies WHERE company_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row : null;
    }

    // removeInfo(infoID): bool   (true only if exactly one company was deleted)
    public function removeInfo($infoID) {
        try {
            $conn = (new Database())->connect();
            $id = (int)$infoID;
            $stmt = $conn->prepare("DELETE FROM companies WHERE company_id = ?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                return false;
            }
            return $stmt->affected_rows === 1;
        } catch (Exception $e) {
            return false;
        }
    }
}
