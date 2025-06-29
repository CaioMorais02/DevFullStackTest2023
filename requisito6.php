<?php

$mapa_itens = []; // Mapa mais detalhado para JS com valor unitário

//Obter Pacientes
$curl_pessoas = curl_init();
curl_setopt_array($curl_pessoas, [
    CURLOPT_URL => "https://comercial.medlynx.com.br/api_devtests2024_1/api/pessoas",
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_FOLLOWLOCATION => TRUE,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET"
]);
$pessoas_response = curl_exec($curl_pessoas);
$err_pessoas = curl_error($curl_pessoas);
curl_close($curl_pessoas);

$pessoas_array = [];

if (!$err_pessoas) {
    $decoded_pessoas = json_decode($pessoas_response, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_pessoas)) {
        $pessoas_array = $decoded_pessoas;
    } else {
        error_log("Erro ao decodificar pessoas da API: " . $pessoas_response);
    }
} else {
    error_log("Erro cURL ao buscar pessoas: " . $err_pessoas);
}

//Obter Itens
$curl_itens = curl_init();
curl_setopt_array($curl_itens, [
    CURLOPT_URL => "https://comercial.medlynx.com.br/api_devtests2024_1/api/itens",
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_FOLLOWLOCATION => TRUE,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET"
]);
$itens_response = curl_exec($curl_itens);
$err_itens = curl_error($curl_itens);
curl_close($curl_itens);

$itens_array = [];

if (!$err_itens) {
    
    $decoded_itens = json_decode($itens_response, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_itens)) {
        $itens_array = $decoded_itens;
        
        // Criar um mapa de itens para fácil acesso no JavaScript
        foreach ($itens_array as $item) {
            $mapa_itens[$item['id_item']] = [
                'descricao' => $item['descricao'],
                'valor' => (float)$item['valor'] // Garante que o valor é float
            ];
        }
    } else {
        error_log("Erro ao decodificar itens da API: " . $itens_response);
    }
} else {
    error_log("Erro cURL ao buscar itens: " . $err_itens);
}

$mensagem_status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_paciente = $_POST['id_paciente'] ?? null;
    
    // O JavaScript enviará os itens_selecionados como um JSON string
    $itens_selecionados_json = $_POST['itens_selecionados'] ?? '[]';
    $itens_para_lancar = json_decode($itens_selecionados_json, true);

    if (empty($id_paciente) || !is_array($itens_para_lancar) || empty($itens_para_lancar)) {
        $mensagem_status = "<p style='color: red;'>Erro: Por favor, selecione um paciente e adicione pelo menos um item.</p>";
    } else {
        
        // Iniciar o atendimento
        $atendimento = [
            'data_atendimento' => date('Y-m-d H:i:s'), // Data e hora atuais
            'id_pessoa' => $id_paciente
        ];

        $curl_atendimento_post = curl_init();
        curl_setopt_array($curl_atendimento_post, [
            CURLOPT_URL => "https://comercial.medlynx.com.br/api_devtests2024_1/api/atendimentos/new",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($atendimento),
        ]);

        $response_atendimento = curl_exec($curl_atendimento_post);
        $err_atendimento = curl_error($curl_atendimento_post);
        curl_close($curl_atendimento_post);

        if ($err_atendimento) {
            $mensagem_status = "<p style='color: red;'>Erro ao criar atendimento: " . htmlspecialchars($err_atendimento) . "</p>";
        } else {
            $atendimento_resposta_decoded = json_decode($response_atendimento, true);
            $novo_id_atendimento = $atendimento_resposta_decoded['id_atendimento'] ?? null;

            if ($novo_id_atendimento) {
                $mensagem_status = "<p style='color: green;'>Atendimento e todos os lançamentos registrados com sucesso! ID do Atendimento: <strong>" . htmlspecialchars($novo_id_atendimento) . "</strong></p>";
            } else {
                $mensagem_status = "<p style='color: red;'>Erro: Não foi possível obter o ID do novo atendimento. Resposta: " . htmlspecialchars($response_atendimento) . "</p>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Novo Atendimento</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); max-width: 800px; margin: auto; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        select, input[type="number"], textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .message { margin-top: 15px; padding: 10px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .item-row { display: flex; align-items: center; margin-bottom: 10px; }
        .item-row > * { margin-right: 10px; }
        .item-row input[type="number"] { width: 80px; }
        #itens-adicionados-lista { border: 1px solid #eee; padding: 10px; margin-top: 20px; border-radius: 4px; }
        #itens-adicionados-lista table { width: 100%; border-collapse: collapse; }
        #itens-adicionados-lista th, #itens-adicionados-lista td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        #itens-adicionados-lista th { background-color: #f2f2f2; }
        .total-section { text-align: right; margin-top: 15px; font-size: 1.2em; font-weight: bold; }
        .remove-item-btn { background-color: #dc3545; color: white; border: none; padding: 5px 8px; border-radius: 4px; cursor: pointer; font-size: 0.8em; }
        .remove-item-btn:hover { background-color: #c82333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registrar Novo Atendimento</h1>

        <?php if (!empty($mensagem_status)): ?>
            <div class="message <?php echo strpos($mensagem_status, 'color: green;') !== false ? 'success' : (strpos($mensagem_status, 'color: red;') !== false ? 'error' : 'warning'); ?>">
                <?php echo $mensagem_status; ?>
            </div>
        <?php endif; ?>

        <form id="atendimentoForm" action="" method="POST">
            <label for="id_paciente">Selecione o Paciente:</label>
            <select id="id_paciente" name="id_paciente" required>
                <option value="">-- Selecione um paciente --</option>
                <?php if (!empty($pessoas_array)): ?>
                    <?php foreach ($pessoas_array as $pessoa): ?>
                        <option value="<?php echo htmlspecialchars($pessoa['id_pessoa']); ?>">
                            ID: <?php echo htmlspecialchars($pessoa['id_pessoa']); ?> -
                            Nome: <?php echo htmlspecialchars($pessoa['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>Nenhum paciente disponível</option>
                <?php endif; ?>
            </select>

            <hr>

            <h2>Adicionar Itens ao Atendimento</h2>
            <div class="item-row">
                <div style="flex-grow: 1;">
                    <label for="item_selecionar">Item:</label>
                    <select id="item_selecionar">
                        <option value="">-- Selecione um item --</option>
                        <?php if (!empty($itens_array)): ?>
                            <?php foreach ($itens_array as $item): ?>
                                <option value="<?php echo htmlspecialchars($item['id_item']); ?>"
                                        data-descricao="<?php echo htmlspecialchars($item['descricao']); ?>"
                                        data-valor="<?php echo htmlspecialchars($item['valor']); ?>">
                                    <?php echo htmlspecialchars($item['descricao']); ?> (R$ <?php echo number_format((float)$item['valor'], 2, ',', '.'); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>Nenhum item disponível</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <label for="quantidade_item">Quantidade:</label>
                    <input type="number" id="quantidade_item" value="1" min="1" step="any" onfocus="this.select()">
                </div>
                <button type="button" id="adicionar_item_btn">Adicionar Item</button>
            </div>

            <h3>Itens do Atendimento:</h3>
            <div id="itens-adicionados-lista">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descrição</th>
                            <th>Qtd</th>
                            <th>Valor Unit.</th>
                            <th>Subtotal</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="itens_tabela_corpo">
                        <tr><td colspan="6" style="text-align: center;">Nenhum item adicionado ainda.</td></tr>
                    </tbody>
                </table>
                <div class="total-section">
                    Total do Atendimento: <span id="total_atendimento">R$ 0,00</span>
                </div>
            </div>

            <input type="hidden" name="itens_selecionados" id="itens_selecionados_hidden">

            <button type="submit" style="margin-top: 20px;">Finalizar Atendimento</button>
        </form>
    </div>

    <script>
        //Mapeamento de itens carregado do PHP para o JavaScript
        const itensData = <?php echo json_encode($mapa_itens); ?>;

        //Array para salvar os itens que serão enviados
        let itensAdicionados = [];

        const itemSelecionar = document.getElementById('item_selecionar');
        const quantidadeItem = document.getElementById('quantidade_item');
        const adicionarItemBtn = document.getElementById('adicionar_item_btn');
        const itensTabelaCorpo = document.getElementById('itens_tabela_corpo');
        const totalAtendimentoSpan = document.getElementById('total_atendimento');
        const itensSelecionadosHidden = document.getElementById('itens_selecionados_hidden');
        const atendimentoForm = document.getElementById('atendimentoForm');

        // Função para atualizar a tabela de itens e o total
        function atualizarListaItens() {
            itensTabelaCorpo.innerHTML = '';
            let totalGeral = 0;

            if (itensAdicionados.length === 0) {
                itensTabelaCorpo.innerHTML = '<tr><td colspan="6" style="text-align: center;">Nenhum item adicionado ainda.</td></tr>';
            } else {
                itensAdicionados.forEach((item, index) => {
                    const valorUnitario = parseFloat(itensData[item.id_item].valor);
                    const quantidade = parseFloat(item.quantidade);
                    const subtotal = valorUnitario * quantidade;
                    totalGeral += subtotal;

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.id_item}</td>
                        <td>${itensData[item.id_item].descricao}</td>
                        <td>${quantidade.toFixed(2)}</td>
                        <td>R$ ${valorUnitario.toFixed(2).replace('.', ',')}</td>
                        <td>R$ ${subtotal.toFixed(2).replace('.', ',')}</td>
                        <td><button type="button" class="remove-item-btn" data-index="${index}">Remover</button></td>
                    `;
                    itensTabelaCorpo.appendChild(row);
                });
            }
            totalAtendimentoSpan.textContent = `R$ ${totalGeral.toFixed(2).replace('.', ',')}`;

            // Atualiza o campo oculto com os dados JSON para o PHP
            itensSelecionadosHidden.value = JSON.stringify(itensAdicionados);
        }

        // Adiciona um item à lista
        adicionarItemBtn.addEventListener('click', () => {
            const idItem = itemSelecionar.value;
            let quantidade = parseFloat(quantidadeItem.value);

            if (!idItem || isNaN(quantidade) || quantidade <= 0) {
                alert('Por favor, selecione um item e insira uma quantidade válida.');
                return;
            }

            // Verifica se o item já foi adicionado
            const itemExistenteIndex = itensAdicionados.findIndex(item => item.id_item === idItem);

            // Se o item já existe, atualiza a quantidade
            if (itemExistenteIndex > -1) {
                itensAdicionados[itemExistenteIndex].quantidade = (parseFloat(itensAdicionados[itemExistenteIndex].quantidade) + quantidade).toFixed(2);
            } 
            
            // Adiciona o novo item
            else {
                itensAdicionados.push({
                    id_item: idItem,
                    quantidade: quantidade.toFixed(2)
                });
            }

            atualizarListaItens();
            itemSelecionar.value = ''; // Limpa a seleção do item
            quantidadeItem.value = 1; // Reseta a quantidade
        });

        // Remove um item da lista
        itensTabelaCorpo.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-item-btn')) {
                const indexParaRemover = parseInt(event.target.dataset.index);
                itensAdicionados.splice(indexParaRemover, 1); // Remove o item do array
                atualizarListaItens(); // Atualiza a tabela
            }
        });

        // Validação final antes de enviar o formulário
        atendimentoForm.addEventListener('submit', (event) => {
            if (itensAdicionados.length === 0) {
                alert('Por favor, adicione pelo menos um item ao atendimento.');
                event.preventDefault();
            }
        });

        // Chama a função ao carregar a página para garantir que o total inicial seja 0
        document.addEventListener('DOMContentLoaded', atualizarListaItens);
    </script>
</body>
</html>