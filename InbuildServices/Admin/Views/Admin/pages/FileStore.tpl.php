<script src='/assets/js/validation.js'></script>


<main x-data="secondFilestore">
<?php
include __DIR__."/components/modal.filestore.php";
?>
<span x-ref="div" x-model="fileInputData"></span>

<div class="pd-ltr-20  customscroll customscroll-10-p height-100-p xs-pd-20-10 " >

  <div class="tw_grid tw_grid-cols-3 tw_gap-4">





      <div class="tw_col-span-2 tabs">
        <ul id="tabs" class="tw_inline-flex tw_w-full tw_px-1 tw_pt-2 ">
            <li class="tw_px-4 tw_py-2 tw_font-semibold  tw_rounded-t tw_opacity-50 tw_border-b-2"><a id="default-tab" href="#first">
              <i class="fa-solid fa-cloud-arrow-up"></i>
              Upload
            </a></li>
            <li class="tw_px-4 tw_py-2 tw_font-semibold  tw_rounded-t tw_opacity-50 tw_border-b-2"><a href="#second">Files Library</a></li>
            <li class="tw_px-4 tw_py-2 tw_font-semibold  tw_rounded-t tw_opacity-50 tw_border-b-2"><a href="#third">Trash</a></li>
            <!-- <li class="tw_px-4 tw_py-2 tw_font-semibold  tw_rounded-t tw_opacity-50 tw_border-b-2"><a href="#fourth">Others</a></li> -->
        </ul>




         <!-- Tab Contents -->
         <div id="tab-contents">

              <div id="first" class="tw_p-4">
                  <div class="tw_drop-shadow-xl tw_bg-slate-100 dark:tw_bg-transparent" x-id="['file-input2']">
                     <div class="post-section post-media" id = "filesChange">
                      <template x-if="checkFileUpload()">
                            <div class="post-media-inner  tw_flex-wrap tw_text-center tw_flex " id="media-inner">

                               <template x-for="(item, index) in filesPreview" :key="index" >
                                   <template x-if="!jQuery.isEmptyObject(item)">
                                      <div class="post-media-image">
                                          <div class="post-media-icon thumbnail tw_pt-5">
                                                <span class="delete-media"  @click="removeFileInfilestore(index)"><svg class="tw_w-5 tw_h-5" style="display:inline-block" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg></span>
                                                <i class="fa-solid fa-file FILE"></i>
                                                <div class="nm-info">
                                                <span x-html="'<strong>Name :</strong>'+item.filename"></span>
                                                <span x-html="'<strong>Size :</strong>'+item.size+'B'"></span>
                                                </div>
                                          </div>
                                          <div class="progress-area onprogress">
                                              <li class="row" style="list-style: none;">
                                                    <div class="content">
                                                      <div class="details">
                                                        <span class="name">• Pending</span>
                                                        <span class="percent" :id="'percent_'+index">0%</span>
                                                      </div>
                                                      <div class="progress-bar">
                                                        <div class="progress " :id="'progress_'+index" style="width: 0%"></div>
                                                      </div>
                                                    </div>
                                                  </li>
                                          </div>
                                        </div>
                                   </template>
                                   
                               </template>
                                <span class=" button post-media-icon tw_pt-5 btn-default " style="cursor:pointer;" @click="fileUploadToggle()" >
                                  <span class="c-m-i tw_inline-block">
                                  <i class="fa-solid fa-cloud-arrow-up" style="display: inline-block;margin-right:10px;"></i><br>
                                    Add File
                                  </span>
                                </span>
                            </div>
                      </template>
                        
                        <template x-if="!checkFileUpload()">
                            <div  id="select-files" @click="$refs.div._x_model.set('#'+$id('file-input2')); onFileStoreChange(); $('#'+$id('file-input2'))[0].click();" class="
                                tw_relative 
                                tw_w-[100%] 
                                tw_h-[320px]
                                tw_flex
                                tw_justify-center
                                tw_items-center
                                tw_text-center
                                tw_rounded-[5px]
                                tw_border-2
                                tw_border-dashed
                                dark:tw_border-slate-200
                                tw_border-slate-500
                                selector-file preview-box-files">
                                    <div class="cancel-icon"><i class="fas fa-times"></i></div>
                                    <div class="img-preview"></div>
                                    <div class="content">
                                        <div class="img-icon"> <i class="fa-solid fa-cloud-arrow-up"></i></div>
                                        <div class="text">Browse Files to Upload!</div>
                                    </div>
                                </div>
                        </template>
                         
                        
                    </div>


                        <form class="fileUpload2 tw_mt-6" >
                          <input class="file-inputs"  :id="$id('file-input2')" multiple autocomplete="off" type="file" name="file" hidden>
                          <div class="tw_flex tw_space-x-2 tw_justify-between tw_items-center tw_mb-8">
                            <div class="tw_w-auto tw_text-sm">Didn't receive an OTP?<br>
                            <a href="#" class="tw_text-xs tw_underline">Resend OTP</a></div>             

                              <button type="submit" @click="startToUpload()"  class="btn 
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
                                  " x-html="fileSubmitButton"> </button>
                          </div>
                        </form>
                  </div>
              </div>
              <?php
              include __DIR__."/components/second.filestore.php";
              
              include __DIR__."/components/third.filestore.php";
              ?>
              <!-- <div id="fourth" class="tw_hidden p-4">
                Fourth tab
              </div> -->
         </div>

        

      </div>
      <div class="wrapper files_list">
          <header>Recently uploaded files</header>
          <br>

            <section class="uploaded-area tw_shadow-md" id="uploaded-area2">
                  
              <template x-for="item in filestoreRecentyUpload()">
                  <li class="row">
                        <div class="content upload">
                        <i :class="getFilestoreData(item).icon"></i>
                        <div class="details">
                            <span class="name" x-text="getFilestoreData(item).file.filename"> </span>
                            <span class="size" x-text="getFilestoreData(item).file.size"></span>
                        </div>
                    </li>
              </template>
                
            </section>

        </div>
  </div>

</div>
</main>



<script src="/assets/js/filestore.js"></script>