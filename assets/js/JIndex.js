var tBaseUrl = window.location.origin+'/adasoft-lang/';
var bActionLoadmore = false;
var aObj = {};
var tActionSite= "";
$(window).scroll(function () {
    if ($(this).scrollTop() > 600) {
        $('.scrollup').fadeIn();
    } else {
        $('.scrollup').fadeOut();
    }
});
$('.scrollup').click(function () {
    $("html, body").animate({ scrollTop: 0 }, 600);
    return false;
});
$(document).ready(function () {
    JSaCGetDataItem();
    $(window).scroll(function () {
        if ($(window).scrollTop() + $(window).height() > $(".table").height() && bActionLoadmore == false){
            bActionLoadmore = true;
            setTimeout(function () {
                JSaCGetDataItem();
            }, 500);
        }
    });
    $(".load-more").on("click", function () {
        JSaCGetDataItem();
    });
});
 
$('#ocmActionType').change(function () {
    aObj = {};
    if($(this).val() == ''){
        $('[id^=ocbLang_]').prop("disabled", true);
    }
});
$('[id^=ocbLang_]').change(function () {
    if($(this).prop('checked') == true){
        $('.xWtr_'+$(this).val()).css('display','');
    }else{
        $('.xWtr_'+$(this).val()).css('display','none');
    }
});

function JSxFChecked(){
    var ocbLang = $('input:checkbox[id^=ocbLang_]');
    for(var i=0;i<ocbLang.length;i++){
        if($(ocbLang[i]).prop('checked') == true){
            $('.xWtr_'+$(ocbLang[i]).val()).css('display','');
        }
    }
}
function JSaCGetDataItem(action_search=null) {
    // $('.xWAlert').remove();
    // if( $('select[id=ocmActionType]').val() == '' ||  $('select[id=ocmActionType]').val() == null){
    //     $('select[id=ocmActionType]').after('<p style="color: #ec2320" class=" xWAlert">กรุณาเลือก แอปพลิเคชัน</p>');
    //     return false;
    // }
    $('.loading').show();
    var nNumPage = $(".data-paging").attr('data-npage');
    if (typeof nNumPage === "undefined" || nNumPage == "" || nNumPage == "0") {
        nNumPage = 1;
    }
    var tActionType = $('select[id=ocmActionType]').val()
    var tSearch = $('input[id=oetSearch]').val()
    var aLang2   = [];
    var aInputLang = $('input:checkbox[name=ocbLang]:checked');
    for(var i=0;i<aInputLang.length;i++){
        aLang2.push($(aInputLang[i]).val());
    }
    var tActionSearch = action_search;
    if(tActionSearch == 'Y' && nNumPage != 1){
        nNumPage = 1;
    }
    if(tActionSite != tActionType){
        $('#odvTable table tbody').empty();
    }
    tActionSite = tActionType;
    $.ajaxSetup({
        async: true
    });
    $('.obtLangSave').css("display", "none");
    $('.obtLangExport').css("display", "none");
    if($('#odvTable table tbody tr').length > 0){
        $('.obtLangSave').css("display", "");
        $('.obtLangExport').css("display", "");
    }
   
    // $('#ohdCountChange').val()
    $.ajax({
        type: "POST",
        url: tBaseUrl+"process.php?tActionType=" + tActionType + "&tSearch=" + tSearch + "&nOffset=" + nNumPage + "&nLimit=50&tActionSearch="+tActionSearch+"&aLang="+aLang2.join(','),
        dataType: 'json',
        success: function (oResponse){

            if(nNumPage == 1) {
                $('#odvTable table tbody').empty();
            }
            if (oResponse.nCode == 100) {
               
                if(tActionSearch == 'Y'){
                    $('#odvTable table tbody').empty().append(oResponse.tData);
                }else{
                    $('#odvTable table tbody').append(oResponse.tData);
                }
               
                var html = '<th class="text-center">key</th>';
                $('[id^=ocbLang_]').prop('disabled',true);
                for (let i = 0; i < oResponse.aLang.length; i++) {
                    var tDisplay = 'style="display:none"';
                    if(aLang2.indexOf(oResponse.aLang[i]['FTMLangName'].toLowerCase()) != -1) {  
                        tDisplay = '';
                    }
                    html += '<th class="text-center xWtr_'+(oResponse.aLang[i]['FTMLangName'].toLowerCase())+'" '+tDisplay+'>'+(oResponse.aLang[i]['FTMLangName'])+'</th>';
                    $('[id=ocbLang_'+(oResponse.aLang[i]['FTMLangName'].toLowerCase())+']').prop('disabled',false);
                }
                $('#odvTable #trHead').empty().append(html);

                if($('#odvTable table tbody tr').length > 0){
                    $('.obtLangExport').css("display", "");
                    $('.obtLangSave').css("display", "");
                }

                $('#ohdCountChange').val(oResponse.nCountChange);
                // if(oResponse.nCountChange > 0 && Object.keys(aObj).length == 0){
                //     $('.obtLangExport').prop("disabled", false);
                // }else{
                //     $('.obtLangExport').prop("disabled", true);
                // }

                var ocbLang = $('input:checkbox[id^=ocbLang_]:checked');
                $('[class^=xWtr_]').css('display','none');
                for(var i=0;i<ocbLang.length;i++){
                    if($(ocbLang[i]).prop('checked') == true){
                        $('[class^=xWtr_'+($(ocbLang[i]).val().toLowerCase())+']').css('display','');
                    }
                }

                var nCurrent = oResponse.aPagination.nCurrent;
                var nNext = oResponse.aPagination.nNext;
                var nTotalPage = oResponse.aPagination.nTotalPage;

                $(".data-paging").attr("data-current", nCurrent);
                $(".data-paging").attr("data-npage", nNext);
                $(".data-paging").attr("data-tpage", nTotalPage);

                if (nCurrent == nTotalPage) {
                    $(".load-more").css("display", "none");
                    bActionLoadmore = true;
                } else {
                    $(".load-more").css("display", "");
                    bActionLoadmore = false;
                }
                
                $('.scrollup').fadeIn();
                $(".keyLangText").on("blur", function () {
                    var tLangText = $(this).val();
                    var nLangId = $(this).attr('attr-id');
                    var tOldLangText = $(this).attr('attr-old');
                    if (tLangText === tOldLangText) {
                        delete aObj[nLangId];
                    } else {
                        aObj[nLangId] = tLangText;
                    }
                    JSxCCheckExport();
                });
                JSxCCheckExport();
            }else {
                $('#odvTable table tbody').append("");
            }

            $('.loading').hide();
        }
    });
}

function JSaCGetSave() {
    $('.xWModalLangSave').modal('show');
}
$('.xWModalLangSave .modal-footer .btn-primary').click(function () {
    $('.loading').show();
    var tActionType = $('select[id=ocmActionType]').val();
    $.ajaxSetup({ async: true });
    $.ajax({
        type: "POST",
        url:  tBaseUrl+"process_save.php",
        dataType: 'json',
        data: {
            tActionType: tActionType,
            aDataObj: aObj
        },
        success: function (oResponse) {
            if (oResponse.nCode == 100) {
                if(Object.keys(aObj).length > 0){
                    for (const [key, value] of Object.entries(aObj)) {
                        $('input[name="oetLangText['+key+']"]').attr('attr-old',value);
                      }
                }
                aObj = {};
                $('#ohdCountChange').val(1)
                // $('.obtLangExport').prop("disabled", false);
                $('.xWModalLangSave').modal('hide');
                JSxCCheckExport()
                $('.loading').hide();
            }
        }
    });
});

function JSxCCheckApp() {
    $('.loading').show();
    JSxCCheckExport()
    var tApp = $('select[id=ocmActionType]').val()
    $.ajaxSetup({ async: true });
    $.ajax({
        type: "POST",
        url:  tBaseUrl+"process_load.php",
        dataType: 'json',
        data: {'tApp': tApp,'tActionType':'CheckReview'},
        success: function (oResponse) {
            if(oResponse.nCode == 900){
                $('.xWModalLangReview').modal("show");
            }else if(oResponse.nCode == 100){
                JSaCGetDataItem('Y');
            }
            $('.loading').hide();
        }
    });
}

$('.xWModalLangReview .modal-footer .btn').click(function () {
    $('.loading').show();
    $('.xWModalLangReview').modal("hide");
    var tApp = $('select[id=ocmActionType]').val()
    var tTypeReview = $(this).attr('attr-review');
    if(tTypeReview == 'Y'){
        $.ajaxSetup({ async: true});
        $.ajax({
            type: "POST",
            url:  tBaseUrl+"process_load.php",
            dataType: 'json',
            data: {'tApp': tApp,'tActionType':'LoadInboundReviewNew'},
            success: function (oResponse) {
                if(oResponse.nCode == 100){
                    JSaCGetDataItem('Y');
                }else{
                    $('.xWModalLangEroor').modal('show')
                }
            }
        });
    }else{
        JSaCGetDataItem('Y');
    }
});

function JSaCGetExport(){
    if(Object.keys(aObj).length > 0){
        // $('.obtLangExport').prop("disabled", true);
        $('.xWModalLangExportAlert').modal('show')
        return false;
    }else{
        if(parseInt($('#ohdCountChange').val()) == 0){
            // $('.xWModalLangExportAlert').modal('show')
            // return false;
            // $('.obtLangExport').prop("disabled", true);
        }else{
            // $('.obtLangExport').prop("disabled", false);
        }
    }

    $('.xWModalLangExport').modal('show');
}

$('.xWModalLangExport .modal-footer .btn-primary').click(function () {
    $('.loading').show();
    $('.xWModalLangExport').modal("hide");
    var tApp = $('select[id=ocmActionType]').val()
    $.ajaxSetup({ async: true});
    $.ajax({
        type: "POST",
        url:  tBaseUrl+"process_load.php",
        dataType: 'json',
        data: {'tApp': tApp,'tActionType':'LoadOuttboundReview'},
        success: function (oResponse) {
            if(oResponse.nCode == 100){
                JSaCGetDataItem('Y');
            }
        }
    });
});


function JSxCCheckExport(){
    console.log(Object.keys(aObj).length+"---"+parseInt($('#ohdCountChange').val()));

    if(Object.keys(aObj).length > 0){
        // $('.obtLangExport').prop("disabled", true);
        // $('.xWModalLangExportAlert').modal('show')
    }else{
        if(parseInt($('#ohdCountChange').val()) == 0){
            // $('.xWModalLangExportAlert').modal('show')
            // $('.obtLangExport').prop("disabled", true);
        }else{
            // $('.obtLangExport').prop("disabled", false);
        }
    }
}