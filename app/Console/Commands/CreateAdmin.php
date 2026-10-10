<?php

namespace App\Console\Commands;

use App\Application\Services\CreateLibrarianService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';
    protected $description = 'Crear el administrador inicial si no existe';

    public function __construct(
        private readonly CreateLibrarianService $createLibrarianService,
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
            $this->createLibrarianService->create($username, $password, 'librarian');
            $this->info("Administrador '$username' creado correctamente.");
        } catch (\App\Domain\Exception\DomainError $e) {
            if ($e->code() === 'USERNAME_TAKEN' || $e->code() === 'DUPLICATE') {
                $this->info("Administrador '$username' ya existe.");
            } else {
                $this->error("Error al crear administrador: {$e->getMessage()}");
                return Command::FAILURE;
            }
        } catch (\Throwable $e) {
            Log::error('Error creando administrador', ['error' => $e->getMessage()]);
            $this->error("Error al crear administrador: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}