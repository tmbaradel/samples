<?php


class Team extends Page{

    public static $db = array(

    );

    public static $description = 'Team Page';

    public static $has_many = array(
        'TeamMembers' => 'TeamMember'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        //remove main content
        $fields->removeFieldFromTab("Root.Main","Content");


//      GRID content for members
        $gridFileds = new GridFieldDataColumns();
        $gridFileds->setDisplayFields(array(
            'ID'    =>'Id',
            'Member' => 'Member',
            'Role' => 'Role',
            'Order' => 'Order'
        ));
//
        $gridFieldConfig = GridFieldConfig::create()->addComponents(
            new GridFieldToolbarHeader(),
            new GridFieldAddNewButton('toolbar-header-right'),
            new GridFieldSortableHeader('Member'),
            $gridFileds,
            new GridFieldPaginator(55),
            new GridFieldEditButton(),
            new GridFieldDeleteAction(),
            new GridFieldDetailForm()
        );
//
        $gridfield = new GridField("TeamMembers", "Team Members", $this->TeamMembers()->sort("Order ASC"), $gridFieldConfig);
        $fields->addFieldToTab('Root.Main', $gridfield);

        return $fields;
    }
}

class Team_Controller extends Page_Controller
{

}