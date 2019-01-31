<html>
<head>
<title>EDI Reader - Showing the contents of your EDI file</title>
<link rel="shortcut icon" href="logo.gif" type="image/x-icon" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="dragtable.css" />
<LINK REL=StyleSheet HREF="style.css" TYPE="text/css">


<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
<script src="jquery.dragtable.js"></script>

<!-- only for jquery.chili-2.2.js -->
<script src="http://code.jquery.com/jquery-migrate-1.1.1.js"></script>
<script type="text/javascript" src="http://akottr.github.io/js/jquery.chili-2.2.js"></script>


<script type="text/javascript">
  $(document).ready(function() {

    $('.defaultTable').dragtable();

    $('#footerTable').dragtable({excludeFooter:true});

    $('#onlyHeaderTable').dragtable({maxMovingRows:1});

    $('#persistTable').dragtable({persistState:'/someAjaxUrl'});

    $('#handlerTable').dragtable({dragHandle:'.some-handle'});

    $('#constrainTable').dragtable({dragaccept:'.accept'});

    $('#customPersistTable').dragtable({persistState: function(table) {
        table.el.find('th').each(function(i) {
          if(this.id != '') {table.sortOrder[this.id]=i;}
        });
        $.ajax({url: '/myAjax?hello=world',
                data: table.sortOrder});
      }
    });

    $('#localStorageTable').dragtable({
        persistState: function(table) {
          if (!window.sessionStorage) return;
          var ss = window.sessionStorage;
          table.el.find('th').each(function(i) {
            if(this.id != '') {table.sortOrder[this.id]=i;}
          });
          ss.setItem('tableorder',JSON.stringify(table.sortOrder));
        },
        restoreState: eval('(' + window.sessionStorage.getItem('tableorder') + ')')
    });

  });
</script>
</head>
<body background="fundo.gif">
<?php


function fnc_obter_linha_segmento ($p_arquivo, $p_segmento, $p_tam_segmento=3)
{
	 $ini = 0;
	 $fim = 0;
	 for( $i = 1; $i <= substr_count($p_arquivo, "'"); $i++ ) {			   
	   $fim = strpos($p_arquivo,"'",$fim)+1;
	   $segmento = substr($p_arquivo,$ini,$fim-$ini);
	   $seg = substr(trim($segmento),0,$p_tam_segmento);
	   if ($seg==$p_segmento) {
		return $segmento;   
	   }
	   $ini = $fim;
	   
	 }
	return "Segmento nao encontrado";
}

function fnc_edi_recupera_compnte ($p_segmento,$p_num_elemento, $p_num_componente)
{
     //obtem o elemento
	 $ini = 1+strpos($p_segmento, "+");     	 	 
	 for( $i = 1; $i <= substr_count($p_segmento, "+"); $i++ ) {		 
	     $fim = strpos($p_segmento, "+",$ini)+1;		 
         if ($i == $p_num_elemento) {
			if ($i==substr_count($p_segmento, "+")){
			   $elemento = substr($p_segmento,$ini,strlen($p_segmento)-$ini-1); 			   
			}			
			else {
				$elemento = substr($p_segmento,$ini,$fim-$ini-1); 			   
			}
		 }
		 $ini = $fim;	     
	 }
	 
	 if (isset($elemento)) {
		$elemento = trim(strtr($elemento,"'"," ")); 
	 } 
	 else {
		return "";
	 }
	 
	 //se nao tem componente retorna o elemento
	 if (substr_count($elemento, ":") == 0) {
		return $elemento; 
	 }
	 else {
		//obtem o componente
		$ini = 0;
	    for( $i = 1; $i <= substr_count($elemento, ":")+1; $i++ ) {		 
	      $fim = strpos($elemento, ":",$ini)+1;		 
            if ($i == $p_num_componente) {
			   if ($i==substr_count($elemento, ":")+1){
			      return substr($elemento,$ini); 			   
			   }			
			   else {
			 	  return substr($elemento,$ini,$fim-$ini-1); 			   
			   }
		  }
		  $ini = $fim;	     
	    }
	 }
}

function fnc_resgata_padrao ($p_arquivo)
{
	return fnc_edi_recupera_compnte(fnc_obter_linha_segmento($p_arquivo,"UNH"),2,1);
}

function fnc_resgata_tipo_movimento($p_codigo)
{
  switch ($p_codigo) {
	 case 46: return "Load";
	 case 44: return "Discharge";
	 case 34: return "GateIn";
	 case 36: return "GateOut";
	 case 42: return "Shift";
	 case 24: return "OnHire";
	 case 25: return "OffHire";
	 case 26: return "Interchange";
	 default: return "Not Identified";
  }  
}

function fnc_resgata_funcao_mensagem($p_codigo)
{
  switch ($p_codigo) {
	 case 2: return "Addition";
	 case 3: return "Deletion";
	 case 4: return "Change";
	 case 5: return "Replace";
	 case 9: return "Original";
	 default: return "Not Identified";
  }  
}

function fnc_resgata_modo_transp($p_codigo)
{
  switch ($p_codigo) {
	 case 1: return "Maritime";
	 case 2: return "Rail";
	 case 3: return "Road";
	 case 4: return "Air";
	 case 8: return "Inland water";
	 default: return "Not Identified";
  }  
}

function fnc_resgata_tipo_id_navio($p_codigo)
{
  switch ($p_codigo) {
	 case 103: return "Call Sign";
	 case 146: return "Lloyd Number";
	 default: return "Not Identified";
  }  
}

function fnc_resgata_qualif_equipamento($p_codigo)
{
  switch ($p_codigo) {
	 case "CN": return "Container";
	 case "BB": return "Break Bulk";
	 case "SW": return "Swapbody";
	 default: return "Not Identified";
  }  
}

function fnc_resgata_tipo_lacre($p_codigo)
{
  switch ($p_codigo) {
	 case "CA": return "Carrier";
	 case "CU": return "Customs";
	 case "QA": return "Quarantine";
	 case "SH": return "Shipper";
	 case "TO": return "Terminal";
	 default: return "Not Identified";
  }  
}

function fnc_resgata_categoria($p_codigo)
{
  switch ($p_codigo) {
	 case 1: return "Continental";
	 case 2: return "Export";
	 case 3: return "Import";
	 case 5: return "Restow";
	 case 6: return "Transhipment";
	 case 12: return "Shortlanded";
	 case 13: return "Overlanded";
	 default: return "Not Identified";
  }  
}

function fnc_resgata_status($p_codigo)
{
  switch ($p_codigo) {
	 case 4: return "Empty";
	 case 5: return "Full";
	 default: return "Not Identified";
  }  
}

function fnc_formata_data($p_data)
{
  //201509151300
  //substr($p_data,-12,4) //ano
  //substr($p_data,-8,2) //mes
  //substr($p_data,-6,2) //dia
  //substr($p_data,-4,2) //hora
  //substr($p_data,-2,2) //minuto
  return substr($p_data,-6,2).'/'.substr($p_data,-8,2).'/'.substr($p_data,-12,4).' '.substr($p_data,-4,2).':'.substr($p_data,-2,2);
}

function fnc_resgata_transp_means($p_codigo)
{
  switch ($p_codigo) {
	 case 1: return "Barge chemical tanker";
	 case 2: return "Coaster chemical tanker";
	 case 3: return "Dry bulk carrier";
	 case 4: return "Deep sea chemical tanker";
	 case 5: return "Gas tanker";
	 case 9: return "Exceptional transport";
	 case 11: return "Ship (for feeder vessels)";
	 case 12: return "Ship tanker";
	 case 13: return "Ocean Vessel";
	 case 21: return "Rail tanker";
	 case 22: return "Rail silo tanker";
	 case 23: return "Rail bulk car";
	 case 25: return "Rail express";
	 case 31: return "Truck";
	 case 33: return "Road silo tanker";
	 case 35: return "Truck/trailer with tilt";
	 default: return "Not Identified";
  }  
}

function fnc_processa_coarri($p_arquivo)
{
  $segmento = fnc_obter_linha_segmento ($p_arquivo, "UNB");
  $ini = strpos($p_arquivo,"UNH",0);
  $fim = strpos($p_arquivo,"UNT",0);
  for( $i = 0; $i <= substr_count($p_arquivo, "UNH")-1; $i++ ) {
	  $parte = substr($p_arquivo,$ini,$fim-$ini);
	  if ($i == 0) {
          $a = ['Sender'=>[fnc_edi_recupera_compnte($segmento,2,0)],
		        'Recipient'=>[fnc_edi_recupera_compnte($segmento,3,0)],
				'DateDoc'=>[fnc_edi_recupera_compnte($segmento,4,1)],
				'TimeDoc'=>[fnc_edi_recupera_compnte($segmento,4,2)],
				'IdSender'=>[fnc_edi_recupera_compnte($segmento,5,0)],
				'Seq'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "BGM"),2,0)],
				'Move'=>[fnc_resgata_tipo_movimento(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "BGM"),1,0))], 
				'FunctionMsg'=>[fnc_resgata_funcao_mensagem(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "BGM"),3,0))], 
				'Voyage'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),2,0)], 				
				'ModeTransp1'=>[fnc_resgata_modo_transp(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),3,0))], 				
				'TranspMeans1'=>[fnc_resgata_transp_means(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),4,0))], 				
				'CarrierCode1'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),5,1)], 				
				'VesselId'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),8,1)], 				
				'TypeId'=>[fnc_resgata_tipo_id_navio(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),8,2))], 				
				'VesselName'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),8,4)],
				'PortLoad'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+9",5),2,1)],
				'PortDischarge'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+11",6),2,1)],
				'Arrival'=>[fnc_formata_data(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DTM+132",7),1,2))],
				'Departure'=>[fnc_formata_data(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DTM+133",7),1,2))],
				'Operator'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "NAD+CF",6),2,0)],
				'Carrier'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "NAD+CA",6),2,0)],
				'Agent'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "NAD+AG",6),2,0)],
				'EquipQualif'=>[fnc_resgata_qualif_equipamento(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),1,0))],
		        'EquipIdent'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),2,0)],
                'SizeType'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),3,1)],
				'Category'=>[fnc_resgata_categoria(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),5,0))],
				'Status'=>[fnc_resgata_status(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),6,0))],
				'Booking'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RFF+BN",6),1,2)],
				'BL'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RFF+BM",6),1,2)],
				'OwnerCargo'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RFF+AAE",7),1,2)],
				'ExecutionDate'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DTM+203",7),1,2)],
				'PositionOnVessel'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+147",7),2,1)],				
				'FinalDest'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+7",5),2,1)],
				'TareWeight'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+T",9),3,2)],				
				'TareWeightUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+T",9),3,1)],				
				'GrossWeight'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+G",9),3,2)],				
				'GrossWeightUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+G",9),3,1)],				
				'Seal'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "SEL"),1,0)],								
				'TypeSeal'=>[fnc_resgata_tipo_lacre(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "SEL"),2,0))],
				'IdealTemp'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TMP"),2,1)],
				'IdealTempUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TMP"),2,2)],
				'MinTemp'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RNG"),2,2)],
				'MaxTemp'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RNG"),2,3)],
				'MinMaxTempUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RNG"),2,1)],
				'OverDmsFrontLen'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,2)],
				'OverDmsFrontWid'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,3)],
				'OverDmsFrontHei'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,4)],
				'OverDmsFrontUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,1)],				
				'OverDmsBackLen'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,2)],
				'OverDmsBackWid'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,3)],
				'OverDmsBackHei'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,4)],
				'OverDmsBackUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,1)],				
				'OverDmsRightLen'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,2)],
				'OverDmsRightWid'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,3)],
				'OverDmsRightHei'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,4)],
				'OverDmsRightUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,1)],				
				'OverDmsLeftLen'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,2)],
				'OverDmsLeftWid'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,3)],
				'OverDmsLeftHei'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,4)],
				'OverDmsLeftUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,1)],				
				'OverDmsGenLen'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,2)],
				'OverDmsGenWid'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,3)],
				'OverDmsGenHei'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,4)],				
				'OverDmsGenUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,1)],												
				'OverDmsExtLen'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,2)],
				'OverDmsExtWid'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,3)],
				'OverDmsExtHei'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,4)],
				'OverDmsExtUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,1)],				
				'ModeTransp2'=>[fnc_resgata_modo_transp(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),3,0))],				
				'TranspMeans2'=>[fnc_resgata_transp_means(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),4,0))],								
				'CarrierCode2'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),8,1)],
				'Responsible'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),8,4)],
	            ];		
	    }
	  else {
		array_push($a["Sender"], fnc_edi_recupera_compnte($segmento,2,0));	
		array_push($a["Recipient"], fnc_edi_recupera_compnte($segmento,3,0));	
		array_push($a["DateDoc"], fnc_edi_recupera_compnte($segmento,4,1));	
		array_push($a["TimeDoc"], fnc_edi_recupera_compnte($segmento,4,2));	
		array_push($a["IdSender"], fnc_edi_recupera_compnte($segmento,5,0));	
		array_push($a["Seq"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "BGM"),2,0));	
		array_push($a["Move"], fnc_resgata_tipo_movimento(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "BGM"),1,0)));	
		array_push($a["FunctionMsg"], fnc_resgata_funcao_mensagem(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "BGM"),3,0)));	
		array_push($a["Voyage"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),2,0));	
		array_push($a["ModeTransp1"], fnc_resgata_modo_transp(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),3,0)));	
		array_push($a["TranspMeans1"], fnc_resgata_transp_means(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),4,0)));	
		array_push($a["CarrierCode1"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),5,1));	
		array_push($a["VesselId"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),8,1));	
		array_push($a["TypeId"], fnc_resgata_tipo_id_navio(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),8,2)));	
		array_push($a["VesselName"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),8,4));	
		array_push($a["PortLoad"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+9",5),2,1));	
		array_push($a["PortDischarge"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+11",6),2,1));	
		array_push($a["Arrival"], fnc_formata_data(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DTM+132",7),1,2)));	
		array_push($a["Departure"], fnc_formata_data(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DTM+133",7),1,2)));	
		array_push($a["Operator"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "NAD+CF",6),2,0));	
		array_push($a["Carrier"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "NAD+CA",6),2,0));	
		array_push($a["Agent"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "NAD+AG",6),2,0));	
		array_push($a["EquipQualif"], fnc_resgata_qualif_equipamento(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),1,0)));	
		array_push($a["EquipIdent"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),2,0));	
        array_push($a["SizeType"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),3,1));	  	
		array_push($a["Category"], fnc_resgata_categoria(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),5,0)));	  	
		array_push($a["Status"], fnc_resgata_status(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),6,0)));	  	
		array_push($a["Booking"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RFF+BN",6),1,2));	  	
		array_push($a["BL"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RFF+BM",6),1,2));	  	
		array_push($a["OwnerCargo"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RFF+AAE",7),1,2));	  	
		array_push($a["ExecutionDate"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DTM+203",7),1,2));	  			
		array_push($a["PositionOnVessel"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+147",7),2,1));	  			
		array_push($a["FinalDest"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+7",5),2,1));	  	
		array_push($a["TareWeight"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+T",9),3,2));	  	
		array_push($a["TareWeightUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+T",9),3,1));	  	
		array_push($a["GrossWeight"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+G",9),3,2));	  	
		array_push($a["GrossWeightUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+G",9),3,1));	  	
		array_push($a["Seal"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "SEL"),1,0));	  	
		array_push($a["TypeSeal"], fnc_resgata_tipo_lacre(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "SEL"),2,0)));	  	
		array_push($a["IdealTemp"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TMP"),2,1));	  	
		array_push($a["IdealTempUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TMP"),2,2));
        array_push($a["MinTemp"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RNG"),2,2));	  		  	
		array_push($a["MaxTemp"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RNG"),2,3));
		array_push($a["MinMaxTempUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RNG"),2,1));
		array_push($a["OverDmsFrontLen"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,2));
		array_push($a["OverDmsFrontWid"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,3));
		array_push($a["OverDmsFrontHei"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,4));		
		array_push($a["OverDmsFrontUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,1));		
		array_push($a["OverDmsBackLen"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,2));
		array_push($a["OverDmsBackWid"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,3));
		array_push($a["OverDmsBackHei"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,4));
		array_push($a["OverDmsBackUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,1));		
		array_push($a["OverDmsRightLen"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,2));
		array_push($a["OverDmsRightWid"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,3));
		array_push($a["OverDmsRightHei"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,4));
		array_push($a["OverDmsRightUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,1));		
		array_push($a["OverDmsLeftLen"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,2));
		array_push($a["OverDmsLeftWid"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,3));
		array_push($a["OverDmsLeftHei"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,4));
		array_push($a["OverDmsLeftUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,1));		
		array_push($a["OverDmsGenLen"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,2));
		array_push($a["OverDmsGenWid"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,3));
		array_push($a["OverDmsGenHei"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,4));
		array_push($a["OverDmsGenUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,1));		
		array_push($a["OverDmsExtLen"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,2));
		array_push($a["OverDmsExtWid"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,3));
		array_push($a["OverDmsExtHei"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,4));
		array_push($a["OverDmsExtUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,1));
		array_push($a["ModeTransp2"], fnc_resgata_modo_transp(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),3,0)));
		array_push($a["TranspMeans2"], fnc_resgata_transp_means(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),4,0)));
		array_push($a["CarrierCode2"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),8,1));
		array_push($a["Responsible"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),8,4));
	  }	
	  $ini = strpos($p_arquivo,"UNH",$fim);
	  $fim = strpos($p_arquivo,"UNT",$ini);
  }  
  return $a;    
}  

function fnc_processa_codeco($p_arquivo)
{
  $segmento = fnc_obter_linha_segmento ($p_arquivo, "UNB");
  $ini = strpos($p_arquivo,"UNH",0);
  $fim = strpos($p_arquivo,"UNT",0);
  for( $i = 0; $i <= substr_count($p_arquivo, "UNH")-1; $i++ ) {
	  $parte = substr($p_arquivo,$ini,$fim-$ini);
	  if ($i == 0) {
          $a = ['Sender'=>[fnc_edi_recupera_compnte($segmento,2,0)],
		        'Recipient'=>[fnc_edi_recupera_compnte($segmento,3,0)],
				'DateDoc'=>[fnc_edi_recupera_compnte($segmento,4,1)],
				'TimeDoc'=>[fnc_edi_recupera_compnte($segmento,4,2)],
				'IdSender'=>[fnc_edi_recupera_compnte($segmento,5,0)],
				'Seq'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "BGM"),2,0)],
				'Move'=>[fnc_resgata_tipo_movimento(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "BGM"),1,0))], 
				'FunctionMsg'=>[fnc_resgata_funcao_mensagem(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "BGM"),3,0))], 
				'Voyage'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),2,0)], 				
				'ModeTransp1'=>[fnc_resgata_modo_transp(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),3,0))], 				
				'TranspMeans1'=>[fnc_resgata_transp_means(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),4,0))], 				
				'CarrierCode1'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),5,1)], 				
				'VesselId'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),8,1)], 				
				'TypeId'=>[fnc_resgata_tipo_id_navio(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),8,2))], 				
				'VesselName'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),8,4)],
				'PortLoad'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+9",5),2,1)],
				'PortDischarge'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+11",6),2,1)],
				'Arrival'=>[fnc_formata_data(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DTM+178",7),1,2))],
				'Departure'=>[fnc_formata_data(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DTM+7",5),1,2))],
				'Operator'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "NAD+CF",6),2,0)],
				'Carrier'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "NAD+CA",6),2,0)],
				'Agent'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "NAD+AG",6),2,0)],
				'EquipQualif'=>[fnc_resgata_qualif_equipamento(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),1,0))],
		        'EquipIdent'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),2,0)],
                'SizeType'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),3,1)],
				'Category'=>[fnc_resgata_categoria(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),5,0))],
				'Status'=>[fnc_resgata_status(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),6,0))],
				'Booking'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RFF+BN",6),1,2)],
				'BL'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RFF+BM",6),1,2)],
				'OwnerCargo'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RFF+AAE",7),1,2)],
				'FinalDest'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+8",5),2,1)],
				'TareWeight'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+T",9),3,2)],				
				'TareWeightUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+T",9),3,1)],				
				'GrossWeight'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+G",9),3,2)],				
				'GrossWeightUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+G",9),3,1)],				
				'Seal'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "SEL"),1,0)],								
				'TypeSeal'=>[fnc_resgata_tipo_lacre(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "SEL"),2,0))],
				'IdealTemp'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TMP"),2,1)],
				'IdealTempUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TMP"),2,2)],
				'MinTemp'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RNG"),2,2)],
				'MaxTemp'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RNG"),2,3)],
				'MinMaxTempUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RNG"),2,1)],
				'OverDmsFrontLen'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,2)],
				'OverDmsFrontWid'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,3)],
				'OverDmsFrontHei'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,4)],
				'OverDmsFrontUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,1)],				
				'OverDmsBackLen'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,2)],
				'OverDmsBackWid'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,3)],
				'OverDmsBackHei'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,4)],
				'OverDmsBackUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,1)],				
				'OverDmsRightLen'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,2)],
				'OverDmsRightWid'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,3)],
				'OverDmsRightHei'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,4)],
				'OverDmsRightUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,1)],				
				'OverDmsLeftLen'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,2)],
				'OverDmsLeftWid'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,3)],
				'OverDmsLeftHei'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,4)],
				'OverDmsLeftUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,1)],				
				'OverDmsGenLen'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,2)],
				'OverDmsGenWid'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,3)],
				'OverDmsGenHei'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,4)],				
				'OverDmsGenUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,1)],												
				'OverDmsExtLen'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,2)],
				'OverDmsExtWid'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,3)],
				'OverDmsExtHei'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,4)],
				'OverDmsExtUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,1)],				
				'ModeTransp2'=>[fnc_resgata_modo_transp(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),3,0))],				
				'TranspMeans2'=>[fnc_resgata_transp_means(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),4,0))],								
				'CarrierCode2'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),8,1)],
				'Responsible'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),8,4)],
	            ];		
	    }
	  else {
		array_push($a["Sender"], fnc_edi_recupera_compnte($segmento,2,0));	
		array_push($a["Recipient"], fnc_edi_recupera_compnte($segmento,3,0));	
		array_push($a["DateDoc"], fnc_edi_recupera_compnte($segmento,4,1));	
		array_push($a["TimeDoc"], fnc_edi_recupera_compnte($segmento,4,2));	
		array_push($a["IdSender"], fnc_edi_recupera_compnte($segmento,5,0));	
		array_push($a["Seq"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "BGM"),2,0));	
		array_push($a["Move"], fnc_resgata_tipo_movimento(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "BGM"),1,0)));	
		array_push($a["FunctionMsg"], fnc_resgata_funcao_mensagem(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "BGM"),3,0)));	
		array_push($a["Voyage"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),2,0));	
		array_push($a["ModeTransp1"], fnc_resgata_modo_transp(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),3,0)));	
		array_push($a["TranspMeans1"], fnc_resgata_transp_means(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),4,0)));	
		array_push($a["CarrierCode1"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),5,1));	
		array_push($a["VesselId"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),8,1));	
		array_push($a["TypeId"], fnc_resgata_tipo_id_navio(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),8,2)));	
		array_push($a["VesselName"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT"),8,4));	
		array_push($a["PortLoad"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+9",5),2,1));	
		array_push($a["PortDischarge"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+11",6),2,1));	
		array_push($a["Arrival"], fnc_formata_data(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DTM+178",7),1,2)));	
		array_push($a["Departure"], fnc_formata_data(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DTM+7",5),1,2)));	
		array_push($a["Operator"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "NAD+CF",6),2,0));	
		array_push($a["Carrier"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "NAD+CA",6),2,0));	
		array_push($a["Agent"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "NAD+AG",6),2,0));	
		array_push($a["EquipQualif"], fnc_resgata_qualif_equipamento(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),1,0)));	
		array_push($a["EquipIdent"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),2,0));	
        array_push($a["SizeType"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),3,1));	  	
		array_push($a["Category"], fnc_resgata_categoria(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),5,0)));	  	
		array_push($a["Status"], fnc_resgata_status(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),6,0)));	  	
		array_push($a["Booking"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RFF+BN",6),1,2));	  	
		array_push($a["BL"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RFF+BM",6),1,2));	  	
		array_push($a["OwnerCargo"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RFF+AAE",7),1,2));	  	
		array_push($a["FinalDest"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+8",5),2,1));	  	
		array_push($a["TareWeight"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+T",9),3,2));	  	
		array_push($a["TareWeightUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+T",9),3,1));	  	
		array_push($a["GrossWeight"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+G",9),3,2));	  	
		array_push($a["GrossWeightUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+AAE+G",9),3,1));	  	
		array_push($a["Seal"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "SEL"),1,0));	  	
		array_push($a["TypeSeal"], fnc_resgata_tipo_lacre(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "SEL"),2,0)));	  	
		array_push($a["IdealTemp"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TMP"),2,1));	  	
		array_push($a["IdealTempUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TMP"),2,2));
        array_push($a["MinTemp"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RNG"),2,2));	  		  	
		array_push($a["MaxTemp"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RNG"),2,3));
		array_push($a["MinMaxTempUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "RNG"),2,1));
		array_push($a["OverDmsFrontLen"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,2));
		array_push($a["OverDmsFrontWid"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,3));
		array_push($a["OverDmsFrontHei"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,4));		
		array_push($a["OverDmsFrontUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+5",5),2,1));		
		array_push($a["OverDmsBackLen"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,2));
		array_push($a["OverDmsBackWid"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,3));
		array_push($a["OverDmsBackHei"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,4));
		array_push($a["OverDmsBackUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+6",5),2,1));		
		array_push($a["OverDmsRightLen"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,2));
		array_push($a["OverDmsRightWid"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,3));
		array_push($a["OverDmsRightHei"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,4));
		array_push($a["OverDmsRightUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+7",5),2,1));		
		array_push($a["OverDmsLeftLen"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,2));
		array_push($a["OverDmsLeftWid"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,3));
		array_push($a["OverDmsLeftHei"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,4));
		array_push($a["OverDmsLeftUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+8",5),2,1));		
		array_push($a["OverDmsGenLen"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,2));
		array_push($a["OverDmsGenWid"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,3));
		array_push($a["OverDmsGenHei"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,4));
		array_push($a["OverDmsGenUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+9",5),2,1));		
		array_push($a["OverDmsExtLen"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,2));
		array_push($a["OverDmsExtWid"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,3));
		array_push($a["OverDmsExtHei"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,4));
		array_push($a["OverDmsExtUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "DIM+10",6),2,1));
		array_push($a["ModeTransp2"], fnc_resgata_modo_transp(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),3,0)));
		array_push($a["TranspMeans2"], fnc_resgata_transp_means(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),4,0)));
		array_push($a["CarrierCode2"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),8,1));
		array_push($a["Responsible"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TDT+1",5),8,4));
	  }	
	  $ini = strpos($p_arquivo,"UNH",$fim);
	  $fim = strpos($p_arquivo,"UNT",$ini);
  }  
  return $a;    
}  

function fnc_processa_baplie($p_arquivo)
{
  $segmento = fnc_obter_linha_segmento ($p_arquivo, "TDT");
  $unb = fnc_obter_linha_segmento ($p_arquivo, "UNB");
  $ini = strpos($p_arquivo,"LOC+147",0);
  $fim = strpos($p_arquivo,"NAD+CA",0)+18;
  for( $i = 0; $i <= substr_count($p_arquivo, "LOC+147")-1; $i++ ) {
	  $parte = substr($p_arquivo,$ini,$fim-$ini);
	  //echo $parte."<br>";
	  if ($i == 0) {
		$a = ['Sender'=>[fnc_edi_recupera_compnte($unb,2,0)],
		      'Recipient'=>[fnc_edi_recupera_compnte($unb,3,0)],
			  'Date'=>[fnc_edi_recupera_compnte($unb,4,1)],
			  'Time'=>[fnc_edi_recupera_compnte($unb,4,2)],
			  'IdSender'=>[fnc_edi_recupera_compnte($unb,5,0)],
		      'VesselName'=>[fnc_edi_recupera_compnte($segmento,4,4)],
		      'Voyage'=>[fnc_edi_recupera_compnte($segmento,2,0)],
		      'PortLoad'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+6",5),2,1)],
		      'PortDischarge'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+12",6),2,1)],
			  'PortDelivery'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+83",6),2,1)],
			  'Slot'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+147",7),2,1)],
			  'Container'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),2,0)],
			  'SizeType'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),3,0)],
			  'Weight'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+WT",6),3,2)],
			  'WeightUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+WT",6),3,1)],
			  'Goods'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "FTX+AAA",7),4,0)],
			  'Status'=>[fnc_resgata_status(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),6,0))],
			  'Temperature'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TMP"),2,1)],
			  'TempUnit'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TMP"),2,2)],
			  'Carrier'=>[fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "NAD+CA",6),2,1)],
		     ];
	  } else {
		 array_push($a["VesselName"], fnc_edi_recupera_compnte($segmento,4,4));	 
		 array_push($a["Voyage"], fnc_edi_recupera_compnte($segmento,2,0));	 
		 array_push($a["PortLoad"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+6",5),2,1));	 
		 array_push($a["PortDischarge"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+12",6),2,1));	 
		 array_push($a["PortDelivery"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+83",6),2,1));	 
		 array_push($a["Slot"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "LOC+147",7),2,1));	 
		 array_push($a["Container"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),2,0));	 
		 array_push($a["SizeType"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),3,0));	 
		 array_push($a["Weight"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+WT",6),3,2));	 
		 array_push($a["WeightUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "MEA+WT",6),3,1));	 
		 array_push($a["Goods"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "FTX+AAA",7),4,0));	 
		 array_push($a["Status"], fnc_resgata_status(fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "EQD"),6,0)));	 		 
		 array_push($a["Temperature"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TMP"),2,1));	 		 
		 array_push($a["TempUnit"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "TMP"),2,2));	 		 
		 array_push($a["Carrier"], fnc_edi_recupera_compnte(fnc_obter_linha_segmento ($parte, "NAD+CA",6),2,1));	 		 
	  }
	  $ini = strpos($p_arquivo,"LOC+147",$fim);
	  $fim = strpos($p_arquivo,"NAD+CA",$ini)+18;
  }  
  return $a;    
}

function prc_mostra_codeco($p_array)
{
    if ($_POST['teste'] == "X") {
	  // Escolher o formato do arquivo
      header("Content-type: application/msexcel");
      // Nome que arquivo será salvo
      header("Content-Disposition: attachment; filename=codeco.xls");
    }
	echo "<table><tr><td><strong>Sender: ".$p_array["Sender"][0]." Recipient: ".$p_array["Recipient"][0]." Date: ".fnc_formata_data($p_array["DateDoc"][0].$p_array["TimeDoc"][0])." Id Sender:".$p_array["IdSender"][0]." Standard: CODECO</strong></td></tr></table><br>";
	echo '<table class="defaultTable sar-table">';
	echo '<tr title="Click on the column to drag and drop">
	        <th>Seq</th>
			<th>Move</th>
			<th>Function</th>
			<th>Vessel Voyage - Carrier - Id - TranspMode - TranspMeans</th>
			<th>Load Port</th>
			<th>Disch Port</th>
			<th>Arrival</th>
			<th>Departure</th>
			<th>Operator</th>
			<th>Carrier</th>
			<th>Agent</th>
			<th>EquipQualif</th>
			<th>EquipIdent</th>
			<th>Size Type</th>
			<th>Category</th>
			<th>Status</th>
			<th>Booking</th>
			<th>BL</th>
			<th>OwnerCargo</th>
			<th>Final Dest</th>
			<th>Tare Weight</th>
			<th>Gross Weight</th>
			<th>Seal</th>
			<th>Ideal Temp</th>
			<th>Min Temp</th>
			<th>Max Temp</th>
			<th>OverDms Front Len,Wid,Hei</th>
			<th>OverDms Back Len,Wid,Hei</th>
			<th>OverDms Right Len,Wid,Hei</th>
			<th>OverDms Left Len,Wid,Hei</th>
			<th>OverDms Gen Len,Wid,Hei</th>
			<th>OverDms Ext Len,Wid,Hei</th>
			<th>Responsible Transp - Carrier Code - Mode Transp - Transp Means</th>
		   </tr>';
	for ($i = 0; $i <= count($p_array["EquipIdent"])-1; $i++) {
	  echo '<tr ';
	  if($i % 2 == 0) echo 'class="alt"';	  
	  echo   '><td>'.$p_array["Seq"][$i].'</td>
	           <td>'.$p_array["Move"][$i].'</td>
			   <td>'.$p_array["FunctionMsg"][$i].'</td>
			   <td>'.$p_array["VesselName"][$i]." ".$p_array["Voyage"][$i]." - ".$p_array["CarrierCode1"][$i]." - ".$p_array["TypeId"][$i]." ".$p_array["VesselId"][$i]." - ".$p_array["ModeTransp1"][$i]." - ".$p_array["TranspMeans1"][$i].'</td>
	           <td>'.$p_array["PortLoad"][$i].'</td>
			   <td>'.$p_array["PortDischarge"][$i].'</td>
			   <td>'.$p_array["Arrival"][$i].'</td>
			   <td>'.$p_array["Departure"][$i].'</td>			   
			   <td>'.$p_array["Operator"][$i].'</td>
			   <td>'.$p_array["Carrier"][$i].'</td>
			   <td>'.$p_array["Agent"][$i].'</td>			   
			   <td>'.$p_array["EquipQualif"][$i].'</td>
			   <td>'.$p_array["EquipIdent"][$i].'</td>
			   <td>'.$p_array["SizeType"][$i].'</td>
			   <td>'.$p_array["Category"][$i].'</td>
			   <td>'.$p_array["Status"][$i].'</td>
			   <td>'.$p_array["Booking"][$i].'</td>
			   <td>'.$p_array["BL"][$i].'</td>
			   <td>'.$p_array["OwnerCargo"][$i].'</td>
			   <td>'.$p_array["FinalDest"][$i].'</td>
			   <td>'.$p_array["TareWeight"][$i]." ".$p_array["TareWeightUnit"][$i].'</td>
			   <td>'.$p_array["GrossWeight"][$i]." ".$p_array["GrossWeightUnit"][$i].'</td>
			   <td>'.$p_array["Seal"][$i]." ".$p_array["TypeSeal"][$i].'</td>
			   <td>'.$p_array["IdealTemp"][$i]." ".$p_array["IdealTempUnit"][$i].'</td>
			   <td>'.$p_array["MinTemp"][$i]." ".$p_array["MinMaxTempUnit"][$i].'</td>
			   <td>'.$p_array["MaxTemp"][$i]." ".$p_array["MinMaxTempUnit"][$i].'</td>
			   <td>'.$p_array["OverDmsFrontLen"][$i].",".$p_array["OverDmsFrontWid"][$i].",".$p_array["OverDmsFrontHei"][$i]." ".$p_array["OverDmsFrontUnit"][$i].'</td>
			   <td>'.$p_array["OverDmsBackLen"][$i].",".$p_array["OverDmsBackWid"][$i].",".$p_array["OverDmsBackHei"][$i]." ".$p_array["OverDmsBackUnit"][$i].'</td>
			   <td>'.$p_array["OverDmsRightLen"][$i].",".$p_array["OverDmsRightWid"][$i].",".$p_array["OverDmsRightHei"][$i]." ".$p_array["OverDmsRightUnit"][$i].'</td>
			   <td>'.$p_array["OverDmsLeftLen"][$i].",".$p_array["OverDmsLeftWid"][$i].",".$p_array["OverDmsLeftHei"][$i]." ".$p_array["OverDmsLeftUnit"][$i].'</td>
			   <td>'.$p_array["OverDmsGenLen"][$i].",".$p_array["OverDmsGenWid"][$i].",".$p_array["OverDmsGenHei"][$i]." ".$p_array["OverDmsGenUnit"][$i].'</td>
			   <td>'.$p_array["OverDmsExtLen"][$i].",".$p_array["OverDmsExtWid"][$i].",".$p_array["OverDmsExtHei"][$i]." ".$p_array["OverDmsExtUnit"][$i].'</td>
			   <td>'.$p_array["Responsible"][$i]." - ".$p_array["CarrierCode2"][$i]." - ".$p_array["ModeTransp2"][$i]." - ".$p_array["TranspMeans2"][$i].'</td>
		   </tr>';
	}
	echo '</table>';
}

function prc_mostra_coarri($p_array)
{
    if ($_POST['teste'] == "X") {
	  // Escolher o formato do arquivo
      header("Content-type: application/msexcel");
      // Nome que arquivo será salvo
      header("Content-Disposition: attachment; filename=coarri.xls");
    }
	echo "<table><tr><td><strong>Sender: ".$p_array["Sender"][0]." Recipient: ".$p_array["Recipient"][0]." Date: ".fnc_formata_data($p_array["DateDoc"][0].$p_array["TimeDoc"][0])." Id Sender:".$p_array["IdSender"][0]." Standard: COARRI</strong></td></tr></table><br>";
	echo '<table class="defaultTable sar-table">';
	echo '<tr title="Click on the column to drag and drop">
	        <th>Seq</th>
			<th>Move</th>
			<th>Function</th>
			<th>Vessel Voyage - Carrier - Id - TranspMode - TranspMeans</th>
			<th>Load Port</th>
			<th>Disch Port</th>
			<th>Arrival</th>
			<th>Departure</th>
			<th>Operator</th>
			<th>Carrier</th>
			<th>Agent</th>
			<th>EquipQualif</th>
			<th>EquipIdent</th>
			<th>Size Type</th>
			<th>Category</th>
			<th>Status</th>
			<th>Booking</th>
			<th>BL</th>
			<th>OwnerCargo</th>
			<th>Position on Vessel</th>
			<th>Execution Date</th>
			<th>Final Dest</th>
			<th>Tare Weight</th>
			<th>Gross Weight</th>
			<th>Seal</th>
			<th>Ideal Temp</th>
			<th>Min Temp</th>
			<th>Max Temp</th>
			<th>OverDms Front Len,Wid,Hei</th>
			<th>OverDms Back Len,Wid,Hei</th>
			<th>OverDms Right Len,Wid,Hei</th>
			<th>OverDms Left Len,Wid,Hei</th>
			<th>OverDms Gen Len,Wid,Hei</th>
			<th>OverDms Ext Len,Wid,Hei</th>
			<th>Responsible Transp - Carrier Code - Mode Transp - Transp Means</th>
		   </tr>';
	for ($i = 0; $i <= count($p_array["EquipIdent"])-1; $i++) {
	  echo '<tr ';
	  if($i % 2 == 0) echo 'class="alt"';	  
	  echo   '><td>'.$p_array["Seq"][$i].'</td>
	           <td>'.$p_array["Move"][$i].'</td>
			   <td>'.$p_array["FunctionMsg"][$i].'</td>
			   <td>'.$p_array["VesselName"][$i]." ".$p_array["Voyage"][$i]." - ".$p_array["CarrierCode1"][$i]." - ".$p_array["TypeId"][$i]." ".$p_array["VesselId"][$i]." - ".$p_array["ModeTransp1"][$i]." - ".$p_array["TranspMeans1"][$i].'</td>
	           <td>'.$p_array["PortLoad"][$i].'</td>
			   <td>'.$p_array["PortDischarge"][$i].'</td>
			   <td>'.$p_array["Arrival"][$i].'</td>
			   <td>'.$p_array["Departure"][$i].'</td>			   
			   <td>'.$p_array["Operator"][$i].'</td>
			   <td>'.$p_array["Carrier"][$i].'</td>
			   <td>'.$p_array["Agent"][$i].'</td>			   
			   <td>'.$p_array["EquipQualif"][$i].'</td>
			   <td>'.$p_array["EquipIdent"][$i].'</td>
			   <td>'.$p_array["SizeType"][$i].'</td>
			   <td>'.$p_array["Category"][$i].'</td>
			   <td>'.$p_array["Status"][$i].'</td>
			   <td>'.$p_array["Booking"][$i].'</td>
			   <td>'.$p_array["BL"][$i].'</td>
			   <td>'.$p_array["OwnerCargo"][$i].'</td>
			   <td>'.$p_array["PositionOnVessel"][$i].'</td>
			   <td>'.$p_array["ExecutionDate"][$i].'</td>
			   <td>'.$p_array["FinalDest"][$i].'</td>
			   <td>'.$p_array["TareWeight"][$i]." ".$p_array["TareWeightUnit"][$i].'</td>
			   <td>'.$p_array["GrossWeight"][$i]." ".$p_array["GrossWeightUnit"][$i].'</td>
			   <td>'.$p_array["Seal"][$i]." ".$p_array["TypeSeal"][$i].'</td>
			   <td>'.$p_array["IdealTemp"][$i]." ".$p_array["IdealTempUnit"][$i].'</td>
			   <td>'.$p_array["MinTemp"][$i]." ".$p_array["MinMaxTempUnit"][$i].'</td>
			   <td>'.$p_array["MaxTemp"][$i]." ".$p_array["MinMaxTempUnit"][$i].'</td>
			   <td>'.$p_array["OverDmsFrontLen"][$i].",".$p_array["OverDmsFrontWid"][$i].",".$p_array["OverDmsFrontHei"][$i]." ".$p_array["OverDmsFrontUnit"][$i].'</td>
			   <td>'.$p_array["OverDmsBackLen"][$i].",".$p_array["OverDmsBackWid"][$i].",".$p_array["OverDmsBackHei"][$i]." ".$p_array["OverDmsBackUnit"][$i].'</td>
			   <td>'.$p_array["OverDmsRightLen"][$i].",".$p_array["OverDmsRightWid"][$i].",".$p_array["OverDmsRightHei"][$i]." ".$p_array["OverDmsRightUnit"][$i].'</td>
			   <td>'.$p_array["OverDmsLeftLen"][$i].",".$p_array["OverDmsLeftWid"][$i].",".$p_array["OverDmsLeftHei"][$i]." ".$p_array["OverDmsLeftUnit"][$i].'</td>
			   <td>'.$p_array["OverDmsGenLen"][$i].",".$p_array["OverDmsGenWid"][$i].",".$p_array["OverDmsGenHei"][$i]." ".$p_array["OverDmsGenUnit"][$i].'</td>
			   <td>'.$p_array["OverDmsExtLen"][$i].",".$p_array["OverDmsExtWid"][$i].",".$p_array["OverDmsExtHei"][$i]." ".$p_array["OverDmsExtUnit"][$i].'</td>
			   <td>'.$p_array["Responsible"][$i]." - ".$p_array["CarrierCode2"][$i]." - ".$p_array["ModeTransp2"][$i]." - ".$p_array["TranspMeans2"][$i].'</td>
		   </tr>';
	}
	echo '</table>';
}

function prc_mostra_baplie($p_array)
{
    if ($_POST['teste'] == "X") {
	  // Escolher o formato do arquivo
      header("Content-type: application/octet-stream");
      // Nome que arquivo será salvo
      header("Content-Disposition: attachment; filename=baplie.xls");
    }		    
	echo "<table><tr><td><strong>Sender: ".$p_array["Sender"][0]." Recipient: ".$p_array["Recipient"][0]." Date: ".fnc_formata_data($p_array["DateDoc"][0].$p_array["TimeDoc"][0])." Id Sender: ".$p_array["IdSender"][0]." Standard: BAPLIE</strong></td></tr></table><br>";
	echo "<table><tr><td><strong>Vessel: ".$p_array["VesselName"][0]." Voyage: ".$p_array["Voyage"][0]."</strong></td></tr></table><br>";
	echo '<table class="defaultTable sar-table">';
	echo '<tr title="Click on the column to drag and drop"><th>Container</th><th>Size Type</th><th>Slot</th><th>Weight</th><th>Temperature</th><th>Carrier</th><th>Goods</th><th>Load Port</th><th>Discharge Port</th><th>Delivery Port</th></tr>';
	for ($i = 0; $i <= count($p_array["Container"])-1; $i++) {
	  echo '<tr ';
	  if($i % 2 == 0) echo 'class="alt"';	  
	  echo'><td>'.$p_array["Container"][$i].'</td>
	        <td>'.$p_array["SizeType"][$i].'</td>
			<td>'.$p_array["Slot"][$i].'</td>
			<td>'.$p_array["Weight"][$i]." ".$p_array["WeightUnit"][$i].'</td>
			<td>'.$p_array["Temperature"][$i]." ".$p_array["TempUnit"][$i].'</td>
			<td>'.$p_array["Carrier"][$i].'</td>
			<td>'.$p_array["Goods"][$i].'</td>
			<td>'.$p_array["PortLoad"][$i].'</td>
			<td>'.$p_array["PortDischarge"][$i].'</td>
			<td>'.$p_array["PortDelivery"][$i].'</td>
			</tr>';
	}
	echo '</table>';
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if(isset($_FILES['ufile']['name'])){
       if ($_POST['teste'] == "S"){
		  echo "<table><tr><td><strong>File: ".$_FILES['ufile']['name']."</strong></td></tr></table><br>";
	      echo "<table><tr><td><strong>Date Format: DD/MM/YYYY HH24:MI</strong></td></tr></table><br>";         
	   }
	   $tmpName = $_FILES['ufile']['tmp_name'];
       $newName = "" . $_FILES['ufile']['name'];   
       if(!is_uploaded_file($tmpName) ||
                            !move_uploaded_file($tmpName, $newName)){
            echo "<table><tr><td><strong>Failed to read file ".$_FILES['ufile']['name']."</strong></td></tr></table>";
       } else {
            echo "<br>";
            
            //coloca o conteudo do arquivo para a variavel $arquivo			
			$ponteiro = fopen ($_FILES['ufile']['name'],"r");
            $arquivo = '';
			while (!feof ($ponteiro)) {
             $arquivo = $arquivo.fgets($ponteiro,4096);
			 
			}			
			fclose ($ponteiro);
			if (unlink($_FILES['ufile']['name']) != 1) echo "Erro ao deletar arquivo!";			
						
			$padrao = fnc_resgata_padrao($arquivo);
			
			if ($padrao == "COARRI"){
				$a = fnc_processa_coarri(trim($arquivo));
				prc_mostra_coarri($a);				
			}
			elseif ($padrao == "CODECO") {
				$a = fnc_processa_codeco(trim($arquivo));
				prc_mostra_codeco($a);				
			}
			elseif ($padrao == "BAPLIE") {				
				$a = fnc_processa_baplie(trim($arquivo));			    				  
				prc_mostra_baplie($a);				
			}
            else {
                echo "<table><tr><td><strong>Invalid file</strong></td></tr></table>";
			}			
			
       } 

   } else {
     echo "You need to select a file.  Please try again.";
  }

?>
</body>
</html>