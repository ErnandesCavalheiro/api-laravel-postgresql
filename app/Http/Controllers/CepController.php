<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Cep;
use Exception;

class CepController extends Controller
{
    /**
     * Retorna todos os CEPS salvos no banco de dados
     * /api/ceps
     */
    public function index()
    {
        try {
            $ceps = Cep::getAll();

            return response()->json($ceps);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Consulta um CEP especifico
     * /api/consultar-cep?cep=
     */
    public function consultAddress(Request $request)
    {
        // Faz as validações para conferir se o CEP é valido
        try {

            //Primeiro verifica se o valor enviado é numerico
            $this->validate($request, [
                'cep' => 'required|numeric'
            ]);

            // Formata o CEP
            $cep = $this->format($request->input('cep'));

            // Verifica se o CEP formatado é válido
            if (!preg_match('/^\d{5}-\d{3}$/', $cep)) {
                return response()->json(['error' => 'CEP inválido'], 400);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'CEP inválido'], 400);
        }

        // Se o CEP for válido faz a procura do endereço
        try {
            // Confirma se o CEP já está salvo no banco de dados
            $confirmCep = Cep::findByCep($cep);

            // Se já estiver salvo retorna o seu valor sem fazer a consulta na API
            if ($confirmCep instanceof Cep) {
                return response()->json($confirmCep);
            }

            // Cria um cliente do guzzle e faz a pesquisa na API
            $client = new Client();
            $response = $client->get("http://viacep.com.br/ws/{$cep}/json/");
            $data = json_decode($response->getBody(), true);

            // Após os dados retornarem cria uma instancia de Cep e salva no banco de dados
            $cepModel = new Cep();
            $cepModel->cep = $data['cep'];
            $cepModel->logradouro = $data['logradouro'];
            $cepModel->bairro = $data['bairro'];
            $cepModel->localidade = $data['localidade'];
            $cepModel->uf = $data['uf'];
            $cepModel->save();

            // Retorna os valores do CEP
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Ocorreu um erro enquanto seu CEP era pesquisado.'
            ], 500);
        }
    }

    /**
     * Formata o CEP
     */
    private function format($cep)
    {
        // Remove caracteres não numéricos
        $cep = preg_replace('/[^0-9]/', '', $cep);

        // Adiciona hífen ao CEP
        $cep = substr($cep, 0, 5) . '-' . substr($cep, 5, 3);

        return $cep;
    }
}
