<?php 
    ini_set('memory_limit', '256M');
    include_once('include_db.php');

    try {

        $tActionType = (string)$_GET['tActionType'];
        $tTypeReview = (isset($_GET['tTypeReview']) && $_GET['tTypeReview'] != '')?$_GET['tTypeReview']:'N';
        $aLangGet = (isset($_GET['aLang']) && $_GET['aLang'] != '')?$_GET['aLang']:"";
        $aLang  =   ($aLangGet != "")?explode(',',$aLangGet):array();
        $nStatus = 400;
        switch ($tActionType) {
          case "AdaStoreBack":

            $tSearch    = (string)trim($_GET['tSearch']);
            $nLimit      = (int)$_GET['nLimit'];
            $nPage       = (int)$_GET['nOffset'];
            $nOffset     = ($nPage-1)*$nLimit;

            $tSqlLang   = "SELECT FTMLangName FROM TCNMLang WHERE FTMLangName in (select LOWER(bak.FTLngName) from TCNTLangBack bak group by bak.FTLngName) ORDER BY FNMLangSeq ASC";
            $oGetLang   = $conn->prepare($tSqlLang);
            $oGetLang->execute();
            $aResultLang    = $oGetLang->fetchAll(PDO::FETCH_ASSOC);

            $aLanguage  = array();
            $aFieldJoin = array();
            $aTableJoin = array();
            $aConditionJoin = array();
            $tCondition = "";
            $aArrCondition = array();
            $tHtml = "";
            $aResponse  = array();

            if(!empty($aResultLang)){
                foreach($aResultLang as $key => $val){
                    $aLanguage[$val['FTMLangName']]  = $val['FTMLangName'];
                    if($val['FTMLangName'] == 'th')
                        continue;

                    $aConditionJoin[] = str_replace(["{{Lang}}"],[$val['FTMLangName']],",{{Lang}}.FTLngValue");
                    $aFieldJoin[] = str_replace(["{{Lang}}"],[$val['FTMLangName']],",{{Lang}}.FNLngId as {{Lang}}Id ,{{Lang}}.FTLngValue as {{Lang}}");
                    $aTableJoin[] = str_replace(["{{Lang}}"],[$val['FTMLangName']],"LEFT JOIN TCNTLangBack {{Lang}} on th.FTLngKey COLLATE SQL_Latin1_General_CP1_CS_AS = {{Lang}}.FTLngKey COLLATE SQL_Latin1_General_CP1_CS_AS and th.FTLngFile = {{Lang}}.FTLngFile and {{Lang}}.FTLngName = '{{Lang}}'");
                }
            }

            if($tSearch != ""){
                $tCondition .= " AND CONCAT(th.FTLngKey,th.FTLngValue".(!empty($aConditionJoin)?implode(" ",$aConditionJoin):"").") like '%".$tSearch."%'";
            }
        
            $tField     = " th.FTLngKey,
                            th.FTLngFile,
                            th.FNLngId as thId,
                            th.FTLngValue as th";
            $tField     .= (!empty($aFieldJoin)?implode(" ",$aFieldJoin):" ");
            $tTable     = " TCNTLangBack th ";
            $tTable     .= (!empty($aTableJoin)?implode(" ",$aTableJoin):" ");
            $tWhere     = " AND th.FTLngName = 'th' ".$tCondition;
            $tOrderBy   = " ORDER BY th.FNLngId";
            $tGroupBy   = "";
            $tOffest    = " OFFSET ".$nOffset." ROWS FETCH NEXT ".$nLimit." ROWS ONLY";
            $tSql       = "SELECT ".$tField." FROM ".$tTable." WHERE 1=1 ".$tWhere.$tOrderBy.$tGroupBy.$tOffest;

            $getRes     = $conn->prepare($tSql);
            $getRes->execute($aArrCondition);
            $aResult    = $getRes->fetchAll(PDO::FETCH_ASSOC);

            $tSqlRows = "SELECT th.FTLngKey FROM ".$tTable." WHERE 1=1 ".$tWhere.$tOrderBy.$tGroupBy;
            $getRows = $conn->prepare($tSqlRows,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $getRows->execute();
            $nRows = $getRows->rowCount();

            $tSqlChange = "SELECT FNLngSataus FROM TCNTLangBack WHERE 1=1 and FNLngSataus = 1";
            $oSqlChange  = $conn->prepare($tSqlChange,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $oSqlChange->execute();
            $nCountChange = $oSqlChange->rowCount();

            if(!empty($aResult)){
                foreach($aResult  as $key => $val){
                    $tHtml .= '<tr>';
                        $tHtml .= '<td>'.$val['FTLngKey'].'<br><span style="color:#333333a6;">'.$val['FTLngFile'].'</span></td>';
                        foreach($aLanguage as $k => $v){
                            $tDisplay = ((!in_array(strtolower($v),$aLang) && !empty($aLang))?'style="display:none"':'');
                            $tHtml .= '<td class="xWtr_'.strtolower($v).'" '.$tDisplay.'><input type="text" class="form-control form-control-sm keyLangText" attr-id="'.$val[$v.'Id'].'" attr-old="'.$val[$v].'" name="oetLangText['.$val[$v.'Id'].']" value="'.htmlspecialchars($val[$v]).'"></td>';
                        }
                    $tHtml .= '</tr>';
                }
                $aPaging	= array('nCurrent' => $nPage, 'nNext' => ($nPage+1), 'nTotalPage' => round(($nRows/$nLimit),0,PHP_ROUND_HALF_EVEN));
    
                $aResponse['nCode']         = "100";
                $aResponse['tDescription']  = "succeed";
                $aResponse['tData']         = $tHtml;
                $aResponse['aPagination']   = $aPaging;
                $aResponse['aLang']         = $aResultLang;
                $aResponse['nCountChange']  = $nCountChange;
            }else{
                $aResponse['nCode']         = "900";
                $aResponse['rDescription']  = "error";
            }
           
            echo json_encode($aResponse);
            exit;

          break;
          case "AdaStoreFront":
            
            $tSearch    = (string)trim($_GET['tSearch']);
            $nLimit      = (int)$_GET['nLimit'];
            $nPage       = (int)$_GET['nOffset'];
            $nOffset     = ($nPage-1)*$nLimit;

            $tSqlLang   = "SELECT UPPER(FTMLangName) as FTMLangName FROM TCNMLang WHERE FTMLangName in (select LOWER(bak.FTLngName) from TCNTLangFront bak group by bak.FTLngName) ORDER BY FNMLangSeq ASC";
            $oGetLang   = $conn->prepare($tSqlLang);
            $oGetLang->execute();
            $aResultLang    = $oGetLang->fetchAll(PDO::FETCH_ASSOC);

            $aLanguage  = array();
            $aFieldJoin = array();
            $aTableJoin = array();
            $aConditionJoin = array();
            $tCondition = "";
            $aArrCondition = array();
            $tHtml = "";
            $aResponse  = array();
         
            if(!empty($aResultLang)){
                foreach($aResultLang as $key => $val){
                    $aLanguage[$val['FTMLangName']]  = $val['FTMLangName'];
                    // if($val['FTMLangName'] == 'TH')
                    //     continue;

                    // $aConditionJoin[] = str_replace(["{{Lang}}"],[$val['FTMLangName']],",{{Lang}}.FTLngValue");
                    // $aFieldJoin[] = str_replace(["{{Lang}}"],[$val['FTMLangName']],",{{Lang}}.FNLngId as {{Lang}}Id ,{{Lang}}.FTLngValue as {{Lang}}");
                    // $aTableJoin[] = str_replace(["{{Lang}}"],[$val['FTMLangName']],"LEFT JOIN TCNTLangFront {{Lang}} on TH.FTLngKey COLLATE SQL_Latin1_General_CP1_CS_AS = {{Lang}}.FTLngKey COLLATE SQL_Latin1_General_CP1_CS_AS and TH.FTLngFile = {{Lang}}.FTLngFile and {{Lang}}.FTLngName = '{{Lang}}'");
                }
            }

            if($tSearch != ""){
                $tCondition .= " AND CONCAT(th.FTLngKey,th.FTLngValue) like '%".$tSearch."%'";
            }
        
            // $tField     = " TH.FTLngKey,
            //                 TH.FTLngFile,
            //                 TH.FNLngId as THId,
            //                 TH.FTLngValue as TH";
            // $tField     .= (!empty($aFieldJoin)?implode(" ",$aFieldJoin):" ");
            // $tTable     = " TCNTLangFront TH ";
            // $tTable     .= (!empty($aTableJoin)?implode(" ",$aTableJoin):" ");
            // $tWhere     = " AND TH.FTLngName = 'TH' ".$tCondition;
            // $tOrderBy   = " ORDER BY TH.FNLngId";
            // $tGroupBy   = "";
            // $tOffest    = " OFFSET ".$nOffset." ROWS FETCH NEXT ".$nLimit." ROWS ONLY";
            // $tSql       = "SELECT ".$tField." FROM ".$tTable." WHERE 1=1 ".$tWhere.$tOrderBy.$tGroupBy.$tOffest;

            $tField     = " th.FTLngFile,
                            th.FTLngKey";
            $tTable     = " TCNTLangFront th ";
            $tWhere     = " ".$tCondition;
            $tOrderBy   = " ORDER BY th.FTLngFile";
            $tGroupBy   = " GROUP BY th.FTLngFile,th.FTLngKey ";
            $tOffest    = " OFFSET ".$nOffset." ROWS FETCH NEXT ".$nLimit." ROWS ONLY";
            $tSql       = "SELECT ".$tField." FROM ".$tTable." WHERE 1=1 ".$tWhere.$tGroupBy.$tOrderBy.$tOffest;

            $getRes     = $conn->prepare($tSql);
            $getRes->execute($aArrCondition);
            $aResult    = $getRes->fetchAll(PDO::FETCH_ASSOC);
        
            $tSqlRows = "SELECT th.FTLngKey FROM ".$tTable." WHERE 1=1 ".$tWhere.$tGroupBy.$tOrderBy;
            $getRows = $conn->prepare($tSqlRows,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $getRows->execute();
            $nRows = $getRows->rowCount();

            $tSqlChange = "SELECT th.FNLngSataus FROM ".$tTable." WHERE 1=1 and th.FNLngSataus = 1";
            $oSqlChange  = $conn->prepare($tSqlChange,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $oSqlChange->execute();
            $nCountChange = $oSqlChange->rowCount();
            
            if(!empty($aResult)){
                foreach($aResult  as $key => $val){
                    $tHtml .= '<tr>';
                    $tHtml .= '<td>'.$val['FTLngKey'].'<br><span style="color:#333333a6;">'.str_replace([PATH_INBOUND_STOREFRONT],[""],$val['FTLngFile']).'</span></td>';
                        foreach($aLanguage as $k => $v){
                            $tSqlDatq= "SELECT FNLngId ,FTLngKey ,FTLngValue FROM TCNTLangFront WHERE 1=1 AND FTLngKey = ? and FTLngFile= ? AND FTLngName =?";
                            $tSqlDatq = $conn->prepare($tSqlDatq);
                            $tSqlDatq->execute(array($val['FTLngKey'],$val['FTLngFile'],$v));
                            $aRes    = $tSqlDatq->fetch(PDO::FETCH_ASSOC);

                            $tDisplay = ((!in_array(strtolower($v),$aLang) && !empty($aLang))?'style="display:none"':'');
                            $tDisabled  = !isset($aRes['FNLngId'])?"disabled":"";
                            $nId        = isset($aRes['FNLngId'])?$aRes['FNLngId']:"";
                            $tValue     = isset($aRes['FTLngValue'])?$aRes['FTLngValue']:"";

                            $tHtml .= '<td class="xWtr_'.strtolower($v).'" '. $tDisplay.'><input type="text" class="form-control form-control-sm keyLangText" '.$tDisabled.' attr-id="'.$nId.'" attr-old="'.$tValue.'" name="oetLangText['.$nId.']" value="'.htmlspecialchars($tValue).'"></td>';
                        }
                    $tHtml .= '</tr>';
                }
                $aPaging	= array('nCurrent' => $nPage, 'nNext' => ($nPage+1), 'nTotalPage' => round(($nRows/$nLimit),0,PHP_ROUND_HALF_EVEN));
                $aResponse['nCode']         = "100";
                $aResponse['tDescription']  = "succeed";
                $aResponse['tData']         = $tHtml;
                $aResponse['aPagination']   = $aPaging;
                $aResponse['aLang']         = $aResultLang;
                $aResponse['nCountChange']  = $nCountChange;
            }else{
                $aResponse['nCode']         = "900";
                $aResponse['rDescription']  = "error";
            }

            echo json_encode($aResponse);
            exit;
            break;
          case "AdaMobile":

            $tSearch    = (string)trim($_GET['tSearch']);
            $nLimit      = (int)$_GET['nLimit'];
            $nPage       = (int)$_GET['nOffset'];
            $nOffset     = ($nPage-1)*$nLimit;

            $tSqlLang   = "SELECT FTMLangName FROM TCNMLang WHERE FTMLangName in (select LOWER(bak.FTLngName) from TCNTLangMobile bak group by bak.FTLngName) ORDER BY FNMLangSeq ASC";
            $oGetLang   = $conn->prepare($tSqlLang);
            $oGetLang->execute();
            $aResultLang    = $oGetLang->fetchAll(PDO::FETCH_ASSOC);

            $aLanguage  = array();
            $aFieldCaseJoin = array();
            $aFieldJoin = array();
            $aTableJoin = array();
            $aConditionJoin = array();
            $tCondition = "";
            $aArrCondition = array();
            $tHtml = "";
            $aResponse  = array();
         
            if(!empty($aResultLang)){
                foreach($aResultLang as $key => $val){
                    $aLanguage[$val['FTMLangName']]  = $val['FTMLangName'];
                }
            }
            
            if($tSearch != ""){
                $tCondition .= " AND CONCAT(th.FTLngKey,th.FTLngValue) like '%".$tSearch."%'";
            }

            $tField     = " th.FTLngFile,
                            th.FTLngKey";
            $tTable     = " TCNTLangMobile th ";
            $tWhere     = " ".$tCondition;
            $tOrderBy   = " ORDER BY th.FTLngFile";
            $tGroupBy   = " GROUP BY th.FTLngFile,th.FTLngKey ";
            $tOffest    = " OFFSET ".$nOffset." ROWS FETCH NEXT ".$nLimit." ROWS ONLY";
            $tSql       = "SELECT ".$tField." FROM ".$tTable." WHERE 1=1 ".$tWhere.$tGroupBy.$tOrderBy.$tOffest;

            $getRes     = $conn->prepare($tSql);
            $getRes->execute($aArrCondition);
            $aResult    = $getRes->fetchAll(PDO::FETCH_ASSOC);

            $tSqlRows = "SELECT th.FTLngKey FROM ".$tTable." WHERE 1=1 ".$tWhere.$tGroupBy.$tOrderBy;
            $getRows = $conn->prepare($tSqlRows,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $getRows->execute();
            $nRows = $getRows->rowCount();

            $tSqlChange = "SELECT th.FNLngSataus FROM ".$tTable." WHERE 1=1 and th.FNLngSataus = 1";
            $oSqlChange  = $conn->prepare($tSqlChange,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $oSqlChange->execute();
            $nCountChange = $oSqlChange->rowCount();

            if(!empty($aResult)){
                foreach($aResult  as $key => $val){
                    $tHtml .= '<tr>';
                    $tHtml .= '<td>'.$val['FTLngKey'].'<br><span style="color:#333333a6;">'.str_replace([PATH_OUTBOUND_STOREMOBILE,'/values-th'],["",""],$val['FTLngFile']).'</span></td>';
                        foreach($aLanguage as $k => $v){
                            $tSqlDatq= "SELECT FNLngId ,FTLngKey ,FTLngValue FROM TCNTLangMobile WHERE 1=1 AND FTLngKey = ? and FTLngFile= ? AND FTLngName =?";
                            $tSqlDatq = $conn->prepare($tSqlDatq);
                            $tSqlDatq->execute(array($val['FTLngKey'],$val['FTLngFile'],$v));
                            $aRes    = $tSqlDatq->fetch(PDO::FETCH_ASSOC);

                            $tDisplay = ((!in_array(strtolower($v),$aLang) && !empty($aLang))?'style="display:none"':'');
                            $tDisabled  = !isset($aRes['FNLngId'])?"disabled":"";
                            $nId        = isset($aRes['FNLngId'])?$aRes['FNLngId']:"";
                            $tValue     = isset($aRes['FTLngValue'])?$aRes['FTLngValue']:"";

                            $tHtml .= '<td class="xWtr_'.strtolower($v).'" '.$tDisplay.'><input type="text" class="form-control form-control-sm keyLangText" '.$tDisabled.' attr-id="'.$nId.'" attr-old="'.$tValue.'" name="oetLangText['.$nId.']" value="'.htmlspecialchars($tValue).'"></td>';
                        }
                    $tHtml .= '</tr>';
                }
                $aPaging	= array('nCurrent' => $nPage, 'nNext' => ($nPage+1), 'nTotalPage' => round(($nRows/$nLimit),0,PHP_ROUND_HALF_EVEN));
                $aResponse['nCode']         = "100";
                $aResponse['tDescription']  = "succeed";
                $aResponse['tData']         = $tHtml;
                $aResponse['aPagination']   = $aPaging;
                $aResponse['aLang']         = $aResultLang;
                $aResponse['nCountChange']  = $nCountChange;
            }else{
                $aResponse['nCode']         = "900";
                $aResponse['rDescription']  = "error";
            }

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
       
        $aResponse = array();
        $aResponse['nCode']         =  $nStatus;
        $aResponse['rDescription']  = $e->getMessage();
        echo json_encode($aResponse);
        exit;

    }


    
?>