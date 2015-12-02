<?php 

use Sari\Provider\RouterProvider;
use Sari\Provider\DataBaseProvider;
use Sari\Provider\FormProvider;
use Sari\Provider\SessionProvider;
use Sari\Provider\RequestProvider;
use Sari\Provider\ResponseProvider;

use Src\Traits\AlertsTrait;

/**
* IndexController
*/
class IndexController extends Controller implements ControllerInterface
{
	use AlertsTrait;

	function __construct()
	{
		$this->router = new RouterProvider;
		$this->dbh = new DataBaseProvider;
		$this->session = new SessionProvider;
		$this->title('', '');
	}

	public function main()
	{

		$response = new ResponseProvider;

		$form = new FormProvider();
		$form->startForm(array('class' => 'ls-form ls-login-form'), $data, 'index/auth', 'POST');
		$form->add('email', 'email',
			array('placeholder' => 'Email', 'class' => 'ls-login-bg-user ls-field-lg', 'autofocus' => 'autofocus', 'required' => 'required'),
			array('class' => 'ls-label')
		);
		$form->add('password', 'password',
			array('placeholder' => 'Senha', 'class' => 'ls-login-bg-password ls-field-lg', 'required' => 'required'),
			array('class' => 'ls-label')
		);

		$form->endForm();

		include('templates/index/login.php');
	}

	public function auth()
	{
		$request = new RequestProvider;
		$response = new ResponseProvider;
		$data = $request->post();

		if(is_null($data['email'])){
			return $response->redirect('index/main/warning/email-required');
		}
		
		$user = $this->dbh->findOneBy('users', 'email', $data['email']);

		if (is_null($user)) {
			return $response->redirect('index/main/warning/user-no-exist');
		}

		$hash = md5($data['password'] . $user['salt']);

		$sql = "SELECT users.name, users.hash, users.role, roles.dashboard_url FROM users
		LEFT JOIN roles ON users.role = roles.id
		WHERE users.status = 1 AND password = '".$hash."' LIMIT 1
		";

		$auth = $this->dbh->listAll($sql);
		$auth = $auth[0];

		if (is_null($auth)) {
			return $response->redirect('index/main/danger/error');
		}

		$this->session->destroy('user');
		$this->session->set('user', 0, array(
			'token' => $auth['hash'],
			'auth' => true
			)
		);

		return $response->redirect($auth['dashboard_url']);
	}

}