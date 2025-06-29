<?php

    //Extraindo dados de atendimentos
    $curl_atendimentos = curl_init();
    curl_setopt_array($curl_atendimentos, [
            CURLOPT_URL => "https://comercial.medlynx.com.br/api_devtests2024_1/api/atendimentos",
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
    ]);

    //Extraindo dados de lançamentos
    $curl_lancamentos = curl_init();
    curl_setopt_array($curl_lancamentos, [
            CURLOPT_URL => "https://comercial.medlynx.com.br/api_devtests2024_1/api/lancamentos",
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
    ]);

    //Extraindo dados de itens
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

    //Executando atendimentos
    $atendimentos_response = curl_exec($curl_atendimentos);
    $err_atendimento = curl_error($curl_atendimentos);

    curl_close($curl_atendimentos);

    //Executando lançamentos
    $lancamentos_response = curl_exec($curl_lancamentos);
    $err_lancamento = curl_error($curl_lancamentos);

    curl_close($curl_lancamentos);

    //Executando itens
    $itens_response = curl_exec($curl_itens);
    $err_itens = curl_error($curl_itens);

    curl_close($curl_itens);
    
    //Decodificar dados do JSON
    $lancamentos_array = json_decode($lancamentos_response, true);
    $itens_array = json_decode($itens_response, true);

    //Verificar se a decodificação está correta
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($lancamentos_array) || !is_array($itens_array)) {
        die("Erro ao decodificar os dados da API provenientes do JSON.");
    }

    //Mapear as descrições dos itens
    $mapa_itens_descricao = [];
    foreach ($itens_array as $item) {
        $mapa_itens_descricao[$item['id_item']] = $item['descricao'];
    }

    //Cálculo de consumo total de cada item
    $consumo_total_por_item = [];
    foreach ($lancamentos_array as $lancamento) {
        $id_item = $lancamento['id_item'];
        $quantidade = (float) $lancamento['quantidade'];

        $consumo_total_por_item[$id_item] = ($consumo_total_por_item[$id_item] ?? 0 ) + $quantidade;
    }

    //Lista para ordenação
    $lista_itens_consumidos = [];
    foreach ($consumo_total_por_item as $id_item => $quantidade_total) {
        $descricao = $mapa_itens_descricao[$id_item] ?? 'Item Desconhecido';
        $lista_itens_consumidos[] = [
            'descricao' => $descricao,
            'quantidade' => $quantidade_total
        ];
    }

    //Ordenar lista do maior para o menor
    usort($lista_itens_consumidos, function($a, $b) {
        return $b['quantidade'] <=> $a['quantidade'];
    });

    $top_5_itens = array_slice($lista_itens_consumidos, 0 ,5);

    //Exibir o resultado
    echo "<h2>Top 5 Itens com Maior Consumo nos Atendimentos</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<thead><tr><th>Descrição do Item</th><th>Quantidade Consumida</th></tr></thead>";
    echo "<tbody>";

    foreach ($top_5_itens as $item) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['descricao']) . "</td>"; // htmlspecialchars para segurança
        echo "<td>" . number_format($item['quantidade'], 2, ',', '.') . "</td>"; // Formata para 2 casas decimais
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
?>