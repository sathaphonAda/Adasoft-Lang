<?php 

   include_once('include_db.php');

   try{
    echo "wait....","\r\n";

    if(!is_dir(PATH_OUTBOUND_STOREBACK))
        @mkdir(PATH_OUTBOUND_STOREBACK);
        

    @FCNxFDeleteDir(PATH_OUTBOUND_STOREBACK."\\");

    $tSQL =    "SELECT 
                    lnd.FTLngFile,
                    lnd.FTLngName,
                    lnd.FTLngKey,
                    lnd.FTLngValue
                FROM TCNTLangBack lnd
                WHERE lnd.FTLngFile in (
                    SELECT 
                        f1.FTLngFile
                    FROM TCNTLangBack f1
                    WHERE ISNULL(f1.FNLngSataus,0) = 1
                    GROUP BY f1.FTLngFile
                )
                ORDER BY 
                    lnd.FTLngFile,
                    lnd.FTLngName";
    $oResulst   = $conn->prepare($tSQL);
    $oResulst->execute();
    $aResulst   = $oResulst->fetchAll(PDO::FETCH_ASSOC);
    
    $tLoopLangFile = "";
    $tLoopLangName = "";
    $tResponsedata = "";

    $tAppName = "AdaStoreBack";
    if(!empty($aResulst)){
        foreach($aResulst as $key => $value){

            $tLoopLangName = $value['FTLngName'];
            $tLoopLangFile = $value['FTLngFile'];

            $tLangFile =  substr($tLoopLangFile, strrpos($tLoopLangFile, '/' )+1);
            $tFullLangFile =  PATH_OUTBOUND_STOREBACK.'/'.$tLoopLangName.$tLoopLangFile;
            
            $tResponsedata = '$lang[\''.$value['FTLngKey'].'\'] = "'.htmlspecialchars($value['FTLngValue']).'";'.PHP_EOL;
       
            $tHeard = "";
            if (!file_exists(str_replace($tLangFile,'',$tFullLangFile))) {
                mkdir(str_replace($tLangFile,'',$tFullLangFile),0777,true);
                $tHeard =  "<?php ".PHP_EOL;
            }

            $xMyfile = fopen(strtolower($tFullLangFile), "a+") or die("Unable to open file!");
            fwrite($xMyfile, $tHeard.$tResponsedata);
            fclose($xMyfile);

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