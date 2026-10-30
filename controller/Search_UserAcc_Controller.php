<?php
require_once __DIR__ . '/../entity/UserAcc.php';

// <<Control>> Search_UserAcc_Controller
class Search_UserAcc_Controller {

    // SearchAccount(str criteria): userList
    public function SearchAccount($criteria) {
        $user = new UserAcc();
        return $user->SearchUser($criteria);       // SearchUser(criteria)
    }
}
