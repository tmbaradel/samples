<?php

namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;

class UserTable
{
	protected $tableGateway;

        protected $salt = '89a87fb&';
        
        protected $db;
        
		public function __construct(TableGateway $tableGateway)
		{
			$this->tableGateway = $tableGateway;
	        $this->db = new Sql($tableGateway->getAdapter());
		}
        
        public function fetchAll()
        {
            $resultSet = $this->tableGateway->select();
            (array)$resultSet;
            return $resultSet;
        }
        
        public function getUserById($id)
        {
        	$id  = (int) $id;
        	$rowset = $this->tableGateway->select(array('id' => $id));
        	
        	$row = $rowset->current();
        	if($row){
        		$row->modules = $this->getUserModules($row->id);
        		return $row;
        	}
        	return $row;
        }
        
        public function getTableFileds()
        {
        	$meta = new Metadata($this->db->getAdapter());
        	$table = $meta->getTable($this->tableGateway->getTable());
        	$return = array();
        	foreach($table->getColumns() as $col){
        		$return[$col->getName()] = "";
        	}
        	return $return;
        }
        
        
        public function getUserByUsername($username)
        {
        	$rowset = $this->tableGateway->select(array('username' => $username));
        	 
        	$row = $rowset->current();
        	
        	return $row;
        }
        
        public function getUser($username, $password)
        {
            $rowset = $this->tableGateway->select(array('username' => $username,
                                                        'password' => $this->_getSecuredPassword($password)
            									  ));
            $row = $rowset->current();
            
            if($row){
                $row->modules = $this->getUserModules($row->id);
                return $row;
            }
            
            return false;
            
        }
        
        protected function getUserModules($id)
        {
            $sql = $this->db->select()
                            ->from('user_module')
                            ->join('module', 'user_module.module = module.id')
                            ->where(array('user'=>$id))
                            ->order(array("module.order"=>'asc'));
            
            $state = $this->db->prepareStatementForSqlObject($sql);
            $result = $state->execute();

            
            //check count
            if(count($result->count())>0){
                $user_modules =array();
                foreach($result as $res){
                	$user_modules[] = $res;
                }
                return json_encode($user_modules);
            }
            
            return false;
            
        }
        
        public function saveUser(User $user)
        {
        	$data = array(
        			'username' => $user->username,
        			'password'  => $this->_getSecuredPassword($user->password),
        			'email'		=>$user->email
        	);
        	
        	$id = (int)$user->id;
        	if ($id == 0) {
        		if(!$this->getUserByUsername($data["username"])){
        			$this->tableGateway->insert($data);
        			//get last id inserted
        			$lastId = $this->db->getAdapter()->getDriver()->getLastGeneratedValue();
        			//get modules from the inputfilter obj
        			$this->saveUserModules($lastId, $user->getInputFilter()->get('Modules')->getValue() );
        		}
        		else{
        			return -1;
        		}
        	} 
        	else {
        		if ($this->getUserById($id)) {
        			$this->tableGateway->update($data, array('id' => $id));
        			//get modules from the inputfilter obj
        			$this->saveUserModules($id, $user->getInputFilter()->get('Modules')->getValue() );
        		} 
        		else {
        			throw new \Exception('Form id does not exist');
        		}
        	}
        }
        
        public function deleteUser($id)
        {
        	$this->tableGateway->delete(array('id' => $id));
        }
        
        protected function _getSecuredPassword($password)
        {
            $halfpass = substr($password, 0,ceil(strlen($password)/2));
            
            return md5($password.$this->salt.$halfpass);
        }
        
        protected function saveUserModules($id,$new_mod)
        {
        	$del_sql = $this->db->delete()
        					->from('user_module')
        					->where(array('user'=>$id));
        	$this->db->prepareStatementForSqlObject($del_sql)->execute();
        	
        	$to_insert = array();
        	//new module
        	foreach($new_mod as $mod){
        		$to_insert = array("user"=>$id,"module"=>$mod);
        		$ins_sql =   $this->db->insert('user_module')->values($to_insert);
        		$this->db->prepareStatementForSqlObject($ins_sql)->execute();
        	}
        	
        }
}
