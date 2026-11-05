<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('SiMarket')
            ->assertSee('Produk terbaru')
            ->assertSee('Belum ada produk yang tayang');
    }

    public function test_homepage_assets_use_the_forwarded_https_host(): void
    {
        $response = $this->withHeaders([
            'Host' => 'simarket.test',
            'X-Forwarded-Host' => 'forwarded-simarket.test',
            'X-Forwarded-Proto' => 'https',
        ])->get('/');

        $manifest = json_decode(
            file_get_contents(public_path('build/manifest.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $response
            ->assertOk()
            ->assertSee(
                'href="https://forwarded-simarket.test/build/'.$manifest['resources/css/app.css']['file'].'" ',
                false,
            )
            ->assertSee(
                'src="https://forwarded-simarket.test/build/'.$manifest['resources/js/app.js']['file'].'" ',
                false,
            );
    }
}
