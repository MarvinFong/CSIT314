<?php
require_once __DIR__ . '/../config/db.php';

// <<Entity>> ID_CI  (Interior Designer - Company Information)
class ID_CI {

    // SaveCompany(str companyname, str address, str contact_no, str ownerEmail): bool
    // ownerEmail = the logged-in designer; stored as created_by.
    public function SaveCompany($companyname, $address, $contact_no, $ownerEmail) {
        try {
            $conn = (new Database())->connect();
            $stmt = $conn->prepare(
                "INSERT INTO companies (company_name, address, contact_no, created_by)
                 SELECT ?, ?, ?, user_id FROM user_accounts WHERE email = ?");
            $stmt->bind_param("ssss", $companyname, $address, $contact_no, $ownerEmail);
            if (!$stmt->execute()) {
                return false;
            }
            return $stmt->affected_rows === 1;     // 0 rows = owner not found
        } catch (Exception $e) {
            return false;
        }
    }
}
