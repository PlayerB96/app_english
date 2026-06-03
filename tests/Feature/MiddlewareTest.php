<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\MobileUser;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    public function test_administrator_can_access_admin_panel(): void
    {
        $admin = MobileUser::fromSpRow((object) [
            'c_usua_codi' => 'ADMIN01',
            'c_usua_nomb' => 'Administrador',
            'c_codi_empr' => '00001',
            'c_codi_sucu' => '01',
            'n_tcam_vent' => 3.75,
            'c_role_codi' => '00001',
            'c_role_nomb' => 'Administrador',
            'c_nomb_sucu' => 'Sucursal Centro',
            'c_sigl_sucu' => 'CTR',
        ], UserRole::Administrator);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
    }

    public function test_cashier_cannot_access_admin_panel(): void
    {
        $cashier = MobileUser::fromSpRow((object) [
            'c_usua_codi' => 'CAJERO01',
            'c_usua_nomb' => 'Cajero',
            'c_codi_empr' => '00001',
            'c_codi_sucu' => '01',
            'n_tcam_vent' => 3.75,
            'c_role_codi' => '00005',
            'c_role_nomb' => 'Caja Rapida',
            'c_nomb_sucu' => 'Sucursal Centro',
            'c_sigl_sucu' => 'CTR',
        ]);

        $response = $this->actingAs($cashier)->get('/admin');

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_admin_panel(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('login'));
    }

    public function test_mobile_user_exposes_expected_role(): void
    {
        $cashier = MobileUser::fromSpRow((object) [
            'c_usua_codi' => 'CAJERO01',
            'c_usua_nomb' => 'Cajero',
            'c_codi_empr' => '00001',
            'c_codi_sucu' => '01',
            'n_tcam_vent' => 3.75,
            'c_role_codi' => '00005',
            'c_role_nomb' => 'Caja Rapida',
            'c_nomb_sucu' => 'Sucursal Centro',
            'c_sigl_sucu' => 'CTR',
        ]);

        $admin = MobileUser::fromSpRow((object) [
            'c_usua_codi' => 'ADMIN01',
            'c_usua_nomb' => 'Administrador',
            'c_codi_empr' => '00001',
            'c_codi_sucu' => '01',
            'n_tcam_vent' => 3.75,
            'c_role_codi' => '00001',
            'c_role_nomb' => 'Administrador',
            'c_nomb_sucu' => 'Sucursal Centro',
            'c_sigl_sucu' => 'CTR',
        ], UserRole::Administrator);

        $this->assertSame(UserRole::Cashier, $cashier->role);
        $this->assertSame(UserRole::Administrator, $admin->role);
    }
}
