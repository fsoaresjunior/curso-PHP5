<?php

function pegarDados($valor){
    $pdo = conectarComPdo();
    try{
        $logar = $pdo->prepare("SELECT * FROM adminitrador WHERE nomd = :nome");
        $logar->bindValue(":nome", $valor);
        $logar->execute();
        
        if($logar->rowCount() == 1):
            return $logar->fetch(PDO::FETCH_ASSOC);
        else:
            throw new Exception("Usuario não encontrado");
        endif;
    } catch (PDOException $e) {
        echo "Erro: ".$e->getMessage();
    }
}


function logarCliente($login, $senha) {
    $pdo = conectarComPdo();
    try {
        $logar = $pdo->prepare("SELECT * FROM treinamento WHERE login = :login AND senha = :senha");
        $logar->bindValue(":login", $login);
        $logar->bindValue(":senha", $senha);
        $logar->execute();
        if ($logar->rowCount() == 1):
            $dados = $logar->fetch(PDO::FETCH_ASSOC);
            $_SESSION['logadoCliente'] = true;
            $_SESSION['cliente'] = $dados['nome'];
            $_SESSION['idCliente'] = $dados['id'];
            header('Location:http://localhost/PHP5/Capitulo%205/Aula3%20-%20Cookies%20e%20Sessoes/logado.php');
        else:
            throw new Exception("Erro ao logar, usuário ou senha invalidos");
        endif;
    } catch (PDOException $e) {
        echo 'Erro: ' . $e->getMessage();
    }
}

function logarSemPDO($login, $senha) {
    $sql = "SELECT * FROM treinamento WHERE login = '$login' AND senha = '$senha'";
    $query = mysql_query($sql);

    if (mysql_num_rows($query) > 0):
        echo "Logado com sucesso";
    else:
        echo "Erro ao logar";
    endif;
}

function registrarVisita($sessao, $cliente) {
    $pdo = conectarComPdo();
    try {
        $pdo->beginTransaction();

        date_default_timezone_set('BRAZIL/EAST');
        $dataAtual = date('Y-m-d H:i:s');
        $validade = date('Y-m-d H:i:s', strtotime('+30seconds'));

        $registrarVisita = $pdo->prepare("SELECT * FROM online WHERE sessao = :sessao");
        $registrarVisita->bindValue(":sessao", $sessao);
        $registrarVisita->execute();

        if ($registrarVisita->rowCount() > 0):
            $atualizaVisita = $pdo->prepare("UPDATE online SET validade = :validade WHERE sessao = :sessao");
            $atualizaVisita->bindValue(":validade", $validade, PDO::PARAM_STR);
            $atualizaVisita->bindValue(":sessao", $sessao, PDO::PARAM_STR);
            $atualizaVisita->execute();
        else:
            $cadastraVisita = $pdo->prepare("INSERT INTO online (cliente, sessao, validade) VALUES (:cliente, :sessao, :validade)");
            $cadastraVisita->bindValue(":cliente", $cliente, PDO::PARAM_INT);
            $cadastraVisita->bindValue(":sessao", $sessao, PDO::PARAM_STR);
            $cadastraVisita->bindValue(":validade", $validade, PDO::PARAM_STR);
            $cadastraVisita->execute();
        endif;
        $pdo->commit();
    } catch (PDOException $e) {
        echo 'Erro: ' . $e->getMessage();
        $pdo->rollBack();
    }
}

function deletaVisita() {
    $pdo = conectarComPdo();
    try {
        date_default_timezone_set('BRAZIL/EAST');
        $dataAtual = date('Y-m-d H:i:s');

        $deletarVisita = $pdo->prepare("DELETE FROM online WHERE validade < :vencimento");
        $deletarVisita->bindValue(":vencimento", $dataAtual, PDO::PARAM_STR);
        $deletarVisita->execute();
    } catch (PDOException $e) {
        echo 'Erro: ' . $e->getMessage();
    }
}

function visitantes() {
    $pdo = conectarComPdo();
    try {
        $listar = $pdo->query('SELECT * FROM online');

        return $listar->rowCount();
    } catch (PDOException $e) {
        echo "Erro " . $e->getMessage();
    }
}

function logOut($sessao) {
    if (isset($_SESSION[$sessao])):
        unset($_SESSION[$sessao]);
        session_destroy();
        header('Location:http://localhost/PHP5/Capitulo%205/Aula3%20-%20Cookies%20e%20Sessoes/sessoes3.php');
    endif;
}

function calculaVencimento($dataInicial, $dataFinal){
    $data1 = strtotime($dataInicial);
    $data2 = strtotime($dataFinal);
    
    $resultado = ($data2 - $data1)/86400;
    
    if($resultado <= 0):
        $validadePlano = "Plano já venceu";
    else:
        $validadePlano = "Faltam ".$resultado." dias para o vencimento de seu plano";
    endif;
    
    return $validadePlano;    
}

function cadastrar(Array $dados) {
    $pdo = conectarComPdo();
	try {
		$cadastrar = $pdo -> prepare('INSERT INTO administrador(nome, data_cadastro)VALUES(:nome, :cadastro)');

		foreach ($dados as $k => $v) :
			$cadastrar -> bindValue(":$k", $v);
		endforeach;
		$cadastrar -> execute();

		if ($cadastrar -> rowCount() == 1) :
			return true;
		else :
			return false;
		endif;

	} catch(PDOException $e) {
		echo "Erro: " . $e -> getMessage();
	}
}

function listarComPdo() {
    $pdo = conectarComPdo();
    try {
        $listar = $pdo->query('SELECT * FROM administrador');
        //$listar->execute();

        if ($listar->rowCount() > 0):
            return $listar->fetchAll(PDO::FETCH_OBJ);
        endif;
    } catch (PDOException $e) {
        echo "Erro " . $e->getMessage();
    }
}