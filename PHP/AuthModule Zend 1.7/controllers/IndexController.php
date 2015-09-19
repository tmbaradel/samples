<?php
class Auth_IndexController extends Zend_Controller_Action
{

    /**
     * Login Helper
     *
     * @var Users_Controller_Action_Helper_Login
     */
    protected $_loginHelper;

    /**
     * Signup Helper
     *
     * @var Users_Controller_Action_Helper_Signup
     */
    protected $_signupHelper;

    public function init()
    {
        $this->view->pageClass = 'register';
    }

    /**
     * Signin Action
     *
     * @return void
     */
    public function signinAction()
    {
        $currentUser = $this->_helper->currentUser->getCurrentUser();

        if ($currentUser) {
            $this->_helper->redirector->goToRoute(
              array(),
              'profile'
            );
            return;
        }

		    $this->view->pageClass = 'login';

        $helper = $this->_getLoginHelper();

        if ($this->getRequest()->isPost() && $helper->isValid($this->getRequest()->getPost())) {
            $return = $helper->execute();
            if ($return) {
              $returnLink = $helper->getForm()->getElement("return")->getValue();
              if ($returnLink == 'change-password') {
                $this->_helper->redirector->goToRoute(
                  array(
                    'module'     => 'auth',
                    'controller' => 'index',
                    'action'     => 'update-password'
                  ),
                  'default');
                  return;
              }

              $this->_helper->currentUser->setAuth($helper->getAuth());
              $redirectSession = new Zend_Session_Namespace('signin');

              if (isset($redirectSession->saveLiteSong)) {
                $this->_helper->redirector->goToRoute(
                  array(
                    'module'    => 'remix',
                    'controller' => 'index',
                    'action'    => 'save-lite-song'
                  ),
                  'default'
                );
                return;
              }

              if (isset($redirectSession->saveProSong)) {
                $stems = $redirectSession->saveProSong;
                $redirectSession->saveProSong = null;
                $this->_helper->redirector->goToRoute(
                  array(
                    'module'    => 'remix',
                    'controller' => 'index',
                    'action'    => 'mix-in-pro',
                    'stems'     => $stems
                  ),
                  'default'
                 );
                 return;
                }

                //check for save pro project
                $mixproSession = new Zend_Session_Namespace('signin');

                if (isset($mixproSession->project)) {
                  $this->_helper->redirector->goToRoute(
                    array(
                      'module'    => 'api',
                      'controller' => 'index',
                      'action'    => 'save-song',
                      'redirectAfter' => true
                    ),
                    'default',
                    true
                  );
                }

                if (isset($redirectSession->postSigninRedirect)) {
                  $route = $redirectSession->postSigninRedirect;
                  $this->_helper->redirector->goToUrl($route, array("prependBase" => empty($return), ));
                  return;
                }

                $currentUser = $this->_helper->currentUser->getCurrentUser();
                if ($currentUser->role == Users_Model_DbTable_Users::ROLE_ADMIN) {
                  $this->_helper->redirector->goToRoute(
                    array(
                      'module'     => 'admin',
                      'controller' => 'index',
                      'action'     => 'index'
                    ),
                    'default'
                  );
                  return;
                }
                else {
                    $this->_helper->redirector->goToRoute(
                        array(
                        ),
                        'profile'
                    );
                    return;
                }

            }
        }
        $successMessages =  $this->_helper->flashMessenger->setNamespace('success')->getMessages();

        $flashhelper = $this->_helper->flashMessenger;
        $flashhelper->setNamespace('error');

        if ($messages = $flashhelper->getMessages()) {
            $this->view->messages = $messages;
        }
        else if ($formMessages = $helper->getForm()->getMessages()) {
            $this->view->messages = $formMessages;
        }

        if ($successMessages) {
            $this->view->successMessages = $successMessages;
        }

        $this->view->form = $helper->getForm();

    }

    /**
     * Signout Action
     *
     * @return void
     */
    public function signoutAction()
    {

        $bootstrap = $this->getInvokeArg('bootstrap')
                          ->bootstrap('auth');

        $bootstrap->getResource('auth')
                  ->clearIdentity();

        Zend_Session::destroy();

        $this->_helper->redirector->gotoRoute(
            array(),
            'login',
            true
        );
    }


    public function forgotPasswordAction()
    {
        $currentUser = $this->_helper->currentUser->getCurrentUser();
        if ($currentUser) {
          $this->_helper->redirector->goToRoute(
            array(),
            'profile'
          );
          return;
        }
        // Forgot Password Form
        $forgotPassHelper = $this->_helper->forgotPassword;

        $forgotPassHelper->setUserTable(new Users_Model_DbTable_Users());
        $forgotPassHelper->setView($this->view);

        if ($this->getRequest()->isPost() && $forgotPassHelper->isValid($this->getRequest()->getPost())) {
            $forgotPassHelper->execute();
            $this->_helper
                 ->flashMessenger
                 ->setNamespace('success')
                 ->addMessage("Instructions to recover your password has been sent to your email.");

            $this->_helper->redirector->goToRoute(
                    array(),
                    'login'
            );
        }

        $this->view->forgotPassForm = $forgotPassHelper->getForm();
		    $this->view->pageClass = "forgotpass";
    }

    public function updatePasswordAction() {
        $currentUser = $this->_helper->currentUser->getCurrentUser();

        if (!$currentUser) {
            $this->_helper->redirector->goToRoute(array(), 'login');
            return;
        }

        if (!$currentUser->forgot_password_code) {
            $this->_helper->redirector->goToRoute(array(), 'profile');
            return;
        }

        $resetPasswordHelper = $this->_helper->resetPassword;
        $resetPasswordHelper->setUserTable(new Users_Model_DbTable_Users())
                            ->setCode($currentUser->forgot_password_code);
        if ($this->getRequest()->isPost() && $resetPasswordHelper->isValid($this->getRequest()->getPost())) {
            $result = $resetPasswordHelper->execute();

            if ($result) {
                $this->_helper->redirector->goToRoute(
                  array(),
                  'profile'
                );
            }
        }
        $this->view->form = $resetPasswordHelper->getForm();
    }

    public function signupAction()
    {
        $user = $this->_helper->model('Users', 'Users')->createRow();
        $signUpHelper = $this->_getSignupHelper();
        $signUpHelper->setUser($user)
                     ->setView($this->view);

        if ($this->getRequest()->isPost() && $signUpHelper->isValid( $this->getRequest()->getPost())) {
            $result = $signUpHelper->execute();

            $redirectSession = new Zend_Session_Namespace('signin');

            if (isset($redirectSession->saveLiteSong)) {
                $this->_helper->redirector->goToRoute(
                    array(
                        'module'    => 'remix',
                        'controller' => 'index',
                        'action'    => 'save-lite-song'
                    ),
                    'default'
                );
                return;
            }

            if (isset($redirectSession->saveProSong)) {
                $stems = $redirectSession->saveProSong;
                $redirectSession->saveProSong = null;
                $this->_helper->redirector->goToRoute(
                    array(
                        'module'    => 'remix',
                        'controller' => 'index',
                        'action'    => 'mix-in-pro',
                        'stems'     => $stems
                    ),
                    'default'
                );
                return;
            }

            //check for save pro project
            $mixproSession = new Zend_Session_Namespace('signin');

            if (isset($mixproSession->project)) {
                $this->_helper->redirector->goToRoute(
                    array(
                        'module'    => 'api',
                        'controller' => 'index',
                        'action'    => 'save-song',
                        'redirectAfter' => true
                    ),
                    'default',
                    true
                );
                return;
            }

            $this->_helper->redirector->goToRoute(
                array(
                ),
                'profile'
            );
            return;
        }
        $this->view->form = $signUpHelper->getForm();
    }


    /**
     * This method handles cropping of images
     * Image is cropped everytime a new profile is added, or
     * when a profile is updated <ul>AND</ul> a new image is uploaded
     *
     * @param Digitas_Form $form
     * @param Users_Model_ProfileImage $profileImageModel
     * @return void
     */
    protected function _handleImageCrop(Digitas_Form $form, Users_Model_ProfileImage $profileImageModel)
    {
         $imageCropHelper = $this->_helper->imageCrop;
         $s3ImageUploadHelper = $this->_helper->s3ImageUpload;

         try {
             $imageCropHelper->setProfileImage($profileImageModel)
             ->execute();
             $s3ImageUploadHelper->setProfileImage($profileImageModel)
             ->execute();
         } catch (Exception $e) {
             $form->image->addError('Unable to resize image');
             $form->addError('Unable to resize image');
         }
    }

    /**
     * Get the Login Helper
     *
     * @return Users_Controller_ActionHelper_Login
     */
    protected function _getLoginHelper()
    {
        if (! $this->_loginHelper) {

            $bootstrap = $this->getInvokeArg('bootstrap')
                              ->bootstrap('auth')
                              ->bootstrap('authAdapter')
                              ->bootstrap('tempAuthAdapter');

            $tempAuthAdapter = $bootstrap->getResource('tempAuthAdapter');
            $authAdapter = $bootstrap->getResource('authAdapter');
            $processUrl = $this->_helper->url->url(
                array(
                    'module'     => 'auth',
                    'controller' => 'index',
                    'action'     => 'signin',
                ),
                'default',
                true
            );

            $this->_loginHelper =
                $this->_helper
                     ->login
                     ->setAction($processUrl)
                     ->setAuth($bootstrap->getResource('auth'))
                     ->setAuthAdapter($authAdapter)
                     ->setTempAuthAdapter($tempAuthAdapter)
                     ->setSessionNamespace(new Zend_Session_Namespace('login'));

        }

        return $this->_loginHelper;
    }

    /**
     * Get the Signup Helper
     *
     * @return Users_Controller_ActionHelper_Login
     */
    protected function _getSignupHelper()
    {
        if (! $this->_signupHelper) {

            $bootstrap = $this->getInvokeArg('bootstrap')
                              ->bootstrap('auth');

            $this->_signupHelper =
                $this->_helper
                     ->signUp
                     ->setAuth($this->_helper->currentUser->getAuth());

        }

        return $this->_signupHelper;
    }
}
