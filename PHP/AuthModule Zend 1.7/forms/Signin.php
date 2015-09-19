<?php
class Auth_Form_Signin extends Digitas_Form
{
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
     * Base URL
     * @var string
     */
    protected $_baseUrl;

    /**
     * Temp Password Auth adapter
     * @var Zend_Auth_Adapter_Interface
     */
    protected $_tempAuthAdapter;
    
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
     * @param Zend_Auth_Adapter_Interface $adapter Temp Password Adapter
     * @return void
     */
    public function setTempAuthAdapter(Zend_Auth_Adapter_Interface $adapter)
    {
        $this->_tempAuthAdapter = $adapter;
         
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
     * Get the base URL
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }
    
    /**
     * Initialize the form
     * 
     * @return void
     */
    public function init() {
        $this->addElement(
            $this->createElement("hidden", "return")
        );
            
        $this->addElement(
            'text', 
            'login_email', 
            array(
                'required'   => true,
                'label'      => 'Email Address',
                'maxlength'  => '50',
                'filters'    => array('stripTags', 'stringTrim'),
                'validators' => array('NotEmpty'),
                'attribs'    => array('class'=> 'input-type-text')
            )
        );
        
        $this->getElement('login_email')->getValidator('NotEmpty')->setMessage('Please enter your user name');
        $this->getElement('login_email')->removeDecorator('Errors');
        
        $this->addElement(
            'password', 
            'login_password', 
            array(
                'required'   => true,
                'label'      => 'Password',
                'maxlength'  => '50',
                'filters'    => array('stripTags','stringTrim'),
                'validators' => array('NotEmpty'),
                'attribs'    => array('class'=>'input-type-text'),
                'renderPassword' => true,
            )
        );
        
        $this->getElement('login_password')->getValidator('NotEmpty')->setMessage('Please enter your password');
        $this->getElement('login_password')->removeDecorator('Errors');
        
        $this->addElement(
            'submit',
            'submit',
            array(
                'label' => 'Sign In',
            )
        );
        
        $this->setDecorators( array("ViewHelper") );
        $this->setElementDecorators( array("ViewHelper") );
    }
    
    /**
     * Validate the form
     *
     * @param array $data Form POST data
     * @return boolean
     */
    public function isValid($data)
    {
        if (!parent::isValid($data)) {
            $this->getElement('login_password')->setValue('');
            $this->getElement('login_password')->addError("Email or Password was incorrect. Please try again.");
            $this->addError('Email or Password was incorrect. Please try again.');
            return false;
        }

        $adapter = $this->getAuthAdapter()
                        ->setIdentity($this->getValue("login_email"))
                        ->setCredential($this->getValue("login_password"));

        $result = $this->getAuth()
                       ->authenticate($adapter);
                                              
        if ($result->isValid()) {
            return true;
        }
        
        // password failed, try temp password
        
        $tempAdapter = $this->getTempAuthAdapter()
                        ->setIdentity($this->getValue("login_email"))
                        ->setCredential($this->getValue("login_password"));

        $result = $this->getAuth()
                       ->authenticate($tempAdapter);

        if ($result->isValid()) {
            $this->getElement('return')->setValue('change-password');
            return true;
        }

        $this->addError('Email or Password was incorrect. Please try again.');
                
        $this->getElement('login_password')->setValue('');
        $this->getElement('login_password')->addError("Email or Password was incorrect. Please try again.");
        
        return false;
    }

    /**
     * Set the auth
     *
     * @param Zend_Auth $auth Auth to use for storing authenticated user
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
     * @param Zend_Auth_Adapter_Interface $authAdapter Adapter for authenticating user
     * @return Users_Controller_ActionHelper_Login
     */
    public function setAuthAdapter(Zend_Auth_Adapter_Interface $authAdapter)
    {
        $this->_authAdapter = $authAdapter;

        return $this;
    }

    /**
     * Set the base URL
     *
     * @param string $baseUrl Form Base URL
     * @return Users_Form_Login
     */
    public function setBaseUrl($baseUrl)
    {
        $this->_baseUrl = $baseUrl;

        return $this;
    }

}


?>
