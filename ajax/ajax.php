<?php
include 'conexao.php';
$return = array();
$return['return'] = false;
$request = $_REQUEST;

function enviaEmail($msg, $return, $emails){
    $assunto = "ZeEncontra.com";
    if($emails === ''){
        $emails = "roboredo.bruno@gmail.com,rafaremagalhaes@gmail.com,contato@zeencontra.com";
        $assunto = "Mensagem do Site";
    }
    if($return != ''){
        $msg .= "<br /><br /><br />";
        $msg .= "Return: \r\n";
        foreach($return as $r)
        {
            $msg .= $r."\r\n";
        }
    }
    $msg = wordwrap($msg,70);
    $headers = "MIME-Version: 1.1\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    $headers .= "From: no-reply@zeencontra.com\r\n";
    $headers .= "Return-Path: no-reply@zeencontra.com\r\n";
    $mail = mail($emails, $assunto, utf8_decode($msg), $headers);
    if($mail){
        return true;
    }
}

if($request['form'] == 'form-email'){
    formEmail($request, $return);
} else if($request['form'] == 'form-more'){
    formMore($request, $return);    
} else if($request['form'] == 'form-friend'){
    formFriend($request, $return);
} else {
    enviaEmail("O site está fazendo alguma requisição de um form que não existe usando o arquivo ajax.", $return);
    echo json_encode($return);
    return json_encode($return);
}

function formEmail($request, $return)
{
    $email = $request['email'];
    if(!empty($email)){
        $return['return'] = true; $return['email'] = $email;
        $result = mysql_query("SELECT email FROM emails WHERE email='".$email."'");
		if(!$result){
            enviaEmail("Erro na query que verifica se o email do primeiro form já está salvo no banco.", $return, '');
        }
        $num = mysql_num_rows($result);	
        if($num<=0)
        {
            $id = mysql_insert_id();
            $time = date("Y-m-d H:i:s");
            $sql = mysql_query("INSERT INTO emails (id, email, createdAt) VALUES ('".$id."', '".$email."', '".$time."')") or die(mysql_error());
            if(!$sql){
                enviaEmail("Erro na inserção de dados no primeiro formulário (#form-email).", $return, '');
            }
            $id = mysql_query("SELECT id FROM emails WHERE email='".$email."'");
            $id = mysql_fetch_object($id);
            $id = $id->id;
            $return['id'] = $id;
            enviaEmail("<center><h3>Valeu por se cadastrar!</h3><p>Aguarde por novidades ;)</p></center>", '', $email, '');
            enviaEmail("Mais um cadastro efetuado no ZeEncontra.com. E-mail: ".$email, $return, '');
            echo json_encode($return);
            return json_encode($return);
        } else {
            $id = mysql_query("SELECT id FROM emails WHERE email='".$email."'");
            if(!$id){
                enviaEmail("Erro ao tentar pegar id da tabela emails na function formEmail (primeira função do ajax).", $return, '');
            }
            $id = mysql_fetch_object($id);
            $id = $id->id;
            $return['id'] = $id;
            enviaEmail("<center><h3>Valeu por se cadastrar!</h3><p>Aguarde por novidades ;)</p></center>", '', $email);
            echo json_encode($return);
            return json_encode($return);
        }
    }else{
        enviaEmail("Primeiro formulário (#form-email) aceitou passar campo email vazio.", $return, '');
        echo json_encode($return);
        return json_encode($return);
    }
}

function formMore($request, $return)
{
    $id_email = $request['id_email'];
    $telefone = $request['telefone'];
    $nome = $request['nome'];
    $mensagem = $request['mensagem'];
    if(!empty($telefone) || !empty($id_email)){
        $return['return'] = true;
        $id = mysql_insert_id();
        $time = date("Y-m-d H:i:s");
        $sql = mysql_query("
            INSERT INTO more 
            (id, id_email, nome, tel, mensagem, createdAt)
            VALUES
            ('".$id."', '".$id_email."', '".utf8_encode($nome)."', '".$telefone."', '".utf8_encode($mensagem)."', '".$time."')")
            or die(mysql_error());
        if(!$sql){
            enviaEmail("Erro na inserção de dados no segundo formulário (#form-more).", $return, '');
        }
        echo json_encode($return);
        return json_encode($return);
    }else{
        enviaEmail("Segundo formulário (#form-more) aceitou passar campo telefone vazio OU não conseguiu pegar o id do primeiro form (#form-email)", $return, '');
        echo json_encode($return);
        return json_encode($return);
    }
}

function formFriend($request, $return)
{
    $id_email = $request['id_email'];
    $email = $request['email'];
    if(!empty($email) || !empty($id_email)){
		$return['return'] = true;
		$quem_indicou = mysql_query("SELECT email FROM emails WHERE id='".$id_email."'");
        if(!$quem_indicou){
            enviaEmail("Erro na query que pega o id de quem está indicando.", $return, '');
		}
        $nqi = mysql_num_rows($quem_indicou);
		if($num_qi<=0){
			$msg = "<center><h3>Olá!</h3><p>Você foi indicado pelo email ".$quem_indicou." para conhecer o ZéEncontra.com!</p><p>Acesse <a href='http://www.zeencontra.com' targe='_blank'>CLICANDO AQUI</a> e venda rápido e muito mais!</p></center>";
		} else {
			$msg = "<center><h3>Olá!</h3><p>Você foi indicado pelo email ".$quem_indicou." para conhecer o ZéEncontra.com!</p><p>Acesse <a href='http://www.zeencontra.com' targe='_blank'>CLICANDO AQUI</a> e venda rápido e muito mais!</p></center>";
		}
        $result = mysql_query("SELECT email_amigo FROM friend WHERE email_amigo='".$email."'");
        if(!$result){
            enviaEmail("Erro na query que verifica se o email do form indique já está salvo no banco.", $return, '');
		}
        $num = mysql_num_rows($result);
        if($num<=0)
        {
            $id = mysql_insert_id();
            $time = date("Y-m-d H:i:s");
            $sql = mysql_query("INSERT INTO friend (id, id_email, email_amigo, createdAt) VALUES ('".$id."', '".$id_email."', '".$email."', '".$time."')") or die(mysql_error());
            if(!$sql){
                enviaEmail("Erro na inserção de dados no formulário indique (#form-friend).", $return, '');
            }
		}
            enviaEmail($msg, '', $email);
            enviaEmail("Mais um amigo foi indicado no ZeEncontra.com. E-mail indicado: ".$email, $return, '');
            echo json_encode($return);
            return json_encode($return);
    }else{
        enviaEmail("Formulário indique um amigo (#form-friend) aceitou passar campo email vazio OU não conseguiu pegar o id do form (#form-email)", $return, '');
        echo json_encode($return);
        return json_encode($return);
    }
}
?>