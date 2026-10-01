<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that the login page directly redirects to SSO authorization.
     */
    public function test_the_login_page_redirects_to_sso_authorization(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(302);
        $response->assertRedirectContains('/oauth/authorize');
        $this->assertNotEmpty(session('sso_state'));
    }

    /**
     * Test that unauthenticated users are redirected to login.
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Test that accessing via LAN IP dynamically redirects to SSO with LAN host and correct redirect_uri.
     */
    public function test_login_from_lan_ip_dynamically_adapts_sso_host_and_redirect_uri(): void
    {
        $response = $this->get('http://10.24.7.207/login');

        $response->assertStatus(302);
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('http://10.24.7.207/sso/public/oauth/authorize', $location);
        $this->assertStringContainsString('redirect_uri=' . urlencode('http://10.24.7.207/si-kep/public/auth/sso/callback'), $location);
    }
}
