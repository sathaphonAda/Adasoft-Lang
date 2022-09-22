<?php 
 
   include_once('include_db.php');

   try{
    echo "wait....","\r\n";

    if(!is_dir(PATH_OUTBOUND_STOREMOBILE))
        @mkdir(PATH_OUTBOUND_STOREMOBILE);


    @FCNxFDeleteDir(PATH_OUTBOUND_STOREMOBILE."\\");

    $tSQL =    "SELECT 
                  *
                FROM TCNTLangMobile f1
                WHERE ISNULL(f1.FNLngSataus,0) = 1
              ";
    $oResulst   = $conn->prepare($tSQL);
    $oResulst->execute();
    $aResulst   = $oResulst->fetchAll(PDO::FETCH_ASSOC);
    $tAppName = "AdaMobile";

    if(!empty($aResulst)){
        foreach($aResulst as $key => $value){
          
            $tFullLangFile =  PATH_OUTBOUND_STOREMOBILE.'/values-'.$value['FTLngName'];
            if (!file_exists($tFullLangFile)) {
                mkdir($tFullLangFile,0777,true);
                
            }
            if (!file_exists($tFullLangFile.$value['FTLngFile'])) {
                copy(PATH_INBOUND_STOREMOBILE."/values-".$value['FTLngName']."/".$value['FTLngFile'], $tFullLangFile.$value['FTLngFile']);
            }

            $xml = simplexml_load_file($tFullLangFile.$value['FTLngFile']);
            $list = $xml->string;
            for ($i = 0; $i < count($list); $i++) {
                if($list[$i]->attributes()->name == $value['FTLngKey']){
                    $list[$i] = htmlspecialchars($value['FTLngValue']);
                }
            }
            $xml->asXML($tFullLangFile.$value['FTLngFile']);
           
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