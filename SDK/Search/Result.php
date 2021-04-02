<?php
namespace Coveo\Search\SDK\Search;

use Coveo\Search\SDK\Response;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/
class Result extends Response
{
    const FALLBACK_RESPONSE_TOTAL_TIME = 0;
    const FALLBACK_RESPONSE_TOTAL_RESULTS = 0;
    const FALLBACK_RESPONSE_ORIGINAL_SEARCH_STRING = "";
    const FALLBACK_RESPONSE_FIXED_SEARCH_STRING = "";
    const FALLBACK_RESPONSE_PARENT_SEARCH_ID = null;

    public function __construct(Response $response = null)
    {
        parent::__construct();

        if ($response) {
            $rawResponse = $response->getResponse();
            $this->setResponse($rawResponse);
        }
    }

    public function getResults()
    {
        if($this->isValid()){
         // error_log(json_encode($this->_response));
            return $this->_response->results;
        }else{
            return array();
        }
    }

    public function setQuery($query)
    {
      $this->currentQuery = $query;
    }

    public function getTotalTime()
    {
        if($this->isValid()){
            return $this->_response->duration;
        }else{
            return self::FALLBACK_RESPONSE_TOTAL_TIME;
        }
    }

    public function getSearchId()
    {
        return $this->getObjectId();
    }

    public function getTotalResults()
    {
        if($this->isValid()){
            return $this->_response->totalCountFiltered;
        }else{
            return self::FALLBACK_RESPONSE_TOTAL_RESULTS;
        }
    }

    public function getOriginalSearchString()
    {
        if($this->isValid()){
            return $this->currentQuery;
        }else{
            return self::FALLBACK_RESPONSE_ORIGINAL_SEARCH_STRING;
        }
    }

    public function getFixedSearchString()
    {
      //This is new: "changedQuery" : {    "originalQuery" : "bagks",    "correctedQuery" : "bags"  },
        if($this->isValid() && isset($this->_response->changedQuery)){//} && sizeof($this->_response->queryCorrections)>0){
          //"queryCorrections" : [ {  "correctedQuery" : "bags",
            return $this->_response->changedQuery->correctedQuery;
        }else{
            return self::FALLBACK_RESPONSE_FIXED_SEARCH_STRING;
        }
    }

    public function getRankCollection()
    {
        if($this->isValid()){
            $rankCollection = array();
            $results = $this->getResults();
            error_log('In GetRankCollection');
            foreach ($results as $result) {
              error_log('In GetRankCollection result');
                $reskey = $result->raw->sku;
                $resval = $result->score;
                $rankCollection[$resval] = $reskey;
            }
            return $rankCollection;
        }else{
            return array();
        }
    }

    public function getRedirect()
    {
        if($this->isValid() && isset($this->_response->triggers) && sizeof($this->_response->triggers)>0){
          if ($this->_response->triggers[0]->type=='redirect') {
            return $this->_response->triggers[0]->content;
          } else return null;
            //return $this->_response->data->redirect;
        }else{
            return null;
        }
    }

    public function getSimilarResultsAlert()
    {
        if($this->isValid() && isset($this->_response->data) && isset($this->_response->data->ai) && isset($this->_response->data->ai->similarResults)){
            return $this->_response->data->ai->similarResults;
        }else{
            return null;
        }
    }

    public function isSearchAvailable()
    {
        return isset($this->_response->results) && $this->getRedirect() === null;
    }

    public function isResultEmpty(){
        if (isset($this->_response->results)) {
            $products = $this->getResults();
            return sizeof($products) <= 0;
        }

        return false;
    }

}
