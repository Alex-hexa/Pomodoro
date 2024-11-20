<?php
use PHPUnit\Framework\TestCase;

class SessionStorageTest extends TestCase
{
    // Simule une fonction qui représenterait la logique de redirection
    private function simulateAccess($page, $tokenPresent)
    {
        // Simule le comportement de sessionStorage
        $_SESSION['jwtToken'] = $tokenPresent ? 'someToken' : null;

        // Logique de redirection
        if (empty($_SESSION['jwtToken'])) {
            if ($page !== 'login.php' && $page !== 'register.php') {
                return 'login.php'; // Redirection vers login.php
            }
        } else {
            if ($page === 'login.php' || $page === 'register.php') {
                return 'index.php'; // Redirection vers index.php
            }
        }

        return $page; // Pas de redirection
    }

    public function testRedirectsToLoginIfTokenIsAbsentOnNonAuthPages()
    {
        $result = $this->simulateAccess('index.php', false);
        $this->assertEquals('login.php', $result, "Should redirect to login.php when token is absent on non-auth page.");
    }

    public function testAccessToLoginShouldNotRedirect()
    {
        $result = $this->simulateAccess('login.php', false);
        $this->assertEquals('login.php', $result, "Should stay on login.php when token is absent.");
    }

    public function testRedirectsToIndexIfTokenIsPresentOnLogin()
    {
        $result = $this->simulateAccess('login.php', true);
        $this->assertEquals('index.php', $result, "Should redirect to index.php when token is present on login.php.");
    }

    /* public function testAccessToDashboardShouldNotRedirectIfTokenIsPresent()
    {
        $result = $this->simulateAccess('dashboard.php', true);
        $this->assertEquals('dashboard.php', $result, "Should stay on dashboard.php when token is present.");
    } */
}
?>