<?php

namespace App\Actions;

use App\DataTransferObjects\TransactionData;
use App\Models\Category;
use App\Models\Transaction;
use App\Repositories\Contracts\CategoryRepository;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CategorizeTransaction
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    ) {}

    public function execute(TransactionData $transaction): ?Category
    {
        $categories = $this->categoryRepository->actives();

        try {
            $response = Ollama::agent(<<<'EOL'
                    Você é um classificador especializado em categorizar transações financeiras.
                    Sempre escolha exatamente uma categoria da lista fornecida e siga estritamente as instruções do usuário.
                EOL)
                ->prompt($this->prompt($transaction, $categories))
                ->model('llama3.2:3b')
                ->options([
                    'temperature' => 0,
                    'num_predict' => 1,
                    'num_ctx' => 1024,
                    'max_tokens' => 1
                ])
                ->ask();

            return $categories
                ->firstWhere('id', (int) $response['response'] ?? 8);
        } catch (\Exception $e) {
            Log::error('Error categorizing transaction', [
                'transaction_id' => $transaction->id,
                'description' => $transaction->description,
                'error' => $e->getMessage()
            ]);

            return $categories
                ->firstWhere('description', 'Outros');
        }
    }

    private function prompt(TransactionData $transaction, Collection $categories): string
    {
        $amount = Money::ofMinor($transaction->amount, currency: 'BRL', roundingMode: RoundingMode::HALF_EVEN);
        $direction = $transaction->direction->isOutflow()
            ? 'Saída'
            : 'Entrada';

        $categories = $categories
            ->map(fn (Category $category): string => "$category->id = $category->description")
            ->join(', ');

        return <<<EOL
            Você é um classificador de transações financeiras brasileiras.

            Sua tarefa:
            - Analise cuidadosamente a transação fornecida.
            - Compare com os exemplos e mapeie para UMA das categorias oficiais.
            - Sempre responda apenas com o **id numérico** da categoria.
            - Se não houver correspondência clara, use "8" (Outros).

            ---

            ### Categorias disponíveis:
            $categories

            ---

            ### Definições resumidas das categorias
            - **Alimentação**: restaurantes, bares, lanchonetes, fast food, compras em supermercados e atacarejos.
            - **Transporte**: combustível, transporte público, aplicativos (Uber, 99), pedágios, estacionamento, financiamento ou manutenção de veículo.
            - **Saúde**: farmácias, drogarias, planos de saúde, consultas, exames, hospitais.
            - **Entretenimento**: streaming (Netflix, Spotify), cinema, shows, festas, lazer.
            - **Educação**: escolas, faculdades, cursos, livros didáticos.
            - **Casa**: despesas de moradia como luz, água, gás, internet, aluguel, condomínio, reparos domésticos.
            - **Serviços**: serviços profissionais ou empresariais, impostos, taxas (ex: MEI, Simples Nacional), softwares, mensalidades diversas.
            - **Outros**: qualquer gasto que não se encaixe claramente nas categorias acima.

            ---

            ### Exemplos de classificação

            Transação:
            - Descrição: "INT /RGE SU 91000874"
            - Valor: R\$ 236,48
            - Tipo: Saída

            Resposta:
            6

            ---

            Transação:
            - Descrição: "INT /SIMPLES NACIONA 070"
            - Valor: R\$ 80,90
            - Tipo: Saída

            Resposta:
            7

            ---

            Transação:
            - Descrição: "PAGTO FINANC VEIC 02/60"
            - Valor: R\$ 502,26
            - Tipo: Saída

            Resposta:
            2

            ---

            Transação:
            - Descrição: "Andreazza Supermercado"
            - Valor: R\$ 70,62
            - Tipo: Saída

            Resposta:
            1

            ---

            Transação:
            - Descrição: "Farmacia Preço Popular"
            - Valor: R\$ 74,30
            - Tipo: Saída

            Resposta:
            3

            ---

            ### Agora classifique esta transação:

            - Descrição: $transaction->description
            - Valor: $amount
            - Tipo: $direction

            Responda somente com o **id numérico** da categoria, sem explicações, sem markdown, sem texto adicional.
        EOL;
    }
}
