<?php

    include_once('include_db.php');

    try{
        echo "wait....","\r\n";
    
        $tSql = "TRUNCATE TABLE TCNTLangFront";
        $getRes = $conn->prepare($tSql);
        $getRes->execute();

        function listFolderFiles($dir, &$arr){
            $allowed = array('resx');
            $fileFolderList = scandir($dir);
            foreach ($fileFolderList as $fileFolder) {
                if ($fileFolder != '.' && $fileFolder != '..') {
                    if (!is_dir($dir . '/' . $fileFolder)) {
                        $ext = pathinfo($fileFolder, PATHINFO_EXTENSION);
                        if (in_array($ext, $allowed)) {
                            $id = ltrim($dir . '/' . $fileFolder, './');
                            array_push($arr, $id);
                        }
                    }
                    if (is_dir($dir . '/' . $fileFolder)) {
                        listFolderFiles($dir . '/' . $fileFolder, $arr);
                    }
                }
            }
            return $arr;
        }

        $aResultFiles = array();
        $aHeard = array();
        listFolderFiles(PATH_INBOUND_STOREFRONT, $aResultFiles);
        $tAppName = "AdaStoreFront";

        $tSqlLang   = "SELECT CONCAT('_', UPPER(FTMLangName)) as FTMLangName FROM TCNMLang";
        $oSqlLang   = $conn->prepare($tSqlLang);
        $oSqlLang->execute();
        $aResLang   = $oSqlLang->fetchAll(PDO::FETCH_ASSOC);
        $aObjReplace = array();
        array_push($aObjReplace,PATH_INBOUND_STOREFRONT);
        if(!empty($aResLang)){
            foreach($aResLang as $tVal){
                array_push($aObjReplace,$tVal['FTMLangName']);
            }
        }

        if(!empty($aResultFiles)){

            $tSqlDel = "DELETE FROM [dbo].[TCNMLog] WHERE FTLogApp = ?";
            $oSqlDel = $conn->prepare($tSqlDel);
            $oSqlDel->execute(array($tAppName));

            $dFilemtime =  date ("Y-m-d H:i:s", filemtime(PATH_INBOUND_STOREFRONT));
            $tSql = "INSERT INTO [dbo].[TCNMLog] ([FTLogApp] ,[FDLogInbound] ,[FDLogCreated]) VALUES  (? ,?,?)";
            $getRes = $conn->prepare($tSql);
            $getRes->execute(array($tAppName,$dFilemtime,date('Y-m-d H:i:s')));

            foreach($aResultFiles as $k => $v){
                
                $oXML = simplexml_load_string(file_get_contents($v) ,'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS) or die("Error: Cannot create object");
                $aResXML = json_decode(json_encode($oXML),TRUE);
                $tShortLangFile =  str_replace($aObjReplace,"",$v);

                $tLangFile =  pathinfo(substr($v, strrpos($v, '/' )+1));
                $tLangName = substr($tLangFile['filename'], strrpos($tLangFile['filename'], '_' )+1);

                if(!empty($aResXML['data'])){
                    foreach($aResXML['data'] as $key => $val){
                        if(!isset($val['comment'])){
                            $tSqlInsert = "INSERT INTO TCNTLangFront (FTLngFile ,FTLngName  ,FTLngKey ,FTLngValue) VALUES (? ,? ,? ,?)";
                            $oSqlInsert = $conn->prepare($tSqlInsert);
                            $FTLngValue = "";
                            //นำเข้า Temp ฌฉพาะข้อมูลที่ถูก Format เท่านั้น 
                            // Ex.
                            //  <data name="tTel" xml:space="preserve">
                                // <value>Tel.</value>
                            // </data> 
                            if(isset($val['value'])){
                                if(is_array($val['value']))
                                    continue;
                                else
                                    $FTLngValue = $val['value'];
                            }
                            $oSqlInsert->execute(array($tShortLangFile,$tLangName,isset($val['@attributes']['name'])?$val['@attributes']['name']:'',$FTLngValue));
                        }else{
                            continue;
                        }
                    }
                }
                echo "wait....".$v,"\r\n";
            }
            FCNxFCheckLang($tAppName);
        }

        echo 'scuess';
    }catch(Exception $e){
        die(print_r($e->getMessage()));
    }
    sleep(1);
    exit;

?>