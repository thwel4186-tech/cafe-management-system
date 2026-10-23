<?php
namespace Tests\Feature;
use Tests\TestCase;
class SmokeTest extends TestCase
{
    public function test_api_home_page_is_available(): void
    {
        $this->get('/')->assertOk()->assertJsonPath('application', 'Cafe Management API');
    }
}
