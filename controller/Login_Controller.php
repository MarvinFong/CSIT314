<?php
require_once __DIR__ . '/../entity/UserAcc.php';

// <<Control>> Login_Controller
class Login_Controller {
    private $user;

    public function __construct() {
        $this->user = new UserAcc();
    }

    // FindUser(str email, str pw): bool  -> calls UserAcc.Authenticate()
    public function FindUser(string $email, string $pw): bool {
        return $this->user->Authenticate($email, $pw);
    }

    // Sequence diagram: controller calls UserAcc.getRole() and returns the role
    public function getRole(): string {
        return $this->user->getRole();
    }
}
