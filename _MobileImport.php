<?php 
 
   include_once('include_db.php');

   try{

    echo "wait....","\r\n";
   
    $tSql = "TRUNCATE TABLE TCNTLangMobile"; 
    $getRes = $conn->prepare($tSql);
    $getRes->execute();

    function listFolderFiles($dir, &$arr){
        $allowed = array('xml');
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
    listFolderFiles(PATH_INBOUND_STOREMOBILE, $aResultFiles);
    $tAppName = "AdaMobile";
    if(!empty($aResultFiles)){

        $tSqlDel = "DELETE FROM [dbo].[TCNMLog] WHERE FTLogApp = ?";
        $oSqlDel = $conn->prepare($tSqlDel);
        $oSqlDel->execute(array($tAppName));

        $dFilemtime =  date ("Y-m-d H:i:s", filemtime(PATH_INBOUND_STOREMOBILE));
        $tSql = "INSERT INTO [dbo].[TCNMLog] ([FTLogApp] ,[FDLogInbound] ,[FDLogCreated]) VALUES  (? ,?,?)";
        $getRes = $conn->prepare($tSql);
        $getRes->execute(array($tAppName,$dFilemtime,date('Y-m-d H:i:s')));

        foreach ($aResultFiles as $key => $value) {
            $tFilesName = str_replace(PATH_INBOUND_STOREMOBILE,'',$value);
          
            $tLangName  = substr($tFilesName, strrpos($tFilesName, '-' )+1);
            $lang       = explode('/',$tLangName );
        
            $xml = simplexml_load_file($value);
            $list = $xml->string;
            for ($i = 0; $i < count($list); $i++) {
                $tSql = "INSERT INTO TCNTLangMobile (FTLngFile ,FTLngName  ,FTLngKey ,FTLngValue) VALUES (? ,? ,? ,?)";
                $getRes = $conn->prepare($tSql);
                $getRes->execute(array(str_replace("/values-".$lang[0],"",$tFilesName),$lang[0],$list[$i]->attributes()->name,$list[$i]));
            }
            echo "wait....","\r\n";
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