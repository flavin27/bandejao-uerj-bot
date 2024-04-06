<?php

namespace Tests\Feature\services;

use App\Services\UerjService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UerjServiceTest extends TestCase
{
    private UerjService $uerjService;

    public function setUp(): void {
        parent::setUp();
        $this->uerjService = new UerjService();
    }


    public function test_can_scrape_data() {
        $response = $this->uerjService->scrape_data();
        $this->assertIsArray($response);
        $this->assertArrayHasKey('Segunda', $response);
        $this->assertArrayHasKey('Terça', $response);
        $this->assertArrayHasKey('Quarta', $response);
        $this->assertArrayHasKey('Quinta', $response);
        $this->assertArrayHasKey('Sexta', $response);
        $this->assertCount(5, $response);
    }

    public function test_can_fetch_content() {
        $response = $this->uerjService::fetchContent();
        $this->assertIsString($response);
    }

    public function test_can_normalize_string() {
        $response = $this->uerjService::normalizeString('ÃÉÀÍÓÇÊ');
        $this->assertIsString($response);
        $this->assertEquals('ãéàíóçê', $response);
    }

    public function teste_can_extract_dia() {
        $response = $this->uerjService::extractDia('Segunda', 'Segunda');
        $this->assertEquals('Segunda', $response);
    }

    public function teste_can_extract_data() {
        $response = $this->uerjService::extractData('01 Jan', '01 Jan');
        $this->assertEquals('01 Jan', $response);
    }





}
