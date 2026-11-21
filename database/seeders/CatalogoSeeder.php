<?php

namespace Database\Seeders;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Edicao;
use App\Models\Editora;
use App\Models\Exemplar;
use App\Models\Obra;
use Database\Factories\CategoriaFactory;
use Illuminate\Database\Seeder;

/**
 * Acervo fictício: 300 obras (10 delas TCCs sem edição), ~330 edições e
 * ~410 exemplares.
 */
class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = collect(CategoriaFactory::NOMES)->map(fn (string $nome) => Categoria::create(['nome' => $nome]));
        $editoras = Editora::factory()->count(30)->create();
        $autores = Autor::factory()->count(150)->create();

        $obras = Obra::factory()->count(290)->create();
        $trabalhosAcademicos = Obra::factory()->trabalhoAcademico()->count(10)->create();

        foreach ($obras->concat($trabalhosAcademicos) as $obra) {
            $obra->autores()->attach($autores->random(fake()->numberBetween(1, 3))->pluck('id'));
            $obra->categorias()->attach($categorias->random(fake()->numberBetween(0, 3))->pluck('id'));
        }

        // Trabalhos acadêmicos não publicados não têm edição, logo não circulam.
        foreach ($obras as $obra) {
            $editorasDaObra = $editoras->random(fake()->boolean(12) ? 2 : 1);

            foreach ($editorasDaObra as $editora) {
                $edicao = Edicao::factory()->create(['obra_id' => $obra->id, 'editora_id' => $editora->id]);

                Exemplar::factory()->count(fake()->boolean(25) ? 2 : 1)->create(['edicao_id' => $edicao->id]);
            }
        }
    }
}
