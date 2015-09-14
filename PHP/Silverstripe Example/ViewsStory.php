<?php
class ViewsStory extends DataObject {

    static $db = array (
        'Title' => 'Varchar(255)',
        'Content' =>'HTMLText',
        'ContentHidden' =>'HTMLText',
        'Date' => 'Date',
    );

    static $default_sort = 'Date DESC';


    static $has_one = array (
        'Views' => 'Views',
        'StoryImage'=> 'Image',
        'HiddenImage' => 'Image',
        'StoryFile' =>'File'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $uploadFieldOne = new UploadField(
            $name = 'StoryImage',
            $title = 'Story Image'
        );

        $uploadFieldOne->setFolderName('Living');

        $uploadFieldTwo = new UploadField(
            $name = 'HiddenImage',
            $title = 'HiddenImage'
        );

        $uploadFieldTwo->setFolderName('Living');

        $uploadFieldFile = new UploadField(
            $name = 'StoryFile',
            $title = 'News File'
        );

        $uploadFieldFile->setFolderName('NewsViewsFiles');
        $fields->addFieldToTab('Root.Main', $uploadFieldFile);


        $fields->addFieldToTab('Root.Main',DateField::create('Date')->setConfig('dateformat', 'dd-MM-yyyy')->setConfig('showcalendar', true));
        $fields->addFieldToTab('Root.Main', $uploadFieldOne);
        $fields->addFieldToTab('Root.Main', $uploadFieldTwo);
        $fields->removeFieldFromTab("Root.Main","CultureID");
        return $fields;
    }

    public function getHumanDate(){
        if($this->Date){
            return date("d F Y",strtotime($this->Date));
        }
        return '';
    }
}
