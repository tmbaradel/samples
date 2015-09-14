<?php


class RestPage extends Page {


    public static $description = 'Rest Page';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        return $fields;
    }

}

class RestPage_Controller extends Page_Controller
{

    private static $allowed_actions = array('json','import','xml');



    public function json(SS_HTTPRequest $request)
    {
        $data = array();
        $request_fnc = $request->allParams();
        $req_body = json_decode($request->getBody());
        $data = $this->getNext($req_body);
        return json_encode($data);
    }




    public function import(SS_HTTPRequest $request)
    {
        $all_params = $request->allParams();
        if($all_params['ID']){
            $this->import_db($all_params['ID']);
        }
        exit;
    }

    /**
     * get next 3 elements
     *
     * @param (char)
     * @return (array)
     */
     protected function getNext($req)
     {
         $stories = $this->getAdapter($req->page);
         if($req->form->year || $req->form->search){
             $search_query =  '';
             $year_query = '';
             if($req->form->search){
                 $search = addslashes($req->form->search);
                 $search_query = ($search != '') ? "(Title LIKE '%".$search."%' OR Content LIKE '%".$search."%' OR ContentHidden LIKE '%".$search."%')" : "";
             }
             if($req->form->year){
                 $year_current = (int)addslashes($req->form->year);
                 $year_next = (int)addslashes($req->form->year)+1;
                 $year_query = ($year_current!="") ? " ( Date >= '".$year_current."-01-01' AND Date <= '".$year_next."-01-01')" : "";
             }
             $and_query = ($search_query && $year_query)? ' AND ': '';
             $src = $stories->where($search_query.$and_query.$year_query)->sort('Date DESC');
             if($src->count()){
                 return $src;
             }
             else{
                 return false;
             }
         }
         else{
             return $this-> getArrayFromResults($stories->sort('Date DESC')->limit(3,(int)$req->present)->toArray());
         }
     }


     protected function getAdapter($page)
     {
        $adapter = false;
        switch($page){
            case 'News':
            $adapter = NewsStory::get();
            break;
            case 'Views':
            $adapter = ViewsStory::get();
            break;
            case 'Ratings':
            $adapter = RatingsStory::get();
            break;
        }
        return $adapter;
     }

     protected function getArrayFromResults($qr_res)
     {
        $ret = array();
        foreach($qr_res as $row){
            $tmp = array();
            $tmp['created']=$row->Created;
            $tmp['lastedit']=$row->LastEdited;
            $tmp['id'] = $row->ID;
            $tmp['title'] = $row->Title;
            $tmp['Content'] = $row->Content;
            $ret[] = $tmp;
        }
        return $ret;

     }

     protected function import_db($tbl)
     {
         switch($tbl){
             case 'news':
                 require_once(dirname(__FILE__).'/library/import_old_site/News_imp.php');
                 $this->importArr($news, 'News');
             break;
             case 'views':
                 require_once(dirname(__FILE__).'/library/import_old_site/Views_imp.php');
                 $this->importArr($news, 'Views');
             break;

         }
     }

     protected function importArr($arr,$adapt)
     {
        foreach($arr as $row){
                $dt = explode(" ",$row['Date']);
                if($adapt == 'News'){
                    $story =  new NewsStory();
                    $story->ClassName = 'NewsStory';
                    $story->NewsID  = 20;
                }
                elseif($adapt == 'Views'){
                    $story =  new ViewsStory();
                    $story->ClassName = 'ViewsStory';
                    $story->ViewsID  = 21;
                }
                $story->Created = $row['Date'];
                $story->LastEdited = $row['Date'];
                $story->Title = strip_tags($row['Title']);
                if(strip_tags($row['Summary']) != ""){
                    $cnt =strip_tags($row['Summary']);
                    $cnt =  $this->cleanText($cnt);
                    $des = '<p>'.$row['Description'].'</p>';
                }
                else{
                    $pas = strip_tags($row['Description']);
                    $tmp = substr($pas, 0, 85);
                    $tmp = substr($tmp,0,strrpos($tmp, " "));
                    $cnt = $this->cleanText($tmp);
                    $des = '<p>'.$row['Description'].'</p>';
                }
                $story->Content = $cnt;
                $story->ContentHidden = $des;
                $story->StoryImageID = 183;
                $story->Date = $dt[0];
                //$story->write();

         }
         echo 'Done '.$adapt;exit;
     }

     protected function cleanText($text)
     {
        $toClean = array('&amp;'=>'&',
                         '&ndash;'=>'-',
                         '&hellip;'=>'',
                         '&nbsp;'=>' ',
                         '&rsquo;'=>"'");
        $ret = $text;
        foreach($toClean as $key=>$val){
            $ret = str_replace($key, $val, $ret);
        }
        return $ret.'...';
     }

}
