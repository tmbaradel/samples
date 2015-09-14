<?php

namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Metadata\Metadata;

class AdminmoduleTable
{
	protected $tableGateway;

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
        
        public function getContent($id)
        {
            $rowset = $this->tableGateway->select(array('id' => $id));
            return $rowset->current();
        }

        
        public function saveModule(Content $module)
        {
        	$data = array(
        			'name' => $module->name,
        			'order'  => $module->order,
        			'sections'		=>$module->sections,
        			);
        	
        	$id = (int)$module->id;
        	if ($id == 0) {
        		$this->tableGateway->insert($data);
        	} 
        	else {
        		if ($this->getContent($id)) {
        			$this->tableGateway->update($data, array('id' => $id));
        		} 
        		else {
        			throw new \Exception('Form id does not exist');
        		}
        	}
        }
        
        public function deleteContent($id)
        {
        	$this->tableGateway->delete(array('id' => $id));
        }
        
        
        
        
}

