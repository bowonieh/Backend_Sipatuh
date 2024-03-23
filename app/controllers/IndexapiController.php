<?php 
/**
 * Index Page Controller
 * @category  Controller
 */
class IndexApiController extends BaseController{
	function __construct(){
		parent::__construct(); 
		$this->tablename = "user";
	}
	/**
     * Index Action 
     * @return null
     */
	function indexapi(){
		if(user_login_status() == true){
			$this->redirect(HOME_PAGE);
		}
		else{
			$this->render_view("index/index.php");
		}
	}
	function checkLoginState(){
		if(!empty(get_session('user_data'))){
			$state = [
				'status' => true,
			];
		}else{
			$state = [
				'status' => false
			];
		}
		return render_json($state,'error');
	}
	private function login_user($username , $password_text, $rememberme = false){
		$db = $this->GetModel();
		$username = filter_var($username, FILTER_SANITIZE_STRING);
		$db->where("nama", $username)->orWhere("email", $username);
		$tablename = $this->tablename;
		$user = $db->getOne($tablename);
		if(!empty($user)){
			//Verify User Password Text With DB Password Hash Value.
			//Uses PHP password_verify() function with default options
			$password_hash = $user['password'];
			$this->modeldata['password'] = $password_hash; //update the modeldata with the password hash
			if(password_verify($password_text,$password_hash)){
        		unset($user['password']); //Remove user password. No need to store it in the session
				set_session("user_data", $user); // Set active user data in a sessions
				//if Remeber Me, Set Cookie
				if($rememberme == true){
					$sessionkey = time().random_str(20); // Generate a session key for the user
					//Update user session info in database with the session key
					$db->where("id", $user['id']);
					$res = $db->update($tablename, array("login_session_key" => hash_value($sessionkey)));
					if(!empty($res)){
						set_cookie("login_session_key", $sessionkey); // save user login_session_key in a Cookie
						
					}
				}
				else{
					clear_cookie("login_session_key");// Clear any previous set cookie

				}
				$pesan = [
					'success' 		=> true,
					'messages'		=> 'User berhasil login',
					'session'		=> get_session('user_data')
				];
				return render_json($pesan,200);
				/*
				$redirect_url = get_session("login_redirect_url");// Redirect to user active page
				if(!empty($redirect_url)){
					clear_session("login_redirect_url");
					return $this->redirect($redirect_url);
				}
				else{
					return $this->redirect(HOME_PAGE);
				}
				*/
				/** 
				 * Login Berhasil
				 */
				
			}
			else{
				//password is not correct
				//return $this->login_fail("Username or password not correct");
				$pesan = [
					'success' 		=> false,
					'messages'		=> 'Username atau password tidak sesuai'
				];
				return render_json($pesan,401);
			}
		}
		else{
			//user is not registered
			//return $this->login_fail("Username or password not correct");
			$pesan = [
				'success' 		=> false,
				'messages'		=> 'Pengguna Tidak terdaftar'
			];
			return render_json($pesan,401);
		}
		
	}
	/**
     * Display login page with custom message when login fails
     * @return BaseView
     */
	private function login_fail($page_error = null){
		$this->set_page_error($page_error);
		$this->render_view("index/login.php");
	}
	/**
     * Login Action
     * If Not $_POST Request, Display Login Form View
     * @return View
     */
	function login($formdata = null){
		if($formdata){
			$modeldata = $this->modeldata = $formdata;
			$username = trim($modeldata['username']);
			$password = $modeldata['password'];
			$rememberme = (!empty($modeldata['rememberme']) ? $modeldata['rememberme'] : false);
			$this->login_user($username, $password, $rememberme);
		}
		else{
			/*
			$this->set_page_error("Invalid request");
			$this->render_view("index/login.php");
			*/
			$pesan = [
				'status' 	=> false,
				'messages'	=> 'Permintaan tidak valid'
			];

			return render_json($pesan);
		}
	}
	/**
     * Insert new record into the user table
	 * @param $formdata array from $_POST
     * @return BaseView
     */
	
	function apiregister($formdata = null){
		if($formdata){
			$request = $this->request;
			$db = $this->GetModel();
			$tablename = $this->tablename;
			$fields = $this->fields = array("nama","email","password","photo"); //registration fields
			$postdata = $this->format_request_data($formdata);
			$cpassword = $postdata['confirm_password'];
			$password = $postdata['password'];
			if($cpassword != $password){
				return render_json(
					array(
						'message' => "Your password confirmation is not consistent",
					)
				);
			}
			$this->rules_array = array(
				'nama' => 'required',
				'email' => 'required|valid_email',
				'password' => 'required',
			);
			$this->sanitize_array = array(
				'nama' => 'sanitize_string',
				'email' => 'sanitize_string',
			);
			$this->filter_vals = true; //set whether to remove empty fields
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			$password_text = $modeldata['password'];
			//update modeldata with the password hash
			$modeldata['password'] = $this->modeldata['password'] = password_hash($password_text , PASSWORD_DEFAULT);
			//Check if Duplicate Record Already Exit In The Database
			$db->where("nama", $modeldata['nama']);
			if($db->has($tablename)){
				return render_json(
					array(
						'data' => $modeldata['nama'],
						'message' => "Already exist",
					)
				);
			}
			//Check if Duplicate Record Already Exit In The Database
			$db->where("email", $modeldata['email']);
			if($db->has($tablename)){
				return render_json(
					array(
						'data' => $modeldata['email'],
						'message' => "Already exist",
					)
				);
			}
			if($this->validated()){
				$rec_id = $this->rec_id = $db->insert($tablename, $modeldata);
				if($rec_id){
					return render_json(
						array(
							'message' => "Success",
							'rec_id' => $rec_id,
						)
					);
				}
				else{
					return render_json(
						array(
							'message' => "Error",
						)
					);
				}
			}
		}
		return render_json(
			array(
				'message' => "Buat data akun terlebih dahulu",
			)
		);
	}
	
	/**
     * Logout Action
     * Destroy All Sessions And Cookies
     * @return View
     */
	function logout($arg=null){
		Csrf::cross_check();
		session_destroy();
		clear_cookie("login_session_key");
		$this->redirect("");
	}
}
