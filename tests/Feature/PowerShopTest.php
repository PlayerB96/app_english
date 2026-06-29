<?php

namespace Tests\Feature;

use App\Models\PowerPurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PowerShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_learner_can_submit_power_purchase_with_receipt(): void
    {
        Storage::fake('public');

        $learner = User::factory()->learner()->create(['tokens' => 40]);

        $response = $this->actingAs($learner)->post('/power-shop/purchases', [
            'power_amount' => 100,
            'payment_method' => 'yape',
            'receipt' => UploadedFile::fake()->image('comprobante.jpg'),
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('power_purchase_requests', [
            'user_id' => $learner->id,
            'power_amount' => 100,
            'soles_amount' => 10,
            'payment_method' => 'yape',
            'status' => 'pending',
        ]);

        $request = PowerPurchaseRequest::query()->first();
        $this->assertNotNull($request);
        Storage::disk('public')->assertExists($request->receipt_path);
    }

    public function test_power_purchase_rejects_invalid_package(): void
    {
        Storage::fake('public');

        $learner = User::factory()->learner()->create();

        $this->actingAs($learner)
            ->post('/power-shop/purchases', [
                'power_amount' => 250,
                'payment_method' => 'plin',
                'receipt' => UploadedFile::fake()->image('comprobante.png'),
            ])
            ->assertSessionHasErrors('receipt');

        $this->assertDatabaseCount('power_purchase_requests', 0);
    }

    public function test_learner_can_redeem_ln1_code_for_500_power(): void
    {
        $learner = User::factory()->learner()->create(['tokens' => 40]);

        $response = $this->actingAs($learner)->post('/power-shop/redeem', [
            'code' => 'LN1',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(540, $learner->fresh()->tokens);

        $this->assertDatabaseHas('power_code_redemptions', [
            'user_id' => $learner->id,
            'internal_code' => 'LN1',
            'public_code' => 'LN1',
            'power_amount' => 500,
        ]);
    }

    public function test_redeem_rejects_invalid_code(): void
    {
        $learner = User::factory()->learner()->create(['tokens' => 40]);

        $this->actingAs($learner)
            ->from('/dashboard')
            ->post('/power-shop/redeem', ['code' => '999'])
            ->assertRedirect('/dashboard')
            ->assertSessionHasErrors('redeem_code');

        $this->assertSame(40, $learner->fresh()->tokens);
        $this->assertDatabaseCount('power_code_redemptions', 0);
    }

    public function test_redeem_rejects_duplicate_ln1_code(): void
    {
        $learner = User::factory()->learner()->create(['tokens' => 40]);

        $this->actingAs($learner)->post('/power-shop/redeem', ['code' => 'LN1']);

        $this->actingAs($learner)
            ->from('/dashboard')
            ->post('/power-shop/redeem', ['code' => 'ln1'])
            ->assertRedirect('/dashboard')
            ->assertSessionHasErrors('redeem_code');

        $this->assertDatabaseCount('power_code_redemptions', 1);
    }
}
