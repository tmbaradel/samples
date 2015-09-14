<?php


class Views extends Page{

    public static $db = array(

    );

    public static $description = 'Views page';

    public static $has_many = array(
        'ViewsStorys' => 'ViewsStory'
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
            'Title' => 'Title',
            'Date' => 'Date'
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
        $gridfield = new GridField("ViewStories", "Views Stories", $this->ViewsStorys()->sort("Date DESC"), $gridFieldConfig);
        $fields->addFieldToTab('Root.Main', $gridfield);

        return $fields;
    }
}

class Views_Controller extends Page_Controller
{
    private $last_saved;
    /*
   * getSearch
   *
   * return search
   *
   * @return (ArrayList)
   */
    public function getSearch()
    {
        $stories = ViewsStory::get();
        if($this->isGet('search') || $this->isGet('year')) {
            $search_query =  '';
            $year_query = '';

            if($this->isGet('search')) {
                $search = addslashes($_GET['search']);
                $search_query = ($search != '') ? "(Title LIKE '%".$search."%' OR Content LIKE '%".$search."%' OR ContentHidden LIKE '%".$search."%')" : "";
            }

            if($this->isGet('year')) {
                $year_current = (int)addslashes($_GET['year']);
                $year_next = (int)addslashes($_GET['year'])+1;
                $year_query = ($year_current!="") ? " ( Date >= '".$year_current."-01-01' AND Date <= '".$year_next."-01-01')" : "";
            }

            $and_query = ($search_query && $year_query)? ' AND ': '';

            $src = $stories->where($search_query.$and_query.$year_query)->sort('Date DESC');

            if($src->count()) {
                $this->last_saved = $src;
                return $src;
            }
            else {
                return false;
            }
        }
        else {
            $this->last_saved = $stories->sort('Date DESC');
            return $this->last_saved;
        }
    }

    /*
  * isGet
  *
  * check if get search
  *
  * @return (Boolean)
  */
    public function isGet($search)
    {
        if(array_key_exists($search, $_GET)){
            return true;
        }
        return false;
    }


    /*
     * getOptions
     * get options with years
     * @return (String)
     */
    public function getOptions()
    {
        $opt = '';
        $stories = ViewsStory::get();
        $ret = $stories->sort('Date DESC');
        if($ret->count()) {
            $first = ($this->cleanYear($ret->first()->Date) == null ) ? date("Y") : $this->cleanYear($ret->first()->Date) ;
            $last =  ($this->cleanYear($ret->last()->Date) == null ) ? '2012' : $this->cleanYear($ret->last()->Date);

            for($i = $first ; $i>= $last; $i--) {
                $opt.='<option value="'.$i.'">'.$i.'</option>';
            }
        }

        return $opt;
    }


    /*
    * clean year from date
    * @return (String)
    */
    protected function cleanYear($date)
    {
        $ex = explode("-",$date);
        return (int)$ex[0];
    }

    /*
       * getNumbers
       * get options with years
       * @return (Int)
   */
    public function getStoryNumber()
    {
        if($this->last_saved->count()){
            return $this->last_saved->count();
        }
        return false;
    }
}
