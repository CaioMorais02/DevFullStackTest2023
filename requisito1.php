<?php

$itens = [
    ['id_item' => 1, 'descricao' => 'item1', 'valor' => 4],
    ['id_item' => 2, 'descricao' => 'item2', 'valor' => 3],
    ['id_item' => 3, 'descricao' => 'item3', 'valor' => 2],
    ['id_item' => 4, 'descricao' => 'item4', 'valor' => 4],
    ['id_item' => 5, 'descricao' => 'item5', 'valor' => 3],
    ['id_item' => 6, 'descricao' => 'item6', 'valor' => 2],
    ['id_item' => 7, 'descricao' => 'item7', 'valor' => 4],
    ['id_item' => 8, 'descricao' => 'item8', 'valor' => 3],
    ['id_item' => 9, 'descricao' => 'item9', 'valor' => 2],
];

$lancamentos = [
    ['id_lancamento' => 11, 'id_atendimento' => 101, 'id_item' => 1, 'valor' => 4],
    ['id_lancamento' => 12, 'id_atendimento' => 102, 'id_item' => 2, 'valor' => 3],
    ['id_lancamento' => 13, 'id_atendimento' => 103, 'id_item' => 3, 'valor' => 2],
    ['id_lancamento' => 14, 'id_atendimento' => 104, 'id_item' => 4, 'valor' => 4],
    ['id_lancamento' => 15, 'id_atendimento' => 105, 'id_item' => 5, 'valor' => 3],
];

$atendimentos = [
    ['id_atendimento' => 101, 'data_atendimento' => '04292023', 'id_pessoa' => 1001],
    ['id_atendimento' => 102, 'data_atendimento' => '05292023', 'id_pessoa' => 1002],
    ['id_atendimento' => 103, 'data_atendimento' => '06292023', 'id_pessoa' => 1003],
    ['id_atendimento' => 104, 'data_atendimento' => '07292023', 'id_pessoa' => 1004],
    ['id_atendimento' => 105, 'data_atendimento' => '08292023', 'id_pessoa' => 1005],
];

//Não sei como acessar os campos dos arrays, assim não consigo fazer as comparações,
//então deixei o print dos 5 mais utilizados

echo '<pre>';
var_dump($itens[0]);
echo '</pre>';

echo '<pre>';
var_dump($itens[1]);
echo '</pre>';

echo '<pre>';
var_dump($itens[2]);
echo '</pre>';

echo '<pre>';
var_dump($itens[3]);
echo '</pre>';

echo '<pre>';
var_dump($itens[4]);
echo '</pre>';
