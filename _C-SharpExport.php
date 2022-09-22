<?php 
 
   include_once('include_db.php');

   try{
    echo "wait....","\r\n";

    if(!is_dir(PATH_OUTBOUND_STOREFRONT))
        @mkdir(PATH_OUTBOUND_STOREFRONT);
    

    @FCNxFDeleteDir(PATH_OUTBOUND_STOREFRONT."\\");

    $tSQL =    "SELECT 
                    *
                FROM TCNTLangFront f1
                WHERE ISNULL(f1.FNLngSataus,0) = 1
                ORDER BY f1.FTLngFile ,f1.FTLngName ,f1.FTLngKey
                ";
    $oResulst   = $conn->prepare($tSQL);
    $oResulst->execute();
    $aResulst   = $oResulst->fetchAll(PDO::FETCH_ASSOC);
    $tAppName = "AdaStoreFront";

    if(!empty($aResulst)){
        foreach($aResulst as $key => $value){
      
            $tLangFileInfoOutBound  =   pathinfo(PATH_OUTBOUND_STOREFRONT.$value['FTLngFile']);
            $tLangFileInfoInBound   =   pathinfo(PATH_INBOUND_STOREFRONT.$value['FTLngFile']);

            $tNewFile               =   $tLangFileInfoOutBound['filename']."_".$value['FTLngName'].".".$tLangFileInfoOutBound['extension'];
            $tNewFullFile           =   $tLangFileInfoOutBound['dirname']."/".$tNewFile;
            // $tLangFileInfoOutBound['dirname'];

            if (!file_exists($tLangFileInfoOutBound['dirname'])) {
                mkdir($tLangFileInfoOutBound['dirname'],0777,true);
            }

            if (!file_exists($tNewFullFile)) {
                copy($tLangFileInfoInBound['dirname']."/".$tNewFile, $tNewFullFile);
            }

            $xml = simplexml_load_file($tNewFullFile);
        
            $list = $xml->data;
            for ($i = 0; $i < count($list); $i++) {
                if($list[$i]->attributes()->name == $value['FTLngKey']){
                    $list[$i]->value = htmlspecialchars($value['FTLngValue']);
                }
            }
            $xml->asXML($tNewFullFile);

            echo "wait....","\r\n";
        }

        $tSqlUpdate = "UPDATE TCNMLog SET FDLogOutbound =? WHERE FTLogApp =?";
        $oSqlUpdate = $conn->prepare($tSqlUpdate);
        $oSqlUpdate->execute(array(date('Y-m-d H:i:s'),$tAppName));
    }
    
    echo 'scuess';
   }catch(Exception $e){
       die(print_r($e->getMessage()));
   }
   sleep(1);
   exit;
?>