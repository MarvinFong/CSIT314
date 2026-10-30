<?php
require_once __DIR__ . '/../entity/CompanyInfo.php';

// <<Control>> Delete_CompanyInfo
class Delete_CompanyInfo {

    // DeleteCompanyInfo(infoID): "success" | "not_found" | "failed"
    public function DeleteCompanyInfo($infoID) {
        $company = new CompanyInfo();

        $details = $company->getInfoDetails($infoID);      // getInfoDetails(infoID)
        if ($details === null) {
            return "not_found";                             // A1
        }
        if ($company->removeInfo($infoID)) {                // removeInfo(infoID)
            return "success";
        }
        return "failed";                                    // A2
    }
}
