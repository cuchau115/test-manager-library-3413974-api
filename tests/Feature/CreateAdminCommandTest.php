<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class CreateAdminCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_configured_bootstrap_user_is_promoted_with_configured_password_idempotently(): void
    {
        $username = 'bootstrap-' . strtolower(Uuid::uuid4()->toString());
        config([
            'library.admin.username' => '  ' . $username . ' ',
            'library.admin.password' => 'configured-bootstrap-password',
        ]);

        $id = (string) Uuid::uuid4();
        DB::table('library.user')->insert([
            'id' => $id,
            'username' => $username,
            'password_hash' => password_hash('old-password', PASSWORD_ARGON2ID),
            'role' => 'librarian',
        ]);

        $this->assertSame(0, Artisan::call('app:create-admin'));
        $promoted = UserModel::findOrFail($id);
        $this->assertSame('admin', $promoted->role);
        $this->assertTrue(password_verify('configured-bootstrap-password', $promoted->password_hash));

        $passwordHash = $promoted->password_hash;
        $this->assertSame(0, Artisan::call('app:create-admin'));
        $promoted->refresh();

        $this->assertSame('admin', $promoted->role);
        $this->assertSame($passwordHash, $promoted->password_hash);
    }

    public function test_command_does_not_promote_a_different_username(): void
    {
        $username = 'bootstrap-' . strtolower(Uuid::uuid4()->toString());
        config([
            'library.admin.username' => $username,
            'library.admin.password' => 'configured-bootstrap-password',
        ]);

        DB::table('library.user')->insert([
            'id' => (string) Uuid::uuid4(),
            'username' => 'another-user',
            'password_hash' => password_hash('user-password', PASSWORD_ARGON2ID),
            'role' => 'librarian',
        ]);

        $this->assertSame(0, Artisan::call('app:create-admin'));
        $this->assertSame('librarian', DB::table('library.user')
            ->where('username', 'another-user')
            ->value('role'));
        $this->assertSame('admin', DB::table('library.user')
            ->where('username', $username)
            ->value('role'));
    }
}
