<?php
namespace Hkm_services\Auth;



use Hkm_code\I18n\Time;
use Hkm_Request\Requests;
use Hkm_services\Auth\HkmUserInterface;
use Hkm_services\Auth\Models\TokenModel;
use Hkm_services\Auth\Models\RequestsModel;
use Hkm_code\Database\BaseBuilder;
use Hkm_services\Auth\Models\UserLoginModel;
use Hkm_services\Hashing\DefaultHasher;
use Hkm_services\Auth\Models\UserUsernameModel;
use Hkm_services\Auth\Models\LoginAttemptsModel;
use Hkm_services\Auth\Models\UserAddressModel;

class AuthProvider implements AuthProviderInterface {

	/**
	 * The active database connection.
	 *
	 * @var UserUsernameModel
	 */
	protected $conn;

    /**
	 * The active database connection.
	 *
	 * @var BaseBuilder $connBuilder
	 */
    protected $connBuilder;

	/**
	 * The hasher implementation.
	 *
	 * @var \Hkm_services\Hashing\HasherInterface
	 */
	protected $hasher;

	/**
	 * App config file.
	 *
	 * @var Object
	 */
	protected $config;

	protected $configAuth;

	/**
	 * Create a new database user provider.
	 *
	 * @param  Object  $config
	 * @return void
	 */
	public function __construct($config=null)
	{
		$this->config = $config??hkm_config('App');
		$this->configAuth = hkm_config('Hkm_services\Auth~Auth');
        $model = new UserUsernameModel();
        $model::CHECK_ENGINE();



        $this->connBuilder = $model::BUILDER();
        $this->conn = $model;



        $this->hasher = !empty($this->config::$passwordHasher)? new $this->config::$passwordHasher() : new DefaultHasher();
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed  $identifier
	 * @return \Hkm_services\Auth\HkmUserInterface|null
	 */
	public function retrieveById($identifier)
	{
		$hourAgo = time() - 60 * 60;

		$this->connBuilder->select('users_info.user_id , password , verified , username , COUNT(LoginAttempts.id) as attemptNumber , users_info.updated_at , email , password , userFullname , phone , birthDate, remember_token, users_info.created_at,users_info.updated_at');
        $this->connBuilder->join('LoginAttempts', 'users_info.user_id = LoginAttempts.user AND timestamp>' . $hourAgo, 'left');
        $this->connBuilder->where('user_id', $identifier);
        $this->connBuilder->groupBy('users_info.user_id');
		$user = $this->connBuilder->get()->getFirstRow('Hkm_services\Auth\HkmUser');

		if ( ! is_null($user))
		{
			return $user->setAddress($this->getUserAddress($user));

		}return null;
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  string  $identifier
	 * @return \Hkm_services\Auth\HkmUserInterface|null
	 */
	public function retrieveUser($identifier)
	{
		$hourAgo = time() - 60 * 60;

		$this->connBuilder->select('users_info.user_id , password , verified , username , COUNT(LoginAttempts.id) as attemptNumber , users_info.updated_at , email , password , userFullname , phone , birthDate, remember_token, users_info.created_at,users_info.updated_at');
        $this->connBuilder->join('LoginAttempts', 'users_info.user_id = LoginAttempts.user AND timestamp>' . $hourAgo, 'left');
        $this->connBuilder->where('email', $identifier);
        $this->connBuilder->orWhere('user_id', $identifier);
        $this->connBuilder->orWhere('username', $identifier);
        $this->connBuilder->groupBy('users_info.user_id');
		$user = $this->connBuilder->get()->getFirstRow('Hkm_services\Auth\HkmUser');

		if ( ! is_null($user))
		{
			return $user->setAddress($this->getUserAddress($user));
		}return null;
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  string  $identifier
	 * @return \Hkm_services\Auth\HkmUserInterface|null
	 */
	public function retrieveByEmail(string $identifier)
	{
		$hourAgo = time() - 60 * 60;

		$this->connBuilder->select('users_info.user_id , password , verified , username , COUNT(LoginAttempts.id) as attemptNumber , users_info.updated_at , email , password , userFullname , phone , birthDate, remember_token, users_info.created_at,users_info.updated_at');
        $this->connBuilder->join('LoginAttempts', 'users_info.user_id = LoginAttempts.user AND timestamp>' . $hourAgo, 'left');
        $this->connBuilder->where('email', $identifier);
        $this->connBuilder->groupBy('users_info.user_id');
		$user = $this->connBuilder->get()->getFirstRow('Hkm_services\Auth\HkmUser');

		if ( ! is_null($user))
		{
			return $user->setAddress($this->getUserAddress($user));
		}return null;
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  string  $identifier
	 * @return \Hkm_services\Auth\HkmUserInterface|null
	 */
	public function retrieveByUsername(string $identifier)
	{
		$hourAgo = time() - 60 * 60;

		$this->connBuilder->select('users_info.user_id , password , verified , username , COUNT(LoginAttempts.id) as attemptNumber , users_info.updated_at , email , password , userFullname , phone , birthDate, remember_token, users_info.created_at,users_info.updated_at');
        $this->connBuilder->join('LoginAttempts', 'users_info.user_id = LoginAttempts.user AND timestamp>' . $hourAgo, 'left');
        $this->connBuilder->orWhere('username', $identifier);
        $this->connBuilder->groupBy('users_info.user_id');
		$user = $this->connBuilder->get()->getFirstRow('Hkm_services\Auth\HkmUser');

		if ( ! is_null($user))
		{
			return $user->setAddress($this->getUserAddress($user));

		}return null;
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  string  $identifier
	 * @return \Hkm_services\Auth\HkmUserInterface|null
	 */
	public function retrieveByUsernameOrEmail(string $identifier)
	{
        $hourAgo = time() - 60 * 60;

		$this->connBuilder->select('users_info.user_id , password , verified , username , COUNT(LoginAttempts.id) as attemptNumber , users_info.updated_at , email , password , userFullname , phone , birthDate, remember_token, users_info.created_at,users_info.updated_at');
        $this->connBuilder->join('LoginAttempts', 'users_info.user_id = LoginAttempts.user AND timestamp>' . $hourAgo, 'left');
        $this->connBuilder->where('email', $identifier);
        $this->connBuilder->orWhere('username', $identifier);
        $this->connBuilder->groupBy('users_info.user_id');
		$user = $this->connBuilder->get()->getFirstRow('Hkm_services\Auth\HkmUser');

		if ( ! is_null($user))
		{
			return $user->setAddress($this->getUserAddress($user));
		}return null;
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  HkmUserInterface  $user
	 * @return \Hkm_services\Auth\HkmUserAddressInterface
	 */
	protected function getUserAddress(HkmUserInterface $user)
	{

		$this->connBuilder->select('user_address.user_id , user_address.id , user_address.country , user_address.state , user_address.postalcode , user_address.updated_at , user_address.address , user_address.created_at , user_address.deleted_at');
        $this->connBuilder->join('user_address', 'user_address.user_id = users_info.user_id', 'left');
        $this->connBuilder->where('users_info.user_id', $user->getAuthIdentifier());
		$address = $this->connBuilder->get()->getFirstRow('Hkm_services\Auth\HkmUserAddress');

		if ( ! is_null($address))
		{
			return $address;
		}return new HkmUserAddress([
			'id'=>'',
			'user_id' => '',
			'address' => '',
			'postalcode' => '',
			'state' => '',
			'country' => ''
		]);
	}

	/**
	 * Retrieve a user by by their unique identifier and "remember me" token.
	 *
	 * @param  mixed   $identifier
	 * @param  string  $token
	 * @return \Hkm_services\Auth\HkmUserInterface|null
	 */
	public function retrieveByToken($identifier, $token)
	{
		$user = $this->connBuilder->where('user_id', $identifier)
                                ->where('remember_token', $token)
                                ->get()->getFirstRow('Hkm_services\Auth\HkmUser');

		if ( ! is_null($user))
		{
			return $user->setAddress($this->getUserAddress($user));

		}return null;
	}

	/**
	 * Retrieve a user by by their phone number.
	 *
	 * @param  string  $phone
	 * @return \Hkm_services\Auth\HkmUserInterface|nul|array
	 */
	public function retrieveByPhone(string $phone)
	{
		$users = $this->connBuilder->where('phone', $phone)
                                ->get()->getFirstRow('Hkm_services\Auth\HkmUser');

		if ( ! is_null($users))
		{
			return $users->setAddress($this->getUserAddress($users));

		}else return null;
	}


	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  \Hkm_services\Auth\HkmUserInterface $user
	 * @param  string  $token
	 * @return void
	 */
	public function updateRememberToken(HkmUserInterface $user, $token)
	{
		
		$this->connBuilder->whereIn('user_id', $user->getAuthIdentifier());
		
		$data = array('remember_token' => $token);
        
		// Must use the set() method to ensure to set the correct escape flag
		foreach ($data as $key => $val)
		{
			$this->connBuilder->set($key, $val, $escape[$key] ?? null);
		}

		$this->connBuilder->update();
	}


	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  \Hkm_services\Auth\HkmUserInterface $user
	 * @param  string  $value
	 * @return void
	 */
	public function setVerified(HkmUserInterface $user, $value)
	{
		$data = array('verified' => $value);
        $this->update($user, $data);
	}

	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  \Hkm_services\Auth\HkmUserAddressInterface $address
	 * @param  array  $value
	 * @return void
	 */
	public function updateAddress(HkmUserAddressInterface $address, $value)
	{
		$model = new UserAddressModel();
		$model::CHECK_ENGINE();
		if ($address->getAddressIdentifier()!= '') {
			$model::UPDATE($address->getAuthIdentifier(),$value);
		}else{
			$model::INSERT($value);
		}
		
		
	}



	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  \Hkm_services\Auth\HkmUserInterface $user
	 * @param  array  $value
	 * @return void
	 */
	public function update(HkmUserInterface $user, $value)
	{
		
		$this->connBuilder->whereIn('user_id', $user->getAuthIdentifier());
		
		$data = $value;
        
		// Must use the set() method to ensure to set the correct escape flag
		foreach ($data as $key => $val)
		{
			$this->connBuilder->set($key, $val, $escape[$key] ?? null);
		}

		$this->connBuilder->update();
	}

	public function createToken($id = null, $payloadData=false, $request = null)
	{

        $secret = Auth_config_private_key($id);

        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);
        if($payloadData){
           $payload =json_encode($payloadData);
        }else{
            $payload = json_encode([
                 md5('id') => $id ?? 0,
				'request' => $request??0,
                'exp' => time() + HOUR_IN_SECONDS
            ]);
        }
        
        $base64UrlHeader = Auth_base64UrlEncode($header);

        // Encode Payload
        $base64UrlPayload = Auth_base64UrlEncode($payload);
        // Create Signature Hash
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = Auth_base64UrlEncode($signature);

        $token = $base64UrlHeader . "_20%_" . $base64UrlPayload . "_20%_" . $base64UrlSignature;

		if (!is_null($id)) {
            $data = [
                'user' => $id,
                'token' => $token,
                'valid' => 'true'
            ];
            $model = new TokenModel();
            $model::INSERT($data);
        }
		
        return $token;
    
	}

	public function unvalidateToken($token)
	{
		$model = new TokenModel();
        $rt = $model::FIND($token);
        
        if (count($rt ?? []) > 0) {
            if($model::DELETE($token)) return true;
			return true;
        }
	}

	public function validateToken($token)
	{
		$model = new TokenModel();
        $rt = $model::FIND($token);
        $id = null;

        if (count($rt ?? []) > 0) {
            if ($rt['valid'] == "true") {
                $id = $rt['user'];
            }
        }
        $secret = Auth_config_private_key($id);
        $token = trim($token);
        $tokenParts = explode('_20%_', $token);
        if (is_array($tokenParts)) {
            if (count($tokenParts) == 3) {
                $header = base64_decode($tokenParts[0]);
                $payload = base64_decode($tokenParts[1]);
                $signatureProvided = $tokenParts[2];
                if (json_decode($payload)->exp) {
                    $expiration = Time::CREATE_FROM_TIMESTAMP(json_decode($payload)->exp);
                    $tokenExpired = ((Time::NOW()->DIFFERENCE($expiration))->GET_SECONDS() < 0);
                    $base64UrlHeader = Auth_base64UrlEncode($header);
                    $base64UrlPayload = Auth_base64UrlEncode($payload);
                    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
                    $base64UrlSignature = Auth_base64UrlEncode($signature);
                    $signatureValid = ($base64UrlSignature === $signatureProvided);
                    if ($tokenExpired) {
                        return false;
                    } else {
            
                        if ($signatureValid) {
                            return json_decode($payload);
                        } else {
                            return json_decode($payload);
                        }
                    }
                }
                
            }
        }
        
        
        return false;
        
        
	}
	public function deleteAttemps(HkmUserInterface $user)
	{
		$m = new LoginAttemptsModel();
		$m::CHECK_ENGINE();
		$m::DELETE($user->getAuthIdentifier());
        $this->updateOnlineStatus($user);
	}

	public function addAttemp(HkmUserInterface $user)
	{

		$data = [
			'user' => $user->getAuthIdentifier(),
			'timestamp' => time()
		];

		$m = new LoginAttemptsModel();
		$m::CHECK_ENGINE();
		$m::INSERT($data);
	}

	public function updateOnlineStatus(HkmUserInterface $user)
	{
		/**
		 * @var UserLoginModel $userLogin
		 */
		$userLogin = new UserLoginModel();

		$F = $userLogin::FIND($user->getAuthIdentifier());
		if (empty($F)) {
			$userLogin::INSERT([
				'user_login' => $user->getAuthIdentifier()
			]);
		}else{
			$userLogin::UPDATE($user->getAuthIdentifier(),['user_login' => $user->getAuthIdentifier()]);
		}
                        
	}

	public function sendRequest(HkmUserInterface $user,int $request,bool $update = false)
	{
		switch ($request) {
			case VERIF_EMAIL:
				$otpLength = $this->configAuth::$otpLength;
				$email = $user->getEmail();
				try {
					$response = Requests::get("http://apis.hakrichteam.com/e_plus/sendotp/$email/$otpLength?subject=X-project OTP-verification", [], [
						'timeout' => 70
					]);
					$body = $response->body;
					if (strpos($response->headers['content-type'], 'application/json') !== false) {
						$body = json_decode($body);
					}
					if ($body->error == 0) {
						$token = $this->createToken($user->getAuthIdentifier(),[
							md5('id') => $user->getAuthIdentifier(),
							'request' => VERIF_EMAIL,
							'code' => $body->code,
							'exp' => time() + HOUR_IN_SECONDS
						]);
						
						$code = $body->code;
						$hash = password_hash($code, PASSWORD_DEFAULT);
						$data = [
							'hash' => $hash,
							'user' => $user->getAuthIdentifier(),
							'timestamp' => time(),
							'type' => VERIF_EMAIL,
							'token' => $token
						];
						$model = new RequestsModel();
						$model::CHECK_ENGINE();
						if ($update){
							$model::BUILDER()->whereIn('user', $user->getAuthIdentifier())->whereIn('type',VERIF_EMAIL);
							// Must use the set() method to ensure to set the correct escape flag
							foreach ($data as $key => $val)
							{
								$model::BUILDER()->set($key, $val, $escape[$key] ?? null);
							}

							$model::BUILDER()->update();
						} 
						else $model::INSERT($data);
	
						return [
							'code' => $code,
							'token' => $token
						];
						
					} else {
						return [
							'error' => true,
							'message' => '<strong>Error </strong>Connecting to Teamsmailer Server!'
						];
					}
				}
				catch (\Throwable $th) {
					return [
						'error' => true,
						'message' => '<strong>Error </strong>Connecting to Teamsmailer Server!'
					];
				}
				break;
			case RESET_PASSWORD_REQUEST:
				$otpLength = $this->configAuth::$otpLength;
				$email = $user->getEmail();
				try {
					$response = Requests::get("https://apis.hakrichteam.com/e_plus/sendotp/$email/$otpLength?subject=X-project OTP-verification", [], [
						'timeout' => 70
					]);
					$body = $response->body;
					if (strpos($response->headers['content-type'], 'application/json') !== false) {
						$body = json_decode($body);
					}
					if ($body->error == 0) {
						$token = $this->createToken($user->getAuthIdentifier(),[
							md5('id') => $user->getAuthIdentifier(),
							'request' => RESET_PASSWORD_REQUEST,
							'code' => $body->code,
							'exp' => time() + HOUR_IN_SECONDS
						]);
						$code = $body->code;
						$hash = password_hash($code, PASSWORD_DEFAULT);
						$data = [
							'hash' => $hash,
							'user' => $user->getAuthIdentifier(),
							'timestamp' => time(),
							'type' => RESET_PASSWORD_REQUEST,
							'token' => $token
						];
						
						$model = new RequestsModel();
						$model::CHECK_ENGINE();
						if ($update){
							$model::BUILDER()->whereIn('user', $user->getAuthIdentifier())->whereIn('type',RESET_PASSWORD_REQUEST);
							// Must use the set() method to ensure to set the correct escape flag
							foreach ($data as $key => $val)
							{
								$model::BUILDER()->set($key, $val, $escape[$key] ?? null);
							}

							$model::BUILDER()->update();
						} 
						else $model::INSERT($data);
						return [
							'code' => $code,
							'token' => $token
						];
					} else {
						return [
							'error' => true,
							'message' => '<strong>Error </strong>Connecting to Teamsmailer Server!'
						];
					}
				}
				catch (\Throwable $th) {
					return [
						'error' => true,
						'message' => '<strong>Error </strong>Connecting to Teamsmailer Server!'
					];
				}
				break;
			
			default:
				# code...
				break;
		}
		
	}

	public function deleteRequest(string $token)
	{
		$model = new RequestsModel();
		$model::CHECK_ENGINE();
		$model::DELETE($token);
	}

	public function getRequests(HkmUserInterface $user,int $request = 0)
	{
		
		$model = new RequestsModel();
		$model::CHECK_ENGINE();
		$builder = $model->BUILDER();
		if ($request>0) $requests = $builder->where('user',$user->getAuthIdentifier())->where('type',$request)->get()->getFirstRow('Hkm_services\Auth\HkmRequest');
		else $requests = $builder->where('user',$user->getAuthIdentifier())->get()->getResultObject('Hkm_services\Auth\HkmRequest');

		if ( ! is_null($requests))
		{
			return $requests;
		}return null;
        
	}

	public function checkRequest(HkmUserInterface $user,$request)
	{
		
		$model = new RequestsModel();
		$model::CHECK_ENGINE();
		$builder = $model->BUILDER();
		$requests = $builder->where('user',$user->getAuthIdentifier())->where('type',$request)->get()->getFirstRow('Hkm_services\Auth\HkmRequest');

		if ( ! is_null($requests))
		{
			return $requests;
		}return null;
        
	}
	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array  $credentials
	 * @return \Hkm_services\Auth\HkmUserInterface|null
	 */
	public function retrieveByCredentials(array $credentials)
	{
		// First we will add each credential element to the query as a where clause.
		// Then we can execute the query and, if we found a user, return it in a
		// generic "user" object that will be utilized by the Guard instances.

		foreach ($credentials as $key => $value)
		{
            $query = $this->connBuilder;
			if ( ! str_contains($key, 'password'))
			{
				$query->where($key, $value);
			}
		}

		// Now we are ready to execute the query to see if we have an user matching
		// the given credentials. If not, we will just return nulls and indicate
		// that there are no matching users for these given credential arrays.
		$user = $query->get()->getFirstRow('Hkm_services\Auth\HkmUser');

		if ( ! is_null($user))
		{
			return $user->setAddress($this->getUserAddress($user));

		}return null;
	}

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  \Hkm_services\Auth\HkmUserInterface  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validateCredentials(HkmUserInterface $user, array $credentials)
	{
		$plain = $credentials['password'];

		return $this->hasher->check($plain, $user->getAuthPassword());
	}

    /**
	 *
	 * @param  HkmUserInterface  $user
	 */
    public function resetPasswordRequest(HkmUserInterface $user)
    {
		return $this->sendRequest($user,RESET_PASSWORD_REQUEST);
        
    }

	 /**
	 *
	 * @param  string  $token
	 */
    public function resendPasswordRequest($token)
    {
        $this->connBuilder->select('users_info.user_id, email');
        $this->connBuilder->join('requests', 'users_info.user_id = requests.user AND type=' . RESET_PASSWORD_REQUEST . " AND token = '" . $token . "'", 'left');
        $user = $this->connBuilder->get()->getFirstRow('Hkm_services\Auth\HkmUser');
        if (! is_null($user)) return $this->sendRequest($user,RESET_PASSWORD_REQUEST,true);
        else return false;
    }

}
