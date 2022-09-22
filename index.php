<?php 
    include_once('include_db.php');

    $tSQL = "SELECT FNMLangId,FTMLangName FROM TCNMLang ORDER BY FNMLangSeq asc";    
    $oResulst   = $conn->prepare($tSQL);
    $oResulst->execute();
    $aResulst   = $oResulst->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adasoft-lang Tool</title>
    <link rel="stylesheet" href="assets\bootstrap\css\bootstrap.min.css">
    <link rel="stylesheet" href="assets\css\Ada.Index.css?v=1">
    <script src="assets\jquery\jquery-3.2.1-1.min.js"></script>
    <script src="assets\bootstrap\js\bootstrap.min.js"></script>
</head>
<body>
    <div class="loading" style="display:none">
        <div class="loader"></div>
    </div>
    <div class="container-xxl mt-4">
        <div class="col-md-12 col-lg-12 col-xl-12">
            <div class="card">
                <div class="card-body mt-2 pt-1">
                    <br>
                    <div class="row">
                        <div class="col-12">
                            <div class="col-6 col-md-6 col-sm-6" style="font-size: 36px;">
                            Adasoft-Lang-Tool
                            </div>
                            <div class="col-6 col-md-6 col-sm-6 text-right">
                                <br>
                                <input  type="hidden" id="ohdCountChange" name="ohdCountChange" value="">
                                <button onclick="JSaCGetExport()" class="btn btn-primary obtLangExport" style="display: none;" type="button">ส่งออก</button>&nbsp;&nbsp;
                                <button onclick="JSaCGetSave()" class="btn btn-info obtLangSave" style="display: none;" type="button">บันทึก</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="col-4 col-md-4 col-sm-4">
                                <label for="olaActionType">แอปพลิเคชัน</label>
                                <select name="ocmActionType" id="ocmActionType" class="form-control" onchange="JSxCCheckApp();//JSaCGetDataItem('Y')">
                                    <option value="">กรุณาเลือก</option>
                                    <option value="AdaStoreBack">AdaStoreBack</option>
                                    <option value="AdaStoreFront">AdaStoreFront</option>
                                    <option value="AdaMobile">AdaMobile</option>
                                </select>
                            </div>
                            <div class="col-4 col-md-4 col-sm-4">
                                <label for="olaSearch">ค้นหา</label>
                                <div class="input-group">
                                    <input class="form-control" type="text" id="oetSearch" name="oetSearch" placeholder="กรอกคำค้นหา" onkeyup="Javascript:if(event.keyCode==13) JSaCGetDataItem('Y')" autocomplete="off">
                                    <span class="input-group-btn">
                                        <a id="oahDepositAdvanceSearch" class="btn btn-primary" href="javascript:;" onclick="JSaCGetDataItem('Y')">ค้นหา</a>
                                    </span>
                               </div>
                            </div>
                        </div>
                    </div>
                    <?php if(!empty($aResulst)): ?>
                    <div class="row" style="display:none">
                        <div class="col-12">
                            <div class="col-4 col-md-4 col-sm-4">
                                <label for="olaLanguage">ภาษา</label>
                                <div class="form-check-inline">
                                <?php foreach($aResulst as $tValue): ?>
                                    <label class="form-check-label" for="olaLang_<?php echo $tValue['FTMLangName']; ?>">
                                        <input type="checkbox" class="form-check-input" id="ocbLang_<?php echo $tValue['FTMLangName']; ?>" name="ocbLang" value="<?php echo $tValue['FTMLangName']; ?>" checked disabled >&nbsp;<?php echo $tValue['FTMLangName']; ?>
                                    </label>&nbsp;&nbsp;
                                <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <br>
                    <div class="row">
                       
                        <div class="col-12 col-md-12 col-sm-12" id="odvTable2">
                            <form action="#" name="ofmSave" id="ofmSave" method="post">
                                <div id="odvTable">
                                    <table class="table tableFixHead" border="1">
                                        <thead>
                                            <tr style="background-color: #009988;" id="trHead">
                                                <th class="text-center">key</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                    <div class="pt-3 ">
                                        <div class="col-12">
                                            <div style="text-align: center;display:none" class="load-more pb-3">
                                                <!-- <button type="button" class="col-12 btn btn-gray">Load more</button> -->
                                            </div>
                                            <div class="data-paging" data-current="1" data-npage="1" data-tpage=""></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade xWModalLangSave" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แจ้งเตือน</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    คุณต้องการยืนยันการบันทึกข้อมูลใช่หรือไม่?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary">ยืนยัน</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">ยกเลิก</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade xWModalLangReview" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แจ้งเตือน</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                   มีไฟล์ใหม่รอทำการรีวิวต้องการรีวิวไฟล์หรือไม่
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" attr-review="Y" data-dismiss="modal">ยืนยัน</button>
                    <button type="button" class="btn btn-danger"  attr-review="N" data-dismiss="modal">ยกเลิก</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade xWModalLangExport" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แจ้งเตือน</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    คุณต้องการส่งออกข้อมูลใช่หรือไม่
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">ยืนยัน</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">ยกเลิก</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade xWModalLangExportAlert" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แจ้งเตือน</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                   มีการแก้ไขข้อมูลแล้วยังไม่ได้บันทึกกรุณาบันทึกข้อมูลก่อนทำการส่งออก
                </div>
                <div class="modal-footer">
                    <!-- <button type="button" class="btn btn-primary" data-dismiss="modal">ยืนยัน</button> -->
                    <button type="button" class="btn btn-danger" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade xWModalLangEroor" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แจ้งเตือน</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>
    <a href="javascript:void(0)" class="waves-effect waves-light scrollup scrollup-icon" style="display: none;" ><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M8 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L7.5 2.707V14.5a.5.5 0 0 0 .5.5z"/>
</svg></a>
    <script src="assets\js\JIndex.js?v=1"></script>
</body>
</html>

