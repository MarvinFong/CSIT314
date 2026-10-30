<?php
require_once __DIR__ . '/../entity/ID_CI.php';

// <<Control>> IDCreate_CI_Controller
class IDCreate_CI_Controller {

    // CreateCompany(str companyname, str address, str contact_no, str ownerEmail): bool
    public function CreateCompany($companyname, $address, $contact_no, $ownerEmail) {
        $company = new ID_CI();
        return $company->SaveCompany($companyname, $address, $contact_no, $ownerEmail);
    }
}
