<?php
namespace Src\Traits;

use Sari\Provider\RouterProvider;
use Sari\Provider\DataBaseProvider;
use Sari\Provider\FormProvider;
use Sari\Provider\SessionProvider;
use Sari\Provider\ResponseProvider;

/**
* UserTrait
*/
trait UserTrait 
{

	public function navUserSessionExist()
	{
		$this->router = new RouterProvider;
		$this->dbh = new DataBaseProvider;

		$login = $this->session->get('user', 0);

		$user = $this->dbh->findBy('users', array(
			'hash' => $login['token']
			),1
		);

		$user = $user[0];
		return $user;
	}

	public function checkLogin($url)
	{
		$response = new ResponseProvider;
		$this->dbh = new DataBaseProvider;

		$login = $this->session->get('user', 0);

		$sql = "SELECT roles.privilege, users.hash FROM users
		LEFT JOIN roles ON roles.id = users.role
		WHERE users.status = 1 AND users.hash = :password
		";

		$user = $this->dbh->listAll($sql, array('password' => $login['token']));
		$user = $user[0];

		if (is_null($user) OR !$login['auth']) {
			return $response->redirect('index/main/info/no-session');
		}

		$privileges = explode(',', $user['privilege']);
		
		if (!in_array($url, $privileges)) {
			return $response->redirect('errors/code/no-privilege');
		}
	}
	
}



