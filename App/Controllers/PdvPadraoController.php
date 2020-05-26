<?php
namespace App\Controllers;
use System\Controller\Controller;
use System\Post\Post;
use System\Get\Get;
use System\Session\Session;

use App\Models\ConfigPdv;
use App\Models\Venda;
use App\Models\Usuario;
use App\Models\MeioPagamento;

use App\Repositories\VendasDoDiaRepository;

use App\Rules\AcessoAoTipoDePdv;

class PdvPadraoController extends Controller
{
	protected $post;
	protected $get;
	protected $layout;
	protected $idCliente;
	protected $idUsuario;
	protected $idPerfilUsuarioLogado;

	public function __construct()
	{
		parent::__construct();
		$this->layout = 'default';

		$this->post = new Post();
		$this->get = new Get();
		$this->idCliente = Session::get('idCliente');
		$this->idUsuario = Session::get('idUsuario');
		$this->idPerfilUsuarioLogado = Session::get('idPerfil');
        
        $acessoAoTipoDePdv = new AcessoAoTipoDePdv();
		$acessoAoTipoDePdv->validate();
	}

	public function index()
	{
		$vendasDoDiaRepository = new VendasDoDiaRepository();

		$vendasGeralDoDia = $vendasDoDiaRepository->vendasGeralDoDia($this->idCliente, 10);
		$totalVendasNoDia = $vendasDoDiaRepository->totalVendasNoDia($this->idCliente);

		$totalValorVendaPorMeioDePagamentoNoDia = $vendasDoDiaRepository->totalValorVendaPorMeioDePagamentoNoDia(
			$this->idCliente
		);

		$totalVendaNoDiaAnterior = $vendasDoDiaRepository->totalVendasNoDia(
			$this->idCliente, decrementDaysFromDate(1)
		);

		$meioPagamanto = new MeioPagamento();
		$meiosPagamentos = $meioPagamanto->all();

		$usuario = new Usuario();
		$usuarios = $usuario->usuarios($this->idCliente, $this->idPerfilUsuarioLogado);

		$this->view('pdv/padrao', $this->layout, 
			compact(
				'vendasGeralDoDia', 
				'meiosPagamentos',
				'usuarios',
				'totalVendasNoDia',
				'totalValorVendaPorMeioDePagamentoNoDia',
				'totalVendaNoDiaAnterior'
			));
	}

	public function save()
	{
		if ($this->post->hasPost()) {
			$dados = (array) $this->post->data();
			$dados['id_cliente'] = $this->idCliente;
            
            # Preparar o valor da moeda para ser armazenado
		    $dados['valor'] = formataValorMoedaParaGravacao($dados['valor']);
		    
		    try {
		    	$venda = new Venda();
				$venda->save($dados);
				return $this->get->redirectTo("pdvPadrao/index");

			} catch(\Exception $e) { 
			    dd($e->getMessage());
		    }
	    }
	}
}