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

    //Datas para teste
    $data_inicio_pesquisa = '1990-01-01';
    $data_fim_pesquisa = '2001-01-01';

    //Decodificando dados JSON em arrays associativos
    $atendimentos_array = json_decode($atendimentos_response, true);
    $lancamentos_array = json_decode($lancamentos_response, true);
    $itens_array = json_decode($itens_response, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($atendimentos_array) || !is_array($lancamentos_array) || !is_array($itens_array)) {
        die("Erro ao decodificar os dados da API provenientes do JSON.");
    }

    //Mapeamento de itens
    $mapa_itens = [];
    foreach ($itens_array as $item) {
        $mapa_itens[$item['id_item']] = [
            'descricao' => $item['descricao'],
            'valor_unitario' => (float) $item['valor']
        ];
    }

    //Mapeamento de lançamentos por atendimento
    $mapa_lancamento_por_atendimento = [];
    foreach ($lancamentos_array as $lancamento) {
        $mapa_lancamento_por_atendimento[$lancamento['id_atendimento']][] = $lancamento;
    }

    //Filtragem de atendimentos por período
    $relatorio_atendimento = [];
    foreach ($atendimentos_array as $atendimento) {
        $data_atendimento  = $atendimento['data_atendimento'];

        if ($data_atendimento >= $data_inicio_pesquisa && $data_atendimento <= $data_fim_pesquisa) {
            $id_atendimento = $atendimento['id_atendimento'];
            $valor_total_atendimento = 0;

            $lancamentos_do_atendimento = $mapa_lancamento_por_atendimento[$id_atendimento] ?? [];

            foreach ($lancamentos_do_atendimento as $lancamento) {
                $id_item = $lancamento['id_item'];
                $quantidade = (float) $lancamento['quantidade'];

                $valor_unitario_item = $mapa_itens[$id_item]['valor_unitario'] ?? 0;

                $valor_total_lancamento = $quantidade * $valor_unitario_item;
                $valor_total_atendimento += $valor_total_lancamento; 
            }

            $relatorio_atendimentos[] = [
                'id_atendimento' => $id_atendimento,
                'data_atendimento' => $data_atendimento,
                'id_pessoa' => $atendimento['id_pessoa'],
                'valor_total' => $valor_total_atendimento
            ];
        }
    }

    //Apresentar relatório
    echo "<h2>Relatório de Atendimentos por Período</h2>";
    echo "<p>Período de: <strong>" . htmlspecialchars($data_inicio_pesquisa) . "</strong> a <strong>" . htmlspecialchars($data_fim_pesquisa) . "</strong></p>";

    if (empty($relatorio_atendimentos)) {
        echo "<p>Nenhum atendimento encontrado para o período especificado.</p>";
    } else {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<thead><tr><th>ID Atendimento</th><th>Data Atendimento</th><th>ID Pessoa</th><th>Valor Total</th></tr></thead>";
        echo "<tbody>";

        foreach ($relatorio_atendimentos as $atendimento_relatorio) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($atendimento_relatorio['id_atendimento']) . "</td>";
            echo "<td>" . htmlspecialchars($atendimento_relatorio['data_atendimento']) . "</td>";
            echo "<td>" . htmlspecialchars($atendimento_relatorio['id_pessoa']) . "</td>";
            // Formata o valor monetário para exibição no Brasil
            echo "<td>R$ " . number_format($atendimento_relatorio['valor_total'], 2, ',', '.') . "</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
    }

?>