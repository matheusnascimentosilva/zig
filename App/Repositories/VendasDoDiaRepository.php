<?php 
namespace App\Repositories;
use App\Models\Venda;

class VendasDoDiaRepository
{
	protected $venda;

	public function __construct()
	{
		$venda = new Venda();
		$this->venda = $venda;
	}

	public function vendasGeralDoDia($idCliente, $quantidade = false)
    {
        $data = date('Y-m-d');
        $queryContidade = false;
        if ($quantidade) {
            $queryContidade = "LIMIT {$quantidade}";
        }
        
    	return $this->venda->query(
    		"SELECT 
            vendas.id, vendas.valor, DATE_FORMAT(vendas.created_at, '%H:%i') AS data,
            meios_pagamentos.legenda, usuarios.id, usuarios.nome, usuarios.imagem 
            FROM vendas INNER JOIN usuarios
            ON vendas.id_usuario =  usuarios.id
            INNER JOIN meios_pagamentos ON vendas.id_meio_pagamento = meios_pagamentos.id
            WHERE vendas.id_cliente = {$idCliente} AND DATE(vendas.created_at) = '{$data}'
            ORDER BY vendas.created_at DESC {$queryContidade}"
    	);
    }

    public function totalVendasNoDia($idCliente, $data = false)
    {
        if ( ! $data) {
            $data = date('Y-m-d');
        }

        $query = $this->venda->query(
            "SELECT SUM(valor) AS totalVendas FROM vendas WHERE id_cliente = {$idCliente}
            AND DATE(created_at) = '{$data}'"
        );

        return $query[0]->totalVendas;
    }

    public function totalValorVendaPorMeioDePagamentoNoDia($idCliente, $idMeioPagamento = false, $data = false)
    {
        if ( ! $data) {
            $data = date('Y-m-d');
        }

        if ($idMeioPagamento) {
            $query = $this->venda->query(
                "SELECT meios_pagamentos.legenda, SUM(vendas.valor) AS totalVendas FROM vendas 
                INNER JOIN meios_pagamentos ON vendas.id_meio_pagamento = meios_pagamentos.id
                WHERE vendas.id_cliente = {$idCliente} AND vendas.id_meio_pagamento = {$idMeioPagamento}
                AND DATE(vendas.created_at) = '{$data}'"
           );
           
           return $query[0];
        }

        $query = $this->venda->query(
            "SELECT meios_pagamentos.id AS idMeioPagamento, 
            meios_pagamentos.legenda, SUM(vendas.valor) AS totalVendas FROM vendas 
            INNER JOIN meios_pagamentos ON vendas.id_meio_pagamento = meios_pagamentos.id
            WHERE vendas.id_cliente = {$idCliente}
            AND DATE(vendas.created_at) = '{$data}'
            GROUP BY vendas.id_meio_pagamento"
        );

        return $query;
    }
}