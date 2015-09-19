<?php
class Auth_Controller_Action_Helper_Login extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Form action
     * @var string
     */
    protected $_action;

    /**
     * Auth
     * @var Zend_Auth
     */
    protected $_auth;

    /**
     * Auth adapter
     * @var Zend_Auth_Adapter_Interface
     */
    protected $_authAdapter;
    
    /**
     * Temp Password Auth adapter
     * @var Zend_Auth_Adapter_Interface
     */
    protected $_tempAuthAdapter;

    /**
     * Login form
     * @var Users_Form_Login
     */
    protected $_form;

    /**
     * Session namespace
     * @var Zend_Session_Namespace
     */
    protected $_session;

    /**
     * Log a user in and redirects to the requesting page
     *
     * @return void
     */
    public function execute()
    {
        if ($this->getAuth()->hasIdentity() && $this->getAuth()->getIdentity() instanceof Users_Model_DbTable_Users_Row) {
            return true;
        }

        $email = (string) $this->getAuth()->getIdentity();

        $userModel = new Users_Model_DbTable_Users();
        $user = $userModel->fetchByEmail($email);
        if (!$user) {
            $this->invalidateUser();
            return false;
        }

        // check if user is banned
        if (!$user->canLogin()) {
            $this->invalidateUser();
            return false;
        }
        
        $this->getAuth()->clearIdentity();
        
        // record login
        $user->postLogin();
       
        // set the user to the session
        $this->getAuth()->getStorage()->write($user);
        // set helper auth
        
        $returnLink = $this->getForm()->getElement("return")->getValue();
        
        // direct to change password AND clear temp password
        if ($returnLink == 'change-password') {
            //$user->clearTempPassword();
            return true;
        }
        
        return true;
        
    }

    private function invalidateUser() {
        $this->getForm()->addError('Invalid username or password.');
        //$this->getForm()->addDecorator('Errors', array('placement' => Zend_Form_Decorator_Abstract::PREPEND));	
    }

    /**
     * Get the auth
     *
     * @return Zend_Auth
     * @throws Zend_Controller_Action_Exception
     */
    public function getAuth()
    {
        if (!$this->_auth) {
            throw new Zend_Controller_Action_Exception(
                "No auth set"
            );
        }

        return $this->_auth;
    }

    /**
     * Get the auth adapter
     *
     * @return Zend_Auth_Adapter_Interface
     * @throws Zend_Controller_Action_Exception
     */
    public function getAuthAdapter()
    {
        if (!$this->_authAdapter) {
            throw new Zend_Controller_Action_Exception(
                "No auth adapter set"
            );
        }

        return $this->_authAdapter;
    }
    
    /**
     * Get the login form
     *
     * @return Users_Form_Login
     */
    public function getForm()
    {
        if (!$this->_form) {
            $this->_form = new Auth_Form_Signin(array(
                "action"      => $this->_action,
                "auth"        => $this->getAuth(),
                "authAdapter" => $this->getAuthAdapter(),
                "baseUrl"     => $this->getRequest()->getBaseUrl()
            ));
            $this->_form->setTempAuthAdapter($this->getTempAuthAdapter());
            $session = $this->getSession();
            if (isset($session->postLoginUrl)) {
                    
                $this->_form
                     ->getElement("return")
                     ->setValue($this->getSession()->postLoginUrl);
                     
                unset($session->postLoginUrl);     
            }
        }

        return $this->_form;
    }

    /**
     * Get the session namespace
     *
     * @return Zend_Session_Namespace
     */
    public function getSession()
    {
        if (!$this->_session) {
            throw new Zend_Controller_Action_Exception(
                "No session namespace set"
            );
        }

        return $this->_session;
    }

    /**
     * Validate the data
     *
     * @param array $data
     * @return boolean
     */
    public function isValid($data)
    {
        if ($this->getForm()->isValid($data)) {
            return true;
        }

        return false;
    }

    /**
     * Set the form action
     *
     * @param string $action
     * @return Users_Controller_ActionHelper_Login
     */
    public function setAction($action)
    {
        $this->_action = $action;

        return $this;
    }

    /**
     * Set the auth
     *
     * @param Zend_Auth $auth
     * @return Users_Controller_ActionHelper_Login
     */
    public function setAuth(Zend_Auth $auth)
    {
        $this->_auth = $auth;

        return $this;
    }

    /**
     * Set the Auth adapter
     *
     * @param Zend_Auth_Adapter_Interface
     * @return Users_Controller_ActionHelper_Login
     */
    public function setAuthAdapter(Zend_Auth_Adapter_Interface $authAdapter)
    {
        $this->_authAdapter = $authAdapter;

        return $this;
    }

    /**
     * Get the temp password auth adapter
     *
     * @return Zend_Auth_Adapter_Interface
     * @throws Zend_Controller_Action_Exception
     */
    public function getTempAuthAdapter()
    {
        if (!$this->_tempAuthAdapter) {
            throw new Zend_Controller_Action_Exception(
                "No temp auth adapter set"
            );
        }

        return $this->_tempAuthAdapter;
    }
    
    /**
     * Set the temp password auth adapter
     *
     * @param Zend_Auth_Adapter_Interface $adapter Temp Adapter
     * @return Users_Controller_ActionHelper_Login
     * @throws Zend_Controller_Action_Exception
     */
    public function setTempAuthAdapter(Zend_Auth_Adapter_Interface $adapter)
    {
        $this->_tempAuthAdapter = $adapter;
        
        return $this;
    }
    
    
    /**
     * Set the session namespace
     *
     * @param Zend_Session_Namespace $session
     * @return Users_Controller_ActionHelper_Login
     */
    public function setSessionNamespace(Zend_Session_Namespace $session)
    {
        $this->_session = $session;

        return $this;
    }
}
