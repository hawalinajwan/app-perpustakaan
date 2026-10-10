<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'password' => 'password']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/books')->assertRedirect('/login');
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Login Perpustakaan');
    }

    public function test_admin_can_login_and_reach_books(): void
    {
        $admin = $this->user('admin');

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/books');

        $this->assertAuthenticatedAs($admin);
        $this->get('/books')->assertOk();
    }

    public function test_wrong_password_is_rejected_without_leaking_email(): void
    {
        $user = $this->user('petugas');

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_petugas_gets_403_on_categories(): void
    {
        $this->actingAs($this->user('petugas'))->get('/categories')->assertForbidden();
    }

    public function test_admin_can_open_categories(): void
    {
        $this->actingAs($this->user('admin'))->get('/categories')->assertOk();
    }

    public function test_logout_ends_session(): void
    {
        $user = $this->user('petugas');

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
        $this->get('/books')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_from_profil(): void
    {
        $this->get('/profil')->assertRedirect('/login');
    }

    public function test_profil_shows_logged_in_identity(): void
    {
        $petugas = $this->user('petugas');

        $this->actingAs($petugas)->get('/profil')
            ->assertOk()
            ->assertSee($petugas->name)
            ->assertSee($petugas->email)
            ->assertSee('Petugas');
    }

    public function test_password_can_be_changed_and_old_one_stops_working(): void
    {
        $petugas = $this->user('petugas');

        $this->actingAs($petugas)->put('/profil/password', [
            'password_lama' => 'password',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/profil')->assertSessionHas('success');

        $this->assertTrue(Hash::check('rahasia123', $petugas->fresh()->password));

        $this->post('/logout');

        $this->post('/login', ['email' => $petugas->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->post('/login', ['email' => $petugas->email, 'password' => 'rahasia123'])
            ->assertRedirect('/books');
    }

    public function test_password_change_rejects_wrong_old_password(): void
    {
        $petugas = $this->user('petugas');

        $this->actingAs($petugas)
            ->from('/profil')
            ->put('/profil/password', [
                'password_lama' => 'salah',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ])
            ->assertRedirect('/profil')
            ->assertSessionHasErrors('password_lama');

        $this->assertTrue(Hash::check('password', $petugas->fresh()->password));
    }

    public function test_password_change_enforces_min_length_and_confirmation(): void
    {
        $petugas = $this->user('petugas');

        $this->actingAs($petugas)->put('/profil/password', [
            'password_lama' => 'password',
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors('password');

        $this->actingAs($petugas)->put('/profil/password', [
            'password_lama' => 'password',
            'password' => 'rahasia123',
            'password_confirmation' => 'beda12345',
        ])->assertSessionHasErrors('password');
    }

    public function test_loan_is_stored_with_logged_in_user(): void
    {
        $petugas = $this->user('petugas');
        $member = Member::create([
            'nama' => 'Budi', 'nim' => '3125600111', 'email' => 'budi@pens.ac.id',
            'nomor_telepon' => '0812', 'alamat' => 'Surabaya',
        ]);
        $book = Book::create([
            'judul' => 'Buku Uji', 'penulis' => 'Penulis', 'penerbit' => 'Penerbit',
            'tahun_terbit' => 2026, 'category_id' => Category::firstOrCreate(['nama_kategori' => 'Uji'])->id,
        ]);

        $this->actingAs($petugas)->get('/loans/create')
            ->assertOk()
            ->assertSee($petugas->name)
            ->assertDontSee('name="user_id"', escape: false);

        $this->actingAs($petugas)->post('/loans', [
            'member_id' => $member->id,
            'tanggal_pinjam' => '2026-10-08',
            'tanggal_kembali' => '2026-10-15',
            'book_ids' => [$book->id],
        ])->assertRedirect('/loans');

        $this->assertDatabaseHas('loans', [
            'member_id' => $member->id,
            'user_id' => $petugas->id,
        ]);
    }
}
