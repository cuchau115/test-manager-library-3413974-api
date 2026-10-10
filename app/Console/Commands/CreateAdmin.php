<?php

namespace App\Console\Commands;

use App\Application\Ports\PasswordHasher;
use App\Application\Ports\UserRepository;
use App\Domain\Enum\Role;
use App\Domain\Model\User;
use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';
    protected $description = 'Crear el administrador inicial si no existe';

    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordHasher $passwordHasher,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $username = config('library.admin.username');
        $password = config('library.admin.password');

        if (!$username || !$password) {
            $this->warn('ADMIN_USERNAME o ADMIN_PASSWORD no configurados. Saltando creación de admin.');
            return Command::SUCCESS;
        }

        try {
            $normalized = User::normalizeUsername($username);
            $existing = $this->users->findByUsername($normalized);
            if ($existing !== null && $existing->role() === Role::Admin) {
                $this->info('El administrador inicial ya está configurado.');

                return Command::SUCCESS;
            }

            $admin = User::create(
                $existing?->id() ?? $this->users->nextIdentity(),
                $normalized,
                $this->passwordHasher->hash($password),
                Role::Admin,
            );
            $this->users->save($admin);
            $this->info($existing === null
                ? 'Administrador inicial creado correctamente.'
                : 'La cuenta configurada se ha actualizado a administrador.');
        } catch (\Throwable) {
            $this->error('Error al crear el administrador inicial.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}