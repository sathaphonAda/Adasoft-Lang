<?php
   include_once('include_db.php');

try {
    $tApp = (string)$_POST['tApp'];
    $tActionType = (string)$_POST['tActionType'];
    $nStatus = 400;
    switch ($tActionType) {
      case "CheckReview":
        $aResponse = array();

        $aPath  = array(
                        'AdaStoreBack'  => PATH_INBOUND_STOREBACK ,
                        'AdaStoreFront' => PATH_INBOUND_STOREFRONT ,
                        'AdaMobile'   => PATH_INBOUND_STOREMOBILE
                    );
        $aDotBat  = array( 
                        'AdaStoreBack'   => '_PHPInbound.bat',
                        'AdaStoreFront' => '_C-SharpInbound.bat' ,
                        'AdaMobile'   => '_MobileInbound.bat'
                    );

        if(!empty($aPath[$tApp])){
            if(file_exists($aPath[$tApp])) {
                $dFilemtime =  date ("Y-m-d H:i:s", filemtime($aPath[$tApp]));
                $tSqlChkLog = "SELECT FTLogApp,FDLogInbound FROM TCNMLog WHERE 1=1 AND FTLogApp = ?";//and FDLogInbound= ?
                $oChkLog = $conn->prepare($tSqlChkLog,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $oChkLog->execute(array($tApp));
                $nChkLog = $oChkLog->rowCount();
                if($nChkLog == 0){
                    exec($aDotBat[$tApp]);
                }else{
                    $aResLog    = $oChkLog->fetch(PDO::FETCH_ASSOC);
                    if(date("Y-m-d H:i:s",strtotime($aResLog['FDLogInbound'])) !== $dFilemtime){
                        $nStatus = 900;
                        throw new Exception("not match Inbound and Temp ".$tApp);
                    }
                }
            }
        }     
    
        $aResponse['nCode']         = "100";
        $aResponse['tDescription']  = "succeed";
        echo json_encode($aResponse);
        exit;
      break;
      case "LoadInboundReviewNew":
        $aResponse = array();
        if($tApp == "AdaStoreBack"){
            exec("_PHPInbound.bat");
        }else if($tApp == "AdaStoreFront"){
            exec("_C-SharpInbound.bat");
        }else if($tApp == "AdaMobile"){
            exec("_MobileInbound.bat");
        }
        $aResponse['nCode']         = "100";
        $aResponse['tDescription']  = "succeed";
        echo json_encode($aResponse);
        exit;
      break;
      case "LoadOuttboundReview":
        $aResponse = array();
        if($tApp == "AdaStoreBack"){
            exec("_PHPOutbound.bat");
        }else if($tApp == "AdaStoreFront"){
            exec("_C-SharpOutbound.bat");
        }else if($tApp == "AdaMobile"){
            exec("_MobileOutbound.bat");
        }
        $aResponse['nCode']         = "100";
        $aResponse['tDescription']  = "succeed";
        echo json_encode($aResponse);
        exit;
      break;
    }
}catch(Exception $e) {
    $aResponse = array();
    $aResponse['nCode']         =  $nStatus;
    $aResponse['rDescription']  = $e->getMessage();
    echo json_encode($aResponse);
    exit;
}
?>