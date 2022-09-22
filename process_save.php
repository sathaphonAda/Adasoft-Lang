<?php
   include_once('include_db.php');

try {
    $tActionType = (string)$_POST['tActionType'];

    switch ($tActionType) {
      case "AdaStoreBack":
        $aResponse = array();

        $conn->beginTransaction();
        $aDataObj = isset($_POST['aDataObj'])?$_POST['aDataObj']:'';
        if(!empty($aDataObj)){
            foreach($aDataObj as $key => $value){
                $tSql = "UPDATE TCNTLangBack SET FTLngValue = ? , FNLngSataus = ? WHERE FNLngId = ?";
                $conn->prepare($tSql)->execute([$value ,1 ,$key]);
            }
        }
        
        $conn->commit();
        $aResponse['nCode']         = "100";
        $aResponse['tDescription']  = "succeed";
        echo json_encode($aResponse);
        exit;
      break;
      case "AdaStoreFront":
        $aResponse = array();

        $conn->beginTransaction();
        $aDataObj = isset($_POST['aDataObj'])?$_POST['aDataObj']:'';
        if(!empty($aDataObj)){
            foreach($aDataObj as $key => $value){
                $tSql = "UPDATE TCNTLangFront SET FTLngValue = ? , FNLngSataus = ? WHERE FNLngId = ?";
                $conn->prepare($tSql)->execute([$value ,1 ,$key]);
            }
        }
        
        $conn->commit();
        $aResponse['nCode']         = "100";
        $aResponse['tDescription']  = "succeed";
        echo json_encode($aResponse);
        exit;
        break;
      case "AdaMobile":
        $aResponse = array();

        $conn->beginTransaction();
        $aDataObj = isset($_POST['aDataObj'])?$_POST['aDataObj']:'';
        if(!empty($aDataObj)){
            foreach($aDataObj as $key => $value){
                $tSql = "UPDATE TCNTLangMobile SET FTLngValue = ? , FNLngSataus = ? WHERE FNLngId = ?";
                $conn->prepare($tSql)->execute([$value ,1 ,$key]);
            }
        }
        
        $conn->commit();
        $aResponse['nCode']         = "100";
        $aResponse['tDescription']  = "succeed";
        echo json_encode($aResponse);
        exit;
        break;
      default:
            $aResponse = array();
            $aResponse['nCode']         = "900";
            $aResponse['rDescription']  = "error";
            echo json_encode($aResponse);
            exit;
    }

}catch(Exception $e) {
    $conn->rollback();
    echo 'Message: ' .$e->getMessage();
    exit;

}
?>