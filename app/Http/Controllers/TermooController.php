<?php

namespace App\Http\Controllers;

use App\Models\Jogo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TermooController extends Controller
{
    private array $palavras = [
        'carro','gatos','lindo','porta','verde',
        'chuva','pedra','livro','campo','noite',
        'forca','barco','bravo','calma','dancu',
        'escola','falar','geral','havia','irmos',
        'jogou','karma','lavar','masse','nobre',
        'obras','pazos','queda','risco','salvo',
        'tarde','ultra','vapor','wanda','xerox',
        'yield','zarpa','abrir','beber','ceder'
    ];

    public function iniciarJogo()
    {
        $palavra = $this->palavras[array_rand($this->palavras)];

        $jogo = Jogo::create([
            'id' => Str::uuid(),
            'palavra' => $palavra,
            'tentativas_restantes' => 6,
            'venceu' => false,
        ]);

        return response()->json([
            'idJogo' => $jogo->id,
            'tamanhoPalavra' => 5,
            'tentativasMaximas' => 6,
        ]);
    }

    public function validarTentativa(Request $request)
    {
        $idJogo = $request->input('idJogo');
        $tentativa = mb_strtolower($request->input('palavra', ''));

        $jogo = Jogo::find($idJogo);

        if (!$jogo) {
            return response()->json(['erro' => 'Jogo não encontrado'], 404);
        }

        if (mb_strlen($tentativa) !== 5) {
            return response()->json([
                'erro' => 'Palavra deve ter 5 letras',
                'palavraValida' => false
            ], 400);
        }

        $secreta = mb_strtolower($jogo->palavra);
        $resultado = $this->avaliarPalavra($tentativa, $secreta);

        $venceu = ($tentativa === $secreta);
        $jogo->tentativas_restantes -= 1;
        $jogo->venceu = $venceu;
        $jogo->save();

        return response()->json([
            'resultado' => $resultado,
            'venceu' => $venceu,
            'tentativasRestantes' => $jogo->tentativas_restantes,
            'palavraValida' => true,
        ]);
    }

    private function avaliarPalavra(string $tentativa, string $secreta): array
    {
        $resultado = [];
        $letrasSecreta = mb_str_split($secreta);
        $letrasTentativa = mb_str_split($tentativa);
        $usadas = array_fill(0, 5, false);

        foreach ($letrasTentativa as $i => $letra) {
            if ($letra === $letrasSecreta[$i]) {
                $resultado[$i] = ['letra' => $letra, 'status' => 'correta'];
                $usadas[$i] = true;
            } else {
                $resultado[$i] = ['letra' => $letra, 'status' => 'ausente'];
            }
        }

        foreach ($letrasTentativa as $i => $letra) {
            if ($resultado[$i]['status'] === 'correta') continue;
            foreach ($letrasSecreta as $j => $ls) {
                if (!$usadas[$j] && $letra === $ls) {
                    $resultado[$i]['status'] = 'presente';
                    $usadas[$j] = true;
                    break;
                }
            }
        }

        return array_values($resultado);
    }
}