<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bibliotecario\SalvarLeitorRequest;
use App\Models\Leitor;
use App\Models\Multa;
use App\Services\ServicoDeLeitor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * RF14, RF15: cadastro de leitores e consulta da situação de cada um.
 */
class LeitorController extends Controller
{
    public function __construct(private ServicoDeLeitor $servicoDeLeitor) {}

    public function index(Request $request): View
    {
        $busca = trim((string) $request->query('busca'));

        $leitores = Leitor::query()
            ->with('usuario')
            ->when($busca !== '', fn (Builder $consulta) => $consulta->whereHas('usuario', fn (Builder $usuario) => $usuario
                ->whereLike('nome', '%'.$busca.'%')
                ->orWhere('cpf', preg_replace('/\D/', '', $busca))))
            ->join('usuarios', 'usuarios.id', '=', 'leitores.usuario_id')
            ->orderBy('usuarios.nome')
            ->select('leitores.*')
            ->paginate(15)
            ->withQueryString();

        return view('bibliotecario.leitores.index', ['leitores' => $leitores, 'busca' => $busca]);
    }

    public function create(): View
    {
        return view('bibliotecario.leitores.form', ['leitor' => null]);
    }

    public function store(SalvarLeitorRequest $request): RedirectResponse
    {
        $leitor = $this->servicoDeLeitor->cadastrar($request->validated());

        return $this->voltarOu($request, route('bibliotecario.leitores.show', $leitor), 'Leitor cadastrado.');
    }

    public function show(Leitor $leitor): View
    {
        $leitor->load('usuario.endereco');
        $comObra = ['exemplar.edicao.obra'];

        return view('bibliotecario.leitores.show', [
            'leitor' => $leitor,
            'bloqueado' => $leitor->estaBloqueado(),
            'emprestimos' => $leitor->emprestimos()->with([...$comObra, 'renovacoes', 'multa'])->latest('id')->get(),
            'reservas' => $leitor->reservas()->with($comObra)->latest('data_hora_reserva')->get(),
            'multas' => Multa::query()
                ->whereHas('emprestimo', fn ($consulta) => $consulta->where('leitor_id', $leitor->usuario_id))
                ->with('emprestimo.exemplar.edicao.obra')
                ->latest('criado_em')
                ->get(),
        ]);
    }

    public function edit(Leitor $leitor): View
    {
        return view('bibliotecario.leitores.form', ['leitor' => $leitor->load('usuario.endereco')]);
    }

    public function update(SalvarLeitorRequest $request, Leitor $leitor): RedirectResponse
    {
        $this->servicoDeLeitor->atualizar($leitor, $request->validated());

        return redirect()->route('bibliotecario.leitores.show', $leitor)->with('sucesso', 'Leitor atualizado.');
    }
}
