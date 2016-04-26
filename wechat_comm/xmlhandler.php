<?php
/** 
  *XML handler OBJECT
  */
Class XMLhandler {
	var $recv_txt; //received XML raw text
	var $data; //formatted XML data in $data->key form

	//constructor receives raw data and store into $data
	function XMLhandler($rawdata){
		
		$this->recv_txt=$rawdata;
		
		//handle received XML, for description see w3school
		$this->data=simplexml_load_string($this->recv_txt);
	}
	
	// get data
	function getData(){
		return $this->data;
	}
}

?>