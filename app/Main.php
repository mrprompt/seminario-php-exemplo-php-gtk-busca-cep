<?php
/**
 * Exemplo de Busca de CEP em PHP-GTK para o SeminÃÂ¡rio PHP.
 *
 * License GPL-3.0+
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 * @package BuscaCEP
 */
class Main extends GtkWindow
{
    const APP_NOME = 'Busca CEP';
    
    private $campos = array();
    
    /**
     * Construtor
     *
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->set_title(self::APP_NOME);
        $this->set_default_size(350, 150);
        $this->set_position(Gtk::WIN_POS_CENTER);
        
        $main = new GtkVBox;
        $main->pack_start($this->insereCampo('CEP', 20), false);
        $main->pack_start($this->insereCampo('Logradouro', 20), false);
        $main->pack_start($this->insereCampo('Bairro', 20), false);
        $main->pack_start($this->insereCampo('Localidade / UF', 20), false);
        
        $botaoLimpar = new GtkButton;
        $botaoLimpar->set_label('Limpar');
        $botaoLimpar->connect_simple('clicked', array($this, 'limparClick'));
        
        $botaoBuscar = new GtkButton;
        $botaoBuscar->set_label('Buscar');
        $botaoBuscar->connect_simple('clicked', array($this, 'buscarClick'));
        
        $botaoFechar = new GtkButton;
        $botaoFechar->set_label('Sair');
        $botaoFechar->connect_simple('clicked', array('gtk', 'main_quit'));
        
        $botoes = new GtkHButtonBox;
        $botoes->add($botaoLimpar);
        $botoes->add($botaoBuscar);
        $botoes->add($botaoFechar);
        
        $main->add($botoes);
        
        $this->add($main);
    }
    
    /**
     * Limpa todos os campos
     *
     * @return void
     */
    public function limparClick()
    {
        foreach ($this->campos as $campo)
        {
            $campo->set_text('');
        }
    }
    
    /**
     * Busca pelo CEP
     *
     * @return void
     */
    public function buscarClick()
    {
        $campos = array(
        'cepEntrada' => $this->campos['CEP']->get_text(),
        'tipoCep'    => '',
        'cepTemp'    => '',
        'metodo'     => 'buscarCep',
        );
        
        $url    = 'http://m.correios.com.br/movel/buscaCepConfirma.do';
        $result = $this->post($url, $campos);
        
        // desabilito a checagem de erros, pq o html Ã© externo e jÃ¡ sabe...
        libxml_use_internal_errors(true);
        
        $doc = new DOMDocument();
        $doc->loadHTML($result);
        
        $path   = new DOMXPath($doc);
        $fields = $path->query('//form/div/span');
        
        if (null == $fields->item(1)) {                                      
            $this->limparClick();
            
            $dialog = new GtkMessageDialog(
                null, 
                Gtk::DIALOG_MODAL, 
                Gtk::MESSAGE_ERROR,
                Gtk::BUTTONS_OK,
                'CEP não encontrado.'
            );
            
            $dialog->add_button(Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL);
            $dialog->run();
            $dialog->destroy();
            
            return false;
        }
        
        $this->campos['Logradouro']->set_text(utf8_decode(trim($fields->item(1)->nodeValue)));
        $this->campos['Bairro']->set_text(utf8_decode(trim($fields->item(3)->nodeValue)));
        $this->campos['Localidade / UF']->set_text(utf8_decode(trim($fields->item(5)->nodeValue)));
    }
    
    /**
     * envia um post via socket
     *
     * @return string
     */
    private function post($url, $campos)
    {
        $urlInfo  = parse_url($url);
        $values   = array();
        $referrer = 'http://www.google.com.br/';
        
        foreach ($campos as $key => $value) {
            $values[] = "$key=" . urlencode($value);
        }
        
        $dataString = implode("&", $values);
        
        if (!isset($urlInfo["port"])) {
            $urlInfo["port"]=80;
        }
        
        $request  = null;
        $request .= "POST {$urlInfo["path"]} HTTP/1.1\n";
        $request .= "Host: {$urlInfo["host"]}\n";
        $request .= "Referer: {$referrer}\n";
        $request .= "Content-type: application/x-www-form-urlencoded\n";
        $request .= "Content-length: " . strlen($dataString) . "\n";
        $request .= "Connection: close\n";
        $request .= "\n";
        $request .= $dataString . "\n";
        
        $fp = fsockopen($urlInfo["host"], $urlInfo["port"]);
        
        fputs($fp, $request);
        
        $result = '';
        
        while ( !feof($fp) ) {
            $result .= fgets($fp);
        }
        
        fclose($fp);
        
        return $result;
    }

    /**
     * Cria linha com campos campos do formulÃ¡rio
     *
     * @param string $label
     * @param integer $largura
     * @return GtkHBox
     */
    private function insereCampo($label, $largura = 60, $altura = 20)
    {
        $labelCampo = new GtkLabel;
        $labelCampo->set_text($label);
        $labelCampo->set_size_request(100, $altura);
        
        $inputCampo = new GtkEntry;
        $inputCampo->set_size_request($largura, $altura);
        
        $box = new GtkHBox;
        $box->pack_start($labelCampo, false);
        $box->pack_start($inputCampo, true);
        
        $this->campos[ $label ] = $inputCampo;
        
        return $box;
    }
}