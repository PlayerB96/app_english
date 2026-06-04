<?php

namespace Tests\Feature;

use App\Auth\MobileUserProvider;
use App\Models\MobileUser;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('login');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function postLogin(array $data): TestResponse
    {
        return $this->withoutMiddleware(ThrottleRequests::class)
            ->from('/login')
            ->postWithCsrf('/login', $data);
    }

    /**
     * @return list<object>
     */
    private function validSpRows(string $roleCode = '00005'): array
    {
        return [(object) [
            'l_exis_usua' => 1,
            'c_usua_codi' => 'CAJERO01',
            'c_usua_nomb' => 'Cajero Demo',
            'c_codi_empr' => '00001',
            'c_codi_sucu' => '01',
            'n_tcam_vent' => 3.75,
            'c_role_codi' => $roleCode,
            'c_role_nomb' => 'Caja Rapida',
            'c_nomb_sucu' => 'Sucursal Centro',
            'c_sigl_sucu' => 'CTR',
        ]];
    }

    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
    }

    public function test_user_can_login_with_valid_credentials_and_allowed_role(): void
    {
        $this->mock(AuthRepositoryInterface::class, function ($mock): void {
            $mock->shouldReceive('validateCredentials')
                ->with('CAJERO01', 'clave123')
                ->once()
                ->andReturn($this->validSpRows());
        });

        $response = $this->postLogin([
            'username' => 'CAJERO01',
            'password' => 'clave123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertInstanceOf(MobileUser::class, $user);
        $this->assertSame('CAJERO01', $user->code);
        $this->assertSame('00005', $user->roleCode);
        $this->assertTrue($user->isCashier());
    }

    public function test_administrator_can_login_with_admin_role_code(): void
    {
        $this->mock(AuthRepositoryInterface::class, function ($mock): void {
            $mock->shouldReceive('validateCredentials')
                ->with('ADMIN01', 'clave123')
                ->once()
                ->andReturn($this->validSpRows('00001'));
        });

        $response = $this->postLogin([
            'username' => 'ADMIN01',
            'password' => 'clave123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertInstanceOf(MobileUser::class, $user);
        $this->assertSame('00001', $user->roleCode);
        $this->assertTrue($user->isAdministrator());
    }

    public function test_login_prefers_caja_rapida_when_sp_returns_multiple_roles(): void
    {
        $rows = [
            (object) array_merge((array) $this->validSpRows('00001')[0], ['c_role_codi' => '00001', 'c_role_nomb' => 'Administrador']),
            (object) array_merge((array) $this->validSpRows('00005')[0], ['c_role_codi' => '00005', 'c_role_nomb' => 'Caja Rapida']),
        ];

        $this->mock(AuthRepositoryInterface::class, function ($mock) use ($rows): void {
            $mock->shouldReceive('validateCredentials')
                ->with('MIXED01', 'clave123')
                ->once()
                ->andReturn($rows);
        });

        $response = $this->postLogin([
            'username' => 'MIXED01',
            'password' => 'clave123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = auth()->user();
        $this->assertSame('00005', $user->roleCode);
        $this->assertTrue($user->isCashier());
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $this->mock(AuthRepositoryInterface::class, function ($mock): void {
            $mock->shouldReceive('validateCredentials')
                ->with('CAJERO01', 'wrong-password')
                ->once()
                ->andReturn([(object) [
                    'l_exis_usua' => 0,
                    'c_usua_codi' => '',
                    'c_usua_nomb' => '',
                    'c_codi_empr' => '',
                    'c_codi_sucu' => '',
                    'n_tcam_vent' => 0,
                    'c_role_codi' => '',
                    'c_role_nomb' => '',
                    'c_nomb_sucu' => '',
                    'c_sigl_sucu' => '',
                ]]);
        });

        $response = $this->withoutMiddleware(ThrottleRequests::class)
            ->from('/login')
            ->postWithCsrf('/login', [
                'username' => 'CAJERO01',
                'password' => 'wrong-password',
            ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_fails_when_user_has_no_caja_rapida_role(): void
    {
        $rows = $this->validSpRows('00039');

        $this->mock(AuthRepositoryInterface::class, function ($mock) use ($rows): void {
            $mock->shouldReceive('validateCredentials')
                ->with('CAJERO01', 'clave123')
                ->once()
                ->andReturn($rows);
        });

        $response = $this->postLogin([
            'username' => 'CAJERO01',
            'password' => 'clave123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = MobileUser::fromSpRow((object) [
            'c_usua_codi' => 'CAJERO01',
            'c_usua_nomb' => 'Cajero Demo',
            'c_codi_empr' => '00001',
            'c_codi_sucu' => '01',
            'n_tcam_vent' => 3.75,
            'c_role_codi' => '00005',
            'c_role_nomb' => 'Caja Rapida',
            'c_nomb_sucu' => 'Sucursal Centro',
            'c_sigl_sucu' => 'CTR',
        ]);

        session([MobileUserProvider::SESSION_KEY => $user->toSessionArray()]);

        $response = $this->actingAs($user)->postWithCsrf('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_login_sanitizes_username_input(): void
    {
        $this->mock(AuthRepositoryInterface::class, function ($mock): void {
            $mock->shouldReceive('validateCredentials')
                ->with('CAJERO01', 'clave123')
                ->once()
                ->andReturn($this->validSpRows());
        });

        $response = $this->postLogin([
            'username' => '  CAJERO01  ',
            'password' => 'clave123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }
}
