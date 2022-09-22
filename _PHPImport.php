<?php 
  // defined('BASEPATH') OR exit('No direct script access allowed');
   include_once('include_db.php');
   try{

    echo "wait....","\r\n";
   
    $tSql = "TRUNCATE TABLE TCNTLangBack";
    $getRes = $conn->prepare($tSql);
    $getRes->execute();

        function listFolderFiles($dir, &$arr){
            $allowed = array('php');
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

       $aLang = array();
       $fileFolderList = scandir(PATH_INBOUND_STOREBACK);
       foreach ($fileFolderList as $fileFolder) {
           if (is_dir(PATH_INBOUND_STOREBACK.'\\'. $fileFolder) && $fileFolder != '.' && $fileFolder != '..') {
               array_push($aLang, $fileFolder);
           }
       }
       $aResultFiles = array();
       $aHeard = array();
       listFolderFiles(PATH_INBOUND_STOREBACK.'\th', $aResultFiles);
       $tAppName = "AdaStoreBack";
       $aResultLang = array();
       if(count($aResultFiles) > 0){

            $tSqlDel = "DELETE FROM [dbo].[TCNMLog] WHERE FTLogApp = ?";
            $oSqlDel = $conn->prepare($tSqlDel);
            $oSqlDel->execute(array($tAppName));

            $dFilemtime =  date ("Y-m-d H:i:s", filemtime(PATH_INBOUND_STOREBACK));
            $tSql = "INSERT INTO [dbo].[TCNMLog] ([FTLogApp] ,[FDLogInbound] ,[FDLogCreated]) VALUES  (? ,?,?)";
            $getRes = $conn->prepare($tSql);
            $getRes->execute(array($tAppName,$dFilemtime,date('Y-m-d H:i:s')));

           foreach($aResultFiles as $key_f => $value) {
               (int)$nRound    = 0 ;
               if(count($aLang) > 0 ){
                   foreach($aLang as $nKey => $tLangVal){

                        if($nRound==0){
                           $tPathLang   = str_replace('th','th',strtolower($value));
                           if(file_exists($tPathLang)){ 
                                $newfile     = str_replace(strtolower(PATH_INBOUND_STOREBACK.'\th'),'',$tPathLang);
                                unset($lang);
                                include($tPathLang);
                                foreach($lang as $key => $val){
                                   $aResultLang[$key_f][$key]['th'] = $val;
                                   $tSql = "INSERT INTO TCNTLangBack (FTLngFile ,FTLngName  ,FTLngKey ,FTLngValue) VALUES (? ,? ,? ,? )";
                                   $getRes = $conn->prepare($tSql);
                                   $getRes->execute(array($newfile,'th',$key,$val));
                                }
                           }
                        }

                        if($tLangVal != 'th'){
                            if(count($aResultLang) > 0){
                                unset($lang);
                                $tPathLang = str_replace('th',$tLangVal,strtolower($value));
                                @include($tPathLang);
                                foreach($aResultLang[$key_f] as $nDfakey => $tDfaVal){
                                    $tSql = "INSERT INTO TCNTLangBack (FTLngFile ,FTLngName  ,FTLngKey ,FTLngValue) VALUES (? ,? ,? ,? )";
                                    $getRes = $conn->prepare($tSql);
                                    $getRes->execute(array($newfile,$tLangVal,$nDfakey,isset($lang[$nDfakey])?$lang[$nDfakey]:''));
                                }
                            }
                        }

                       $nRound++;
                   }
                   echo "wait....","\r\n";
               }
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