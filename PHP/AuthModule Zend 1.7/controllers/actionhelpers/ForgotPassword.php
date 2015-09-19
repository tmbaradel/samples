<?php
/**
 * Chi
 *
 * @category Chi
 * @package Users
 * @subpackage Controller
 * @author Tom Baradel
 */

/**
 * Forgot password action helper
 *
 * @category Chi
 * @package Users
 * @subpackage Controller
 * @author Tom Baradel
 * @version $Id$
 */
class Auth_Controller_Action_Helper_ForgotPassword extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Forgot password form
     * 
     * @var Users_Form_ForgotPassword
     */
    protected $_form;

    /**
     * User
     * 
     * @var Users_Model_DbTable_Users_Row
     */
    protected $_user;
    
    /**
     * User Table
     *
     * @var Users_Model_DbTable_Users
     */
    protected $_userTable;
    
    /**
     * View
     *
     * @var Zend_View
     */
    protected $_view;

    /**
     * Send the forgot password email
     *
     * @return Users_Controller_ActionHelper_ForgotPassword
     */
    public function execute()
    {
        $this->_user = $this->getUserTable()
                            ->fetchByEmail($this->getForm()->getValue("emailAddress"));

        return $this->_sendForgotPasswordEmail();
    }

    /**
     * Get the forgot password form
     *
     * @return Users_Form_ForgotPassword
     */
    public function getForm()
    {
        if (!$this->_form) {
            $this->_form = new Users_Form_ForgotPassword();
        }
        return $this->_form;
    }

    /**
     * Check if the data is valid
     *
     * @param array $data
     * @return boolean
     */
    public function isValid(array $data)
    {
        return $this->getForm()
                    ->isValid($data);
    }
    
    /**
     * Set user table
     *
     * @param Users_Model_DbTable_Users $userTable
     * @return Users_Controller_ActionHelper_ForgotPassword
     */
    public function setUserTable(Users_Model_DbTable_Users $userTable)
    {
        $this->_userTable = $userTable;
    }
    
    /**
     * Get user table
     *
     * @return Users_Model_DbTable_Users $userTable
     */
    public function getUserTable()
    {
        if (! $this->_userTable) {
            throw new Exception('User table not set');
        }
        
        return $this->_userTable;
    }

    /**
     * Set view
     *
     * @param Zend_View $view
     * @return Users_Controller_ActionHelper_ForgotPassword
     */
    public function setView(Zend_View $view)
    {
        $this->_view = $view;
    }
    
    /**
     * Get view
     *
     * @return Zend_View $view
     */
    public function getView()
    {
        if (! $this->_view) {
            throw new Exception('View not set');
        }
        
        return $this->_view;
    }

    /**
     * Set the forgot password
     *
     * @return Users_Controller_ActionHelper_ForgotPassword
     */
    protected function _setForgotPassword()
    {
        $this->_user->generateForgotPassword()->save();

        return $this;
    }

    /**
     * Send the forgot password email
     *
     * @return Users_Controller_ActionHelper_ForgotPassword
     */
    protected function _sendForgotPasswordEmail()
    {
        $mail = new Zend_Mail();
        
        $view = clone $this->getView();

        $view->name = $this->_user->first_name;

        $this->_user->generateForgotPassword();
        $this->_user->has_temp_password = 1;
        $this->_user->save();

        $view->newPassword = $this->_user->forgot_password_code;

        $config = Zend_Registry::get('config');

        $mail->addTo($this->_user->email, $this->_user->first_name . ' ' . $this->_user->last_name)
             ->setSubject("Forgot Password")
             ->setFrom($config['email']['username'])
             ->setBodyText($view->render("email/forgot-password-text.phtml"))
             ->setBodyHtml($view->render("email/forgot-password-html.phtml"))
             ->send()
             ;

        return $this;
    }

}
