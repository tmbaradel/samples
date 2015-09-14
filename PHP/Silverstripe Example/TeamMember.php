<?php
class TeamMember extends DataObject {
    static $db = array (
        'Member' => 'Varchar',
        'Role' =>'Varchar',
        'Quote' => 'Text',
        'Color' =>'Varchar',
        'Order' => 'Int',
        'Link' => 'Int'
    );


    static $default_sort = 'Order ASC';

    static $has_one = array (
        'Team' => 'Team',
        'ProfileImage' => 'Image',
    );

    protected $standard = array();

    protected $colors = array(
        'purple' =>'Purple',
        'pink' => 'Pink',
        'orange' => 'Orange',
        'brown' => 'Brown',
        'blue' => 'Blue',
        'blue-2'  => 'Light Blue',
        'green' =>'Green',
        'green-2' =>'Dark Green',
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeFieldFromTab("Root.Main","TeamID");

        $lnk_bio =  new TreeDropdownField("Link", "Bio Link", "SiteTree");

        $dropdown = new DropdownField('Color', 'Hover Color', $this->colors);

        $uploadFieldOne = new UploadField(
            $name = 'ProfileImage',
            $title = 'Profile Image'
        );

        $uploadFieldOne->setFolderName('TeamImages');

        $fields->addFieldToTab('Root.Main', $uploadFieldOne);
        $fields->addFieldToTab("Root.Main",$dropdown);
        $fields->addFieldToTab("Root.Main",$lnk_bio);
        $fields->removeFieldFromTab("Root.Main","TeamPageID");

        return $fields;
    }

    public function getPgAll()
    {
        return DataObject::get_by_id("SiteTree", $this->Link );
    }

    public function getDashName()
    {
        if(!in_array($this->Member,$this->standard)) {
            $name = str_replace(" ", "-",trim($this->Member));
            return strtolower( preg_replace("/[^a-zA-Z0-9\-]+/", "", $name));
        }
        else{
            return 'comapny-name';
        }
    }
}
