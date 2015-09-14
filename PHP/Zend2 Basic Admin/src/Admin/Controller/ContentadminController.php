<?php
namespace Admin\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\LoginForm;
use Admin\Model\Content;
use Admin\Controller\AdminController;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Debug\Debug;
use Admin\Form\ContentEditForm;

class ContentadminController extends AdminController
{

	protected $route_name;
	protected $cls_name;
	protected $contentTable;
	protected $madl_admin;

	public function __construct()
	{
		parent::__construct();
		$this->route_name = 'contentadmin';
		$this->madl_admin = 'content';
	}


	public function indexAction()
	{
		$msg = '';
		//admin mod vars
		$this->setAdminModVars(array($this->madl_admin=>array("href"=>"","class"=>"current")));
		//get all the content
		$all_content = $this->getContentTable()->fetchAll();
		$fields = array("id","title","date","author");
		//get edit content address from route
		$edit_address = $this->url()->fromRoute($this->route_name,array("controller"=>$this->route_name,"action"=>"editcontent"));
		//get delete content address from route
		$delete_address = $this->url()->fromRoute($this->route_name,array("controller"=>$this->route_name,"action"=>"deletecontent"));
		//view partial table
		$table = new ViewModel(array('to_show'=>$all_content,
				'fields'=>$fields,
				'edit_address'=>$edit_address,
				'delete_address'=>$delete_address
		));
		$table->setTemplate('components/table');

		$request = $this->getRequest();
		if ($request->isGet()) {
			if ($this->params()->fromQuery('message')=="del" && $this->params()->fromQuery($this->madl_admin)) {
				$msg = array("alert_info","Content with Id ".(int)$this->params()->fromQuery($this->madl_admin)." deleted");
			}
		}
		$view = new ViewModel(array("msg"=>$msg));
		$view->addChild($table,'table');
		return $view;
	}

	/*
	* editcontentAction
	*
	* return object
	*/
	public function editcontentAction()
	{
		//set vars and pass the breacrumbs
		$this->setAdminModVars(array($this->madl_admin=>array("href"=>$this->url()->fromRoute($this->route_name),"class"=>"current"),
				"edit_".$this->madl_admin=>array("href"=>"","class"=>"current")));

		$edit_id = (int) $this->params()->fromRoute('id', 0);
		$content = $this->getContentTable()->getContent($edit_id);

		if (!$content) {//redirect if edit item doesn't exists
			$this->redirect()->toRoute($this->route_name,array('controller'=>$this->route_name,'action' => 'index'));
		}

		$edit_form = new ContentEditForm('user_edit',$content);

		//check if there is a request
		$request = $this->getRequest();

		if ($request->isPost()) {
			$this->sendToSaveModule($request, $edit_form,$content);
		}

		$edit_form->bind($content);
		$form_tpl = new ViewModel(array("form"=>$edit_form,"module_name"=>"Content"));
		$form_tpl->setTemplate('components/edit_form');

		$view = new ViewModel();
		$view->addChild($form_tpl,'edit_form');

		return $view;
	}

	/*
	* newcontentAction
	*
	* return object
	*/
	public function newcontentAction()
	{
		$admin_variables = array(
				$this->madl_admin => array("href"=>$this->url()->fromRoute($this->route_name),"class"=>"current"),
				$this->madl_admin=>array("href"=>"","class"=>"current"));

		$this->setAdminModVars( ));
		$edit_form = new ContentEditForm('content_new',new Content($this->getContentTable()->getTableFileds()));
		//check the post
		$request = $this->getRequest();

		if ($request->isPost()) {
			if ($this->sendToSaveModule($request, $edit_form) === -1) {
				$msg = array("alert_error","Contentname already used");
			}
			else{
				$msg = array("alert_success","Content added");
			}
		}

		$form_tpl = new ViewModel(array("form"=>$edit_form,"module_name"=>"Content"));
		$form_tpl->setTemplate('components/edit_form');
		$view = new ViewModel(array("msg"=>$msg));
		$view->addChild($form_tpl,'new_form');

		return $view;
	}

	/*
	* yourProfileAction
	*
	* return void
	*/
	public function yourProfileAction()
	{
		$this->setAdminModVars();
		$this->redirect()->toUrl('/usersadmin/editcontent/'.$this->user_details->id);
	}

	/*
	* viewallusersAction
	*
	* redirect
	*/
	public function viewallusersAction()
	{
		$this->redirect()->toRoute($this->route_name);
	}

	/*
	* deletecontentAction
	*
	* return redirect
	*/
	public function deletecontentAction()
	{
		$delete_id = (int) $this->params()->fromRoute('id', 0);
		$content = $this->getContentTable()->getContent($delete_id);
		if (!$content) {//redirect if user doesn't exists
			return $this->redirect()->toRoute($this->route_nam);
		}
		$this->getContentTable()->deleteContent($delete_id);
		return $this->redirect()->toUrl('/'.$this->route_name.'?message=del&'.$this->madl_admin.'='.$delete_id);
	}

	/*
	* sendToSaveModule
	*
	* return @boolan
	*/
	protected function sendToSaveModule($request, $form, $content = null)
	{
		if (!$content) {
			$content = new Content();
		}

		$form->setInputFilter($content->getInputFilter());
		$form->setData($request->getPost());

		if ($form->isValid()) {
			$content->exchangeArray($form->getData());
			$content->date = date("Y-m-d H:i:s");

			if ($content->author != $this->user_details->id) {
				$content->author = $this->user_details->id;
			}

			return $this->getContentTable()->saveContent($content);
		}
		return false;
	}

	/*
	* getContentTable
	*
	* return @String
	*/
	public function getContentTable()
	{
		if (!$this->contentTable) {
			$sm = $this->getServiceLocator();
			$this->contentTable = $sm->get('Admin\Model\ContentTable');
		}
		return $this->contentTable;
	}
}
