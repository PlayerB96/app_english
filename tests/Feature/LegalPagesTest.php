<?php

namespace Tests\Feature;

use App\Models\CustomerComplaint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_are_publicly_accessible(): void
    {
        $this->get('/legal/terminos')->assertOk();
        $this->get('/legal/privacidad')->assertOk();
        $this->get('/legal/devoluciones')->assertOk();
        $this->get('/legal/proveedor')->assertOk();
        $this->get('/legal/reclamaciones')->assertOk();
    }

    public function test_complaint_submission_creates_record_with_number(): void
    {
        $response = $this->post('/legal/reclamaciones', [
            'consumer_name' => 'Juan Pérez',
            'document_type' => 'dni',
            'document_number' => '12345678',
            'address' => 'Av. Example 123, Lima',
            'email' => 'juan@example.com',
            'phone' => '999888777',
            'item_type' => 'servicio',
            'amount' => '30.00',
            'complaint_type' => 'reclamo',
            'description' => 'No se acreditó el poder tras enviar el comprobante de pago.',
            'order_reference' => 'Compra 300 poder',
        ]);

        $response->assertRedirect();

        $complaint = CustomerComplaint::query()->first();

        $this->assertNotNull($complaint);
        $this->assertStringStartsWith('REC-'.now()->format('Y').'-', $complaint->complaint_number);
        $this->assertSame('pending', $complaint->status);
        $this->assertSame('reclamo', $complaint->complaint_type);
    }

    public function test_complaint_submission_validates_required_fields(): void
    {
        $this->from('/legal/reclamaciones')
            ->post('/legal/reclamaciones', [])
            ->assertRedirect('/legal/reclamaciones')
            ->assertSessionHasErrors([
                'consumer_name',
                'document_type',
                'document_number',
                'address',
                'email',
                'item_type',
                'complaint_type',
                'description',
            ]);

        $this->assertDatabaseCount('customer_complaints', 0);
    }
}
