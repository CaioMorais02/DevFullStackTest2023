<?php

    //Extraindo dados de evolução
    $curl_evolucao = curl_init();
    curl_setopt_array($curl_evolucao, [
            CURLOPT_URL => "https://comercial.medlynx.com.br/api_devtests2024_1/api/evolucao",
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
    ]);

    //Extraindo dados de atendimento
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

    //Extraindo dados de lançamento
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

    //Extraindo dados de pessoas
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

    $evolucao_response = curl_exec($curl_evolucao);
    $atendimentos_response = curl_exec($curl_atendimentos);
    $lancamentos_response = curl_exec($curl_lancamentos);
    $itens_response = curl_exec($curl_itens);
    $pessoas_response = curl_exec($curl_pessoas);

    curl_close($curl_evolucao);
    curl_close($curl_atendimentos);
    curl_close($curl_lancamentos);
    curl_close($curl_itens);
    curl_close($curl_pessoas);

    $evolucao_array = json_decode($evolucao_response, true);
    $atendimentos_array = json_decode($atendimentos_response, true);
    $lancamentos_array = json_decode($lancamentos_response, true);
    $itens_array = json_decode($itens_response, true);
    $pessoas_array = json_decode($pessoas_response, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($evolucao_array) || !is_array($atendimentos_array) || !is_array($lancamentos_array) || !is_array($itens_array) || !is_array($pessoas_array)) {
        die("Erro ao decodificar os dados da API provenientes do JSON!");
    }

    //Mapeamento para acesso de itens
    $mapa_itens = [];
    foreach ($itens_array as $item) {
        $mapa_itens[$item['id_item']] = $item['descricao'];
    }

    //Mapeamento para acessar atendimentos, onde um atendimento terá como chave o id do atendimento, e o valor será id da pessoa e data do atendimento
    $mapa_atendimentos = [];
    foreach ($atendimentos_array as $atendimento) {
        $mapa_atendimentos[$atendimento['id_atendimento']] = [
            'id_pessoa' => $atendimento['id_pessoa'],
            'data_atendimento' => $atendimento['data_atendimento']
        ];
    }

    //Variável para identificar pacientes com reação alérgica grave no ano de 2022
    $pacientes_com_reacao = []; //Será armazenado com key sendo id_pessoa, e valor um array de id_atendimento

    //A string do diagnóstico, com base como está salva na API
    $diagnostico_buscado = "reação alérgica grave";

    foreach ($evolucao_array as $evolucao) {
        $ano_evolucao = date('Y', strtotime($evolucao['data']));

        if ($evolucao['descricao'] === $diagnostico_buscado && $ano_evolucao === '2022') {
            $id_atendimento_evolucao = $evolucao['id_atendimento'];
            $id_pessoa_do_atendimento = $mapa_atendimentos[$id_atendimento_evolucao]['id_pessoa'] ?? NULL;

            if ($id_pessoa_do_atendimento !== NULL) {
                $pacientes_com_reacao[$id_pessoa_do_atendimento][] = $id_atendimento_evolucao;
            }
        }
    }

    //Se nenhum paciente for encontrado
    if (empty($pacientes_com_reacao)) {
        echo "<h2>Investigação Dr. Magnovaldo</h2>";
        echo "<p>Nenhum paciente com 'reação alérgica grave' em 2022 foi encontrado.</p>";
        exit();
    }

    //Coletar todos os medicamentos usados por esses pacientes nos atendimentos
    $medicamentos_por_paciente = [];

    foreach ($pacientes_com_reacao as $id_pessoa => $atendimentos_ids) {
        $medicamentos_do_paciente = [];
        foreach ($lancamentos_array as $lancamento) {
            if (in_array($lancamento['id_atendimento'], $atendimentos_ids)){
                $medicamentos_do_paciente[$lancamento['id_item']] = true;
            }
        }

        $medicamentos_por_paciente[$id_pessoa] = $medicamentos_do_paciente;
    }

    //Variável para encontrar os medicamentos que são comuns a todos os pacientes com: reação alérgica grave
    $medicamentos_comuns = [];

    $primeiro_paciente_id = array_key_first($medicamentos_por_paciente);
    if ($primeiro_paciente_id !== NULL) {
        $medicamentos_do_primeiro = array_keys($medicamentos_por_paciente[$primeiro_paciente_id]);

        foreach ($medicamentos_do_primeiro as $id_item_suspeito) {
            $comum_a_todos = true;
            foreach ($medicamentos_por_paciente as $id_pessoa => $medicamentos_presentes) {
                if (!isset($medicamentos_presentes[$id_item_suspeito])){
                    $comum_a_todos = false;
                    break;
                }
            }

            if ($comum_a_todos) {
                $medicamentos_comuns[] = $id_item_suspeito;
            }
        }
    }

    //Exibir o relatório
    echo "<h2>Investigação Dr. Magnovaldo: Suspeita de Reação Alérgica Grave</h2>";
    echo "<p>Diagnóstico de pesquisa: '<strong>" . htmlspecialchars($diagnostico_buscado) . "</strong>' no ano de <strong>2022</strong>.</p>";
    echo "<hr>";

    echo "<h3>Pacientes Identificados com 'Reação Alérgica Grave':</h3>";
    if (empty($pacientes_com_reacao)) {
        echo "<p>Nenhum paciente encontrado com este diagnóstico no período.</p>";
    } else {
        echo "<ul>";
        foreach ($pacientes_com_reacao as $id_pessoa => $atendimentos_ids) {
            $nome_pessoa = $mapa_pessoas[$id_pessoa] ?? "Paciente Desconhecido (ID: {$id_pessoa})";
            echo "<li><strong>" . htmlspecialchars($nome_pessoa) . "</strong> (ID: {$id_pessoa}) - Atendimentos: " . implode(', ', $atendimentos_ids) . "</li>";
        }
        echo "</ul>";
    }
    echo "<hr>";

    echo "<h3>Medicamento(s) em Comum nos Atendimentos Desses Pacientes:</h3>";
    if (empty($medicamentos_comuns)) {
        echo "<p>Não foi encontrado nenhum medicamento comum a TODOS os pacientes com este diagnóstico.</p>";
    } else {
        echo "<ul>";
        foreach ($medicamentos_comuns as $id_item) {
            $descricao_item = $mapa_itens[$id_item] ?? "Item Desconhecido (ID: {$id_item})";
            echo "<li><strong>" . htmlspecialchars($descricao_item) . "</strong> (ID: {$id_item})</li>";
        }
        echo "</ul>";
    }
?>