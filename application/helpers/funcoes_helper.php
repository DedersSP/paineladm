<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//carrega o modulo de sistema devolvendo a tela solicitada
function load_modulo($modulo=NULL, $tela=NULL, $diretorio='painel'){
	$CI =& get_instance();
	if ($modulo != NULL) {
		return $CI->load->view("$diretorio/$modulo", array('tela' => $tela), TRUE);
	} else {
		return FALSE;
	}
}
	
//seta valores ao array $tema da classe sistema
function set_tema($prop, $valor, $replace=TRUE){
	$CI =& get_instance();
	$CI->load->library('sistema');
	if ($replace) {
		$CI->sistema->tema[$prop] = $valor;
	} else {
		if (!isset($CI->sistema->tema[$prop])) {
			$CI->sistema->tema[$prop] = '';
		}
		$CI->sistema->tema[$prop] .= $valor;
	}	
}

//retorna valores do array $tema da classe sistema
function get_tema(){
	$CI =& get_instance();
	$CI->load->library('sistema');
	return $CI->sistema->tema;
}

//inicializar o painel adm carregando os recursos necessarios
function init_painel(){
	$CI =& get_instance();
	$CI->load->library(array('sistema', 'session', 'form_validation', 'parser'));
	$CI->load->helper(array('form', 'url', 'array', 'text'));
	//carregamento dos models
	$CI->load->model('usuarios_model');
	
	set_tema('titulo_padrao', 'Gerenciamento de Conteudo');
	set_tema('rodape', '<p>&copy; 2013 | Todos os direitos reservados para <a href="http://www.mktreports.com.br">@MKT-Reports</a></p>');
	set_tema('template', 'painel_view');
	set_tema('headerinc', load_css(array('foundation.min', 'app')), FALSE);
	set_tema('headerinc', load_js(array('foundation.min', 'app')), FALSE);
    set_tema('footerinc', '');
    
}

//carrega um template passando o array $tema como parametro
function load_template(){
	$CI =& get_instance();
	$CI->load->library('sistema');
	$CI->parser->parse($CI->sistema->tema['template'], get_tema());
}

//carrega um ou vários arquivos de CSS de uma pasta
function load_css($arquivo=NULL, $pasta='css', $media='all'){
	if ($arquivo != NULL) {
		$CI =& get_instance();
		$CI->load->helper('url');
		$retorno = '';
		if (is_array($arquivo)) {
			foreach ($arquivo as $css){
				$retorno .= '<link rel="stylesheet" type="text/css" href="'.base_url("$pasta/$css.css").'" media="'.$media.'" />';
			}
		}else{
			$retorno = '<link rel="stylesheet" type="text/css" href="'.base_url("$pasta/$arquivo.css").'" media="'.$media.'" />';
		}
	
	}
	return $retorno;
}

//carregar um ou varios JS de uma pasta ou servidor remoto
function load_js($arquivo=NULL, $pasta='js', $remoto=FALSE){
	if ($arquivo != NULL) {
		$CI =& get_instance();
		$CI->load->helper('url');
		$retorno = '';
		if (is_array($arquivo)) {
			foreach ($arquivo as $js){
				if ($remoto) {
					$retorno .= '<script type="text/javascript" src="'.$js.'"></script>';
				} else {
					$retorno .= '<script type="text/javascript" src="'.base_url("$pasta/$js.js").'"></script>';
				}
			}
		}else{
			if ($remoto) {
				$retorno .= '<script type="text/javascript" src="'.$arquivo.'"></script>';
			} else {
				$retorno .= '<script type="text/javascript" src="'.base_url("$pasta/$arquivo.js").'"></script>';
			}
		}
	
	}
	return $retorno;
}

//monstra erros de validação em forms
function erros_validacao(){
	if (validation_errors()) echo '<div class="alert-box alert">'.validation_errors('<p>','</p>').'</div>';
}

//verificar se o usuario esta logado no sistema
function esta_logado($redir=TRUE){
    $CI =& get_instance();
    $CI->load->library('session');
    $user_status = $CI->session->userdata('user_logado');
    if (!isset($user_status) || $user_status != TRUE) {
        //$CI->session->sess_destroy();
        //$CI->session->sess_create();
        if ($redir) {
            $CI->session->set_userdata(array('redir_para'=>current_url()));
            set_msg('errologin','Acesso restrito, faça login antes de prosseguir!','erro');
            redirect('usuarios/login');
        } else {
            return FALSE;
        }        
    } else {
        return TRUE;
    }	
}

//defini uma mensagem para ser exibida na proxima tela carregada.
function set_msg($id='msgerro', $msg=NULL, $tipo='erro'){
    $CI =& get_instance();
    switch ($tipo) {
        case 'erro':
            $CI->session->set_flashdata($id, '<div class="alert-box alert"><p>'.$msg.'</p></div>');
            break;
        case 'sucesso':
            $CI->session->set_flashdata($id, '<div class="alert-box success"><p>'.$msg.'</p></div>');
            break;        
        default:
            $CI->session->set_flashdata($id, '<div class="alert-box"><p>'.$msg.'</p></div>');            
            break;
    }
}

// verifica se existe uma mensagem para ser exibida na tela atual
function get_msg($id, $printar=TRUE){
    $CI =& get_instance();
    if ($CI->session->flashdata($id)) {
        if ($printar) {
            echo $CI->session->flashdata($id);
            return TRUE;
        } else {
            return $CI->session->flashdata($id);
        }
        
    }
    return FALSE;
}

//verifica se o usuarios atual é administrador
function is_admin($set_msg=FALSE){
    $CI =& get_instance();
    $user_admin = $CI->session->userdata('user_admin');
    if (!isset($user_admin) || $user_admin != TRUE) {
        if ($set_msg) {
            set_msg('msgerro','Seu usuário não tem permissão para executar esta operação!','erro');
        }
        return FALSE;
    }else{
        return TRUE;
    }    
    
}
	
//gera um breadcrumb com base no controler atual
function breadcrumb(){
    $CI =& get_instance();
    $CI->load->helper('url');
    $classe = ucfirst($CI->router->class);
    if ($classe == 'Painel') {
        $classe = anchor($CI->router->class, 'Início');
    } else {
        $classe = anchor($CI->router->class, $classe);
    }
    $metodo = ucwords(str_replace('_', ' ', $CI->router->method));
    if ($metodo && $metodo != 'Index') {
        $metodo = " &raquo; ".anchor($CI->router->class."/".$CI->router->method, $metodo);
    } else {
        $metodo = '';
    }
    return '<p>Sua localização: '.anchor('painel', 'Painel').' &raquo; '.$classe.$metodo.'</p>';    
}
