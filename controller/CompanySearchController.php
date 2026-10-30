<?php
require_once __DIR__ . '/../entity/CompanyInfo.php';

// <<Control>> CompanySearchController
class CompanySearchController {

    // CompanySearch(str companyName): List<CompanyInfo>
    public function CompanySearch($companyName) {
        $company = new CompanyInfo();
        return $company->CompanySearch($companyName);
    }

    // GetCompanyInfo(int companyId): CompanyInfo | null
    public function GetCompanyInfo($companyId) {
        $company = new CompanyInfo();
        return $company->getCompanyInfo($companyId);
    }
}
