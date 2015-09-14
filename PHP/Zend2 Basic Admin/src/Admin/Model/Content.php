<?php

namespace Admin\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Content implements InputFilterAwareInterface
{
	public $id;
	public $title;
	public $content;
	public $date;
	public $author;
	protected $inputFilter;

	public function exchangeArray($data)
	{
		
		$this->id     = (isset($data['id'])) ? $data['id'] : null;
		$this->title = (isset($data['title'])) ? $data['title'] : null;
		$this->content = (isset($data['content'])) ? $data['content'] : null;
		$this->date = (isset($data['date'])) ? $data['date'] : null;
		$this->author = (isset($data['author'])) ? $data['author'] : null;
	}

	// Add the following method:
	public function getArrayCopy()
	{
		return get_object_vars($this);
	}


	// Add content to this method:
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception("Not used");
	}



	public function getInputFilter()
	{
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$factory     = new InputFactory();

			$inputFilter->add($factory->createInput(array(
					'name'     => 'title',
					'required' => true,
					'filters'  => array(
							array('name' => 'StripTags'),
							array('name' => 'StringTrim'),
					),
					'validators' => array(
							array(
									'name'    => 'StringLength',
									'options' => array(
											'encoding' => 'UTF-8',
											'min'      => 1,
											'max'      => 100,
									),
							),
					),
			)));

			$inputFilter->add($factory->createInput(array(
					'name'     => 'content',
					'required' => true,
					'filters'  => array(
							array('name' => 'StripTags'),
							array('name' => 'StringTrim'),
					),
					'validators' => array(
							array(
									'name'    => 'StringLength',
									'options' => array(
											'encoding' => 'UTF-8',
											'min'      => 3,
											'max'      => 100,
									),
							),
					),
			)));

			$this->inputFilter = $inputFilter;
		}

		return $this->inputFilter;
	}
}