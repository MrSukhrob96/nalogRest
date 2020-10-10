<?php

    function prepareXml($data, $postKey = 1)
    {
    	if($postKey === 2)
    	{
    		$account = "";

    		foreach ($data['accounts'] as $key => $value) 
    		{
    			$account .= "<account account_num='" . $value['account_num'] . "' account_currency='" . $value['account_currency'] . "'/>";
    		}

				
			$xml = "<xml><info><requestType>12</requestType></info>
						<request>
							 <inn>".$data['inn']."</inn>
							 <bank_branch_inn>".$data['bank_branch_inn']."</bank_branch_inn>
							 <dereg_date>".$data['dereg_date']."</dereg_date>
							 <dereg_reason_id>".$data['dereg_reason_id']."</dereg_reason_id>
							 <dereg_reason_desc>".$data['dereg_reason_desc']."</dereg_reason_desc>
							 <bank_branch_operator>".$data['bank_branch_operator']."</bank_branch_operator>
							 <accounts>".$account."</accounts>
						</request>
					</xml>";
		}

		if( $postKey === 1)
		{
			$xml = "<xml version='1.0' encoding='utf-8'><info><requestType>1</requestType></info><request><inn>".$data."</inn></request></xml>";
		}
		
		if( $postKey === 3)
		{
			$xml = "<xml><info><requestType>2</requestType></info><request><inn>".$data."</inn></request></xml>";
		}


		if ($postKey === 4)
		{
			$account = "";

    		foreach ($data['accounts'] as $key => $value) 
    		{
    			$account .= "<account account_num='" . $value['account_num'] . "' account_currency='" . $value['account_currency'] . "'/>";
    		}

					$xml = "<xml><info><requestType>3</requestType></info>
						<request>
							<inn>".$data['inn']."</inn>
							<bank_branch_inn>".$data['bank_branch_inn']."</bank_branch_inn>
							<doc_date>".$data['doc_date']."</doc_date>
							<doc_num>".$data['doc_num']."</doc_num>
							<bank_branch_director>".$data['bank_branch_director']."</bank_branch_director>
							<bank_branch_operator>".$data['bank_branch_operator']."</bank_branch_operator>
							<authCode>".$data['authCode']."</authCode>
							<accounts>".$account."</accounts>
						</request>
					</xml>";
		}
		
        return $xml;
    }

    function prepareUrl($xml)
    {
        $sign = SHA1(MD5($xml)."cb7b01a7c198234a66a2dcfc6d17d9284a95f0dca513bb04a33a1f6651bbed1e");
        $url = "http://10.50.63.196:888/eSERVICE.aspx?username=510022365&sign=".$sign."";
        return $url;
    }

    function sendRequest($xml, $url)
    {
        $headers = array(
            "Content-type: text/xml; charset=utf-8",
            "Content-length: " . strlen($xml),
            "Connection: close",
        );

        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $data = curl_exec($ch); 
        curl_close($ch);
        return $data;
    }


    function responseData($inn, $postKey=1)
    {
		$xml = prepareXml($inn, $postKey);
		$url = prepareUrl($xml);
		$data = sendRequest($xml, $url);

		if (!$data){
			return false;
		}

		return $data;
    }


    function xmlFileReader($file)
	{
		 $array = array();
		 $arr = json_decode(json_encode(simplexml_load_file($file)),true);
		 foreach($arr as $key => $value)
		 {
			 if(!is_array($value))
			 {
				 array_push($array, [$key=> $value]);
			 } 
			 else 
			 {
				 foreach( $value as $k => $val)
				 {
					 array_push($array, ['accounts'=>$val]);
				 }
			 }
		 }
		 return $array;
	}


	function firstPostCloseAcct($inn)
	{

		$data = responseData($inn);
		
		if(!$data){
			echo json_encode("NO1");
			return;
		}

		$xml_file = iconv('utf-8', 'utf-16', $data);
		$document = new DOMDocument();
		$document->loadXml($xml_file);

		$ein = $document->getElementsByTagName('ein')->item(0)->nodeValue;
		$name = $document->getElementsByTagName('FullName')->item(0)->nodeValue;

		$arr = xmlFileReader("Z:".$inn.".xml");

		if($arr){
			echo json_encode("NO2");
			return;
		}
		array_push($arr, ['bank_branch_inn'=>$ein], ['name'=> $name]);
		echo json_encode($arr);		
	}

	// function resultCloseData($data)
	// {
	// 	$arr = array();
	// 	$dom = new DOMDocument(); 
	// 	$dom->loadXML(iconv("utf-8", "utf-16", $data)); 
	// 	$result = $dom->getElementsByTagName('errorCode')->item(0)->nodeValue;
	// 	$response = $dom->getElementsByTagName('account');

	// 	foreach ($response as $element) { 
	// 		array_push($arr, ["result" => $result, "id" => $element->getAttribute('id'), "account_num" => $element->getAttribute('account_num'), "dereg_date" => $element->getAttribute('dereg_date')]);
	// 	}
	// 	return $arr;
	// }

	// function secondPostCloseAcct($data)
	// {
	// 	$data = responseData($inn, 2);	
	// 	$arr = resultCloseData($data);
	// 	echo json_encode($arr);
	// }

