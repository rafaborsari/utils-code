<?php 

// arquivo de camada de Conexão

session_start();

// É interessante manter os antigos handlers de conexão, para fazer essa migração.

// $con = mysql_connect("localhost", "root", "") or die ("Sem conexão com o servidor");
// $select = mysql_select_db("bd_usuarios") or die("Erro ao acessar");
// $result = mysql_query("SELECT * FROM `USUARIO` WHERE `NOME` = '$login' AND `SENHA`= '$senha'");

try {
	$dbh = new PDO('mysql:host=localhost;dbname=app_rjmid', 'root', 'rafa1991');	
} catch (PDOException $e) {
	echo 'Error!: ' . $e->getMessage() . '<br/>';
    die();
}


// Camada de Persistência POSTs e GETs

// Criar uma camada de validação para esses dados
$login = (string) isset($_POST['login']) ? $_POST['login'] : 'daniel@corretorarjmid.com.br';
$senha = (string) isset($_POST['senha']) ? $_POST['senha'] : '35635b1833b44c6aba47f56da3725874';


$dbh->beginTransaction(); //Importante inciar Transaction isso protege seu SGDB

$sql = (string)
'SELECT 
	id,
	email,
	hash
FROM 
	users
WHERE 
	email = :login 
	AND password = :senha 
LIMIT 1
';

// Nâo se usa * em querys e apenas palavras reservadas do SQL são em Uppercase, tabelas e colunas devem ser lowercase
$stmt = $dbh->prepare($sql);	

// bindParam escapa e protege todos os dados que vão para a query, isso facilita e evita SQL Injection
$stmt->bindParam(':login', $login);
$stmt->bindParam(':senha', $senha);

$stmt->execute();

if ($stmt->rowCount() === 1) { // Verifica se existe o valor e se existir 1 ele autentifica
	// Fetch retorna um array com todas os dados
	$user = $stmt->fetch(PDO::FETCH_NAMED);

	// Sempre persista a sessão do usuário usando arrays com encriptados, isso evita que alguem consiga pegar a informação
	// E se passar por um usuário, use arrays isso aumenta complexidade para se quebrar o Hash

	$_SESSION['token']['email'] = md5($user['email']);
	$_SESSION['token']['hash'] = $user['hash'];
	$_SESSION['token']['id'] = md5($user['id']);

	header('location:site.php');
}else{
	unset ($_SESSION['token']);
	header('location:index.php');
}
