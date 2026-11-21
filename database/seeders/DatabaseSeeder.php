<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Base de dados fictícios (RNF08): acervo, usuários e alguns meses de
     * circulação simulada.
     */
    public function run(): void
    {
        $this->call([
            CatalogoSeeder::class,
            UsuarioSeeder::class,
            CirculacaoSeeder::class,
        ]);
    }
}
