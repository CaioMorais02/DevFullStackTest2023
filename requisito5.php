<?php

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

    $atendimentos_response = curl_exec($curl_atendimentos);
    $err_atendimentos = curl_error($curl_atendimentos);
    curl_close($curl_atendimentos);

    if ($err_atendimentos) {
        echo "<p style='color: red;'>Erro ao carregar atendimentos: " . htmlspecialchars($err_atendimentos) . "</p>";
    } else {
        $decoded_atendimentos = json_decode($atendimentos_response, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_atendimentos)) {
            $atendimentos_array = $decoded_atendimentos;
        } else {
            echo "<p style='color: red;'>Erro ao decodificar atendimentos da API. Resposta: " . htmlspecialchars($atendimentos_response) . "</p>";
        }
    }

    $mensagem_status = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //Coleta as informações do formulário
        $id_atendimento_selecionado = $_POST['id_atendimento'] ?? '';
        $descricao_evolucao = $_POST['descricao_evolucao'] ?? '';

        if (empty($id_atendimento_selecionado) || empty($descricao_evolucao)) {
            $mensagem_status = "<p style='color: red;'>Erro: Por favor, selecione um atendimento e digite a descrição da evolução.</p>";
        } else {
            $data_evolucao = date('Y-m-d H:i:s');

            $evolucao = [
                'id_atendimento' => $id_atendimento_selecionado,
                'data' => $data_evolucao,
                'descricao' => $descricao_evolucao
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://comercial.medlynx.com.br/api_devtests2024_1/api/evolucao/new",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => array('Content-Type:application/json'),
                CURLOPT_POSTFIELDS => json_encode($evolucao),
            ]);

            $response_post = curl_exec($curl);
            $err_post = curl_error($curl);
            curl_close($curl);

            if ($err_post) {
                $mensagem_status = "<p style='color: red;'>Erro ao enviar evolução: " . htmlspecialchars($err_post) . "</p>";
            } else {
                $api_response_decoded = json_decode($response_post, true);

                if (isset($api_response_decoded['success']) && $api_response_decoded['success'] === true) {
                    $mensagem_status = "<p style='color: green;'>Evolução registrada com sucesso!</p>";
                } elseif (isset($api_response_decoded['message'])) {
                    $mensagem_status = "<p style='color: orange;'>Resposta da API: " . htmlspecialchars($api_response_decoded['message']) . "</p>";
                } else {
                    $mensagem_status = "<p style='color: orange;'>Evolução enviada. Resposta da API: " . htmlspecialchars($response_post) . "</p>";
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
    <title>Registrar nova evolução</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); max-width: 500px; margin: auto; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        select, textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .message { margin-top: 15px; padding: 10px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Registrar Nova Evolução</h1>

        <?php if (!empty($mensagem_status)): ?>
            <div class="message <?php echo strpos($mensagem_status, 'color: green;') !== false ? 'success' : (strpos($mensagem_status, 'color: red;') !== false ? 'error' : 'warning'); ?>">
                <?php echo $mensagem_status; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <label for="id_atendimento">Selecione o Atendimento:</label>
            <select id="id_atendimento" name="id_atendimento" required>
                <option value="">-- Selecione um atendimento --</option>
                <?php if (!empty($atendimentos_array)): ?>
                    <?php foreach ($atendimentos_array as $atendimento): ?>
                        <option value="<?php echo htmlspecialchars($atendimento['id_atendimento']); ?>">
                            ID: <?php echo htmlspecialchars($atendimento['id_atendimento']); ?> -
                            Data: <?php echo htmlspecialchars($atendimento['data_atendimento']); ?> -
                            Pessoa ID: <?php echo htmlspecialchars($atendimento['id_pessoa']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>Nenhum atendimento disponível</option>
                <?php endif; ?>
            </select>

            <label for="descricao_evolucao">Descrição da Evolução:</label>
            <textarea id="descricao_evolucao" name="descricao_evolucao" rows="5" required placeholder="Digite a descrição da evolução aqui..."></textarea>

            <button type="submit">Registrar Evolução</button>
        </form>
    </div>
    
</body>
</html>