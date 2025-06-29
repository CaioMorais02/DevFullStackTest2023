<?php

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
    $err = curl_error($curl_itens);

    curl_close($curl_itens);

    //Verifica se houve erro na requisição cURL
    if ($err) {
        echo "<h2>Erro na Requisição da API de Itens</h2>";
        echo "<p>Não foi possível buscar os itens. Erro cURL: " . htmlspecialchars($err) . "</p>";
        exit();
    }

    //Decodifica a resposta JSON para um array PHP
    $itens_array = json_decode($itens_response, true);

    //Verifica se a decodificação JSON foi bem-sucedida e se é um array
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($itens_array)) {
        echo "<h2>Erro ao Processar Dados dos Itens</h2>";
        echo "<p>A resposta da API de itens não pôde ser decodificada ou não está no formato esperado.</p>";
        echo "<p>Resposta bruta recebida: " . htmlspecialchars($itens_response) . "</p>";
        exit(); // Encerra o script
    }

    //Exibe a lista de itens
    echo "<h2>Itens Cadastrados no Sistema</h2>";

    if (empty($itens_array)) {
        echo "<p>Nenhum item encontrado no sistema.</p>";
    } else {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<thead><tr><th>ID do Item</th><th>Descrição do Item</th><th>Valor Unitário</th></tr></thead>";
        echo "<tbody>";

        foreach ($itens_array as $item) {
            $id_item = $item['id_item'] ?? 'N/A';
            $descricao = $item['descricao'] ?? 'Descrição Indisponível';
            $valor = $item['valor'] ?? '0.00';

            echo "<tr>";
            echo "<td>" . htmlspecialchars($id_item) . "</td>";
            echo "<td>" . htmlspecialchars($descricao) . "</td>";
            echo "<td>R$ " . number_format((float)$valor, 2, ',', '.') . "</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
    }

?>