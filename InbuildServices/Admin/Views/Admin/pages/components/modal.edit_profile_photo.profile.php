<div class="modal fade " id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content tw_bg-slate-100 dark:tw_bg-gray-900 dark:tw_border-[1px] dark:tw_border-gray-500">
     
      <div class="modal-body tw_border-0"> 
        <div>

              
          <div  id="select-file" class="preview-box selector-file">
              <div class="cancel-icon"><i class="fas fa-times"></i></div>
              <div class="img-preview"></div>
              <div class="content">
                  <div class="img-icon"><i class="far fa-image"></i></div>
                  <div class="text">Browse File to Upload, <br/>to change your profile picture!</div>
              </div>
          </div>
          <section class="progress-area">
          </section>

          <form class="fileUpload tw_mt-6">
            <input class="file-input" autocomplete="off" type="file" accept="image/*" name="file" hidden>
             <div class="tw_flex tw_space-x-2 tw_justify-between tw_items-center tw_mb-8">
               <div class="tw_w-auto tw_text-sm">#For good profile picture select an image with 1by1 ratio.</div>             

                <button type="submit" data-act="profP" disabled id="verifyingBtn" class="btn 
                tw_px-6
                    tw_py-2.5
                    tw_bg-sky-600
                    tw_text-white
                    tw_font-medium
                    tw_text-xs
                    tw_leading-tight
                    tw_uppercase
                    tw_rounded
                    tw_shadow-md
                    dark:disabled:hover:tw_bg-sky-800
                    dark:disabled:focus:tw_bg-sky-800
                    dark:disabled:tw_bg-sky-800
                    hover:tw_bg-sky-700 hover:tw_shadow-lg
                    focus:tw_bg-sky-700 focus:tw_shadow-lg focus:tw_outline-none focus:tw_ring-0
                    active:tw_bg-sky-800 active:tw_shadow-lg
                    tw_transition
                    tw_duration-150
                    tw_ease-in-out
                    "> Submit</button>
            </div>
         </form>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
 $('#select-file').click(function(){
    if ($(this).hasClass('selector-file')) {
      $('.file-input')[0].click();
    }
  });

  $('.file-input').change(function() {
    let file = this.files[0];
    if(file){
        var reader = new FileReader();
        var butns = $(this).next().find(":input[type='submit']");
        
        reader.onload = function(event) {
          if($('#select-file').hasClass('selector-file')) $('#select-file').removeClass('selector-file');
          var imgURL = event.target.result;
          var imgTag = '<img src="'+ imgURL +'" alt="">'; //creating a new img tag to show img
                        $(".img-preview").append(imgTag); //appending img tag with user entered img url
                        // adding new class which i've created in css
                        $(".preview-box").addClass("imgActive");
                        $(".cancel-icon").on("click", function(){
                          $(".preview-box").removeClass("imgActive");
                            butns.prop('disabled',true);
                            butns.html(`submit`);
                            $('form.fileUpload')[0].reset();
                            $(".img-preview img").remove();
                            setTimeout(() => {
                              if(!$('#select-file').hasClass('selector-file')) $('#select-file').addClass('selector-file');
                            }, 500);
                            //we'll remove all new added class on cancel icon click
                            
                            // that's all in javascript/jquery now the main part is PHP
                        });
        };
        reader.onerror = function(event) {
            console.log("I AM ERROR: " + event.target.error.code);
        };
        reader.readAsDataURL(file);

        butns.prop('disabled',false);
    }

  });

  $('form.fileUpload').submit(function (e) {
    e.preventDefault();
    return false;
  });


  $('button#verifyingBtn').click(function(){
        $(this).prop('disabled', true);
        $(this).html(`<i class="fa fa-spinner fa-spin"></i> sending...`);
        $act = $(this).data('act');
        if ($act == 'profP') {
            let file = $('.file-input')[0].files[0];
            let fileName = file.name;
            if(fileName.length >= 12){
              let splitName = fileName.split('.');
              fileName = splitName[0].substring(0, 13) + "... ." + splitName[1];
            }
            uploadFile2(file,fileName,$(this));
        }
  });

  function uploadFile2(fileD,name,btn){
  var progressArea = $('.progress-area');
  let xhr = new XMLHttpRequest();
  xhr.open("POST", getBaseUrl()+"upload/image");
  xhr.upload.addEventListener("progress", ({loaded, total}) =>{
    let fileLoaded = Math.floor((loaded / total) * 100);
    let fileTotal = Math.floor(total / 1000);
    let fileSize;
    (fileTotal < 1024) ? fileSize = fileTotal + " KB" : fileSize = (loaded / (1024*1024)).toFixed(2) + " MB";
        
    let progressHTML = $(`<li class="custom_row">
                          <i class="fas fa-file-alt"></i>
                          <div class="content">
                            <div class="details">
                              <span class="name">${name} • Uploading</span>
                              <span class="percent">${fileLoaded}%</span>
                            </div>
                            <div class="progress-bar">
                              <div class="progress" style="width: ${fileLoaded}%"></div>
                            </div>
                          </div>
                        </li>`);
    progressArea.html(progressHTML);

  });
  xhr.onreadystatechange = function () {
        switch (xhr.readyState) {
            case 0:
                btn.html(`<i class="fa fa-spinner fa-spin"></i> connectig...`);
                break;
            case 1:
                btn.html(`<i class="fa fa-spinner fa-spin"></i> connected...`);
                break;
            case 3:
                btn.html(`<i class="fa fa-spinner fa-spin"></i> processing...`);
                break;
            case 4:
                btn.html(`completed`);
                 if (this.status == 200) {
                    var data = JSON.parse(this.responseText);
                    if (data.uploaded == 1) {
                        $('#profPictu').each(function(){
                          $(this).attr('src',data.url);
                        });
                      $(".cancel-icon")[0].click(); 
                      $('#modal').modal('hide');
                    }
                 }
                 progressArea.html('');
                break;
        
            default:
                break;
        }
    };
  var fdata = new FormData();
  fdata.append('update_profile',"yes");
  fdata.append('upload', fileD, name);
  xhr.send(fdata);
}
</script>