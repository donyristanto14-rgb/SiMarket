<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['auth', 'role:admin'])->get('/_tests/admin-only', fn () => response()->noContent());
        Route::middleware(['auth', 'role:student'])->get('/_tests/student-only', fn () => response()->noContent());
    }

    public function test_registration_always_creates_a_student_and_hashes_the_password(): void
    {
        $password = Str::random(20);

        $response = $this->post(route('register'), [
            'name' => 'Siswa Demo',
            'email' => ' SISWA@EXAMPLE.TEST ',
            'password' => $password,
            'password_confirmation' => $password,
            'role' => User::ROLE_ADMIN,
        ]);

        $user = User::query()->where('email', 'siswa@example.test')->firstOrFail();

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame(User::ROLE_STUDENT, $user->role);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    public function test_students_can_log_in_and_log_out(): void
    {
        $password = Str::random(20);
        $user = User::factory()->create([
            'email' => 'siswa@example.test',
            'password' => $password,
        ]);

        $this->post(route('login'), [
            'email' => ' SISWA@EXAMPLE.TEST ',
            'password' => $password,
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_invalid_credentials_do_not_authenticate_the_user(): void
    {
        User::factory()->create(['email' => 'siswa@example.test']);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'siswa@example.test',
                'password' => Str::random(20),
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_role_middleware_protects_admin_and_student_routes(): void
    {
        $this->get('/_tests/admin-only')->assertRedirect(route('login'));

        $student = User::factory()->create();
        $this->actingAs($student)->get('/_tests/admin-only')->assertForbidden();
        $this->actingAs($student)->get('/_tests/student-only')->assertNoContent();

        $admin = User::factory()->create();
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

        $this->actingAs($admin)->get('/_tests/admin-only')->assertNoContent();
        $this->actingAs($admin)->get('/_tests/student-only')->assertForbidden();
    }

    public function test_login_and_registration_forms_render(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk ke SiMarket');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Buat akun siswa');
    }
}
