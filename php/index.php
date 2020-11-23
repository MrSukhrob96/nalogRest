<?php

    function prepareXml($data, $postKey = 1)
    {
    	if($postKey === 2)
    	{
    		$account = "";

    		foreach ($data['accounts'] as $key => $value) 
    		{
    			$account .= "<account account_num='" . $value['acct'] . "' account_currency='" . $value['curr'] . "'/>";
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
    			$account .= "<account account_num='" . $value['acct'] . "' account_currency='" . $value['curr'] . "'/>";
    		}

					$xml = "<xml><info><requestType>3</requestType></info>
						<request>
							<inn>".$data['inn']."</inn>
							<bank_branch_inn>".$data['bank_branch_inn']."</bank_branch_inn>
							<doc_date>".$data['dereg_date']."</doc_date>
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
        $sign = SHA1(MD5($xml)."3452435345345345");
        $url = "http://345345345345:888/eSERVICE.aspx?username=34534534534&sign=".$sign."";
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
					 array_push($array, ['currency'=>$val]);
				 }
			 }
		 }
		 return $array;
	}


	function firstPostCloseAcct($inn)
	{

		$xml = prepareXml($inn);
		$url = prepareUrl($xml);
		$data = sendRequest($xml, $url);

		$xml_file = iconv('utf-8', 'utf-16', $data);

		$document = new DOMDocument();
		$document->loadXml($xml_file);

		$ein = $document->getElementsByTagName('ein')->item(0)->nodeValue;
		$name = $document->getElementsByTagName('FullName')->item(0)->nodeValue;

		$arr = xmlFileReader("Z:".$inn.".xml");
		if(!$arr){
			echo json_encode("error");
		}
		array_push($arr, ['ein'=>$ein], ['name'=> $name]);
		echo json_encode($arr);		
	}
	
	function resultCloseData($data)
	{
		$arr = array();
		$dom = new DOMDocument(); 
		$dom->loadXML(iconv("utf-8", "utf-16", $data)); 
		$result = $dom->getElementsByTagName('errorCode')->item(0)->nodeValue;
		$response = $dom->getElementsByTagName('account');
		foreach ($response as $element) { 
			array_push($arr, ["result" => $result, "id" => $element->getAttribute('id'), "account_num" => $element->getAttribute('account_num'), "dereg_date" => $element->getAttribute('dereg_date')]);
		}
		return $arr;
	}

	function secondPostCloseAcct($data)
	{
		$xml = prepareXml($data, 2);
		$url = prepareUrl($xml);
		$data = sendRequest($xml, $url);	
		$arr = resultCloseData($data);
		echo json_encode($arr);
	}


	function resultCreateData($data)
	{
		$arr = array();
		$dom = new DOMDocument(); 
		$dom->loadXML(iconv("utf-8", "utf-16", $data)); 
		$result = $dom->getElementsByTagName('errorCode')->item(0)->nodeValue;
		$response = $dom->getElementsByTagName('account');
		foreach ($response as $element) { 
			array_push($arr, ["result" => $result, "id" => $element->getAttribute('id'), "account_num" => $element->getAttribute('account_num'), "dereg_date" => $element->getAttribute('dereg_date')]);
		}
		return $arr;
	}


	function firstPostCreateAcct($inn)
	{

		$xml = prepareXml($inn);
		$url = prepareUrl($xml);
		$data = sendRequest($xml, $url);

		$xml_file = iconv('utf-8', 'utf-16', $data);

		$document = new DOMDocument();
		$document->loadXml($xml_file);

		$bank_branch_inn = $document->getElementsByTagName('ein')->item(0)->nodeValue;
		$name = $document->getElementsByTagName('FullName')->item(0)->nodeValue;

		$xml1 = prepareXml($inn, 3);
		$url1 = prepareUrl($xml1);
		$data1 = sendRequest($xml1, $url1);

		$xml_file1 = iconv('utf-8', 'utf-16', $data1);
		$document1 = new DOMDocument();
		$document1->loadXml($xml_file1);
		$authCode = $document1->getElementsByTagName('authCode')->item(0)->nodeValue;

	
		$arr = xmlFileReader("Z:".$inn.".xml");

		if(!$arr){
			echo json_encode("error");
		}

		array_push($arr, ['bank_branch_inn'=>$bank_branch_inn], ['name'=> $name], ['authCode'=>$authCode]);

		echo json_encode($arr);		
	}

	function secondPostCreateAcct($data)
	{
		$xml = prepareXml($data, 4);
		$url = prepareUrl($xml);
		$data = sendRequest($xml, $url);
		
		$xml_file = iconv('utf-8', 'utf-16', $data);
		$document = new DOMDocument();
		$document->loadXml($xml_file);
		$ein = $document->getElementsByTagName('errorCode')->item(0)->nodeValue;

		echo json_encode(($ein));
	}

?>
