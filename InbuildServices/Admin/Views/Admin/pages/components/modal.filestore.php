<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered ">
    <div class="modal-content modal_box ">
      <div class="modal-header tw_border-blue-900">
          <h5 class="modal-title dark:tw_text-inherit" id="exampleModalLabel">Attachment details</h5>

          <button type="button" class="tw_ml-auto tw_-mx-1.5 tw_-my-1.5 tw_bg-white tw_text-gray-400 hover:tw_text-gray-900 tw_rounded-lg focus:tw_ring-2 focus:tw_ring-gray-300 
            tw_p-1.5 hover:tw_bg-gray-100 tw_inline-flex tw_h-8 tw_w-8 dark:tw_text-gray-500 dark:hover:tw_text-white dark:tw_bg-gray-800 dark:hover:tw_bg-gray-700" 
            data-dismiss="modal" aria-label="Close">
                <span class="tw_sr-only">Close</span>
                <svg class="tw_w-5 tw_h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
            </button>
      </div>

      <div class="file_display">
        <template x-if="checkFilestoreModal()">
            <i :class="currentFilestoreData.icon"></i>
        </template>
        <template x-if="!checkFilestoreModal()">
            <i class="fa-solid fa-file FILE"></i>
        </template>
      </div>

      <div class="tw_flex tw_space-x-2 tw_justify-between tw_items-center tw_mb-8">
          <div class="tw_w-auto tw_text-sm">
            <template x-if="checkFilestoreModal()">
              
                <template x-for=" data in getCurrentFilestoreKeys('file')">
                     <div class="finfo" x-html="'<strong>'+ucfirst(data)+'</strong>: ' + currentFilestoreData.file[data]">
                </template>
              
            </template>
          </div>  
          <div class="">
            <div class="tw_w-auto tw_text-sm">
                <template x-if="checkFilestoreModal()">
                  
                  <template x-for=" data in getCurrentFilestoreKeys('other')">
                      <div class="finfo" x-html="'<strong>'+ucfirst(data)+'</strong>: ' + currentFilestoreData.other[data]">
                  </template>
                
               </template>
            </div>
            <div class="tw_flex tw_items-center tw_mt-3 tw_space-x-3 tw_divide-x tw_divide-gray-200 dark:tw_divide-gray-600">
                <a href="#" class="tw_text-gray-900 tw_bg-white tw_border tw_border-gray-300 focus:tw_outline-none hover:tw_tw_bg-gray-100 focus:tw_ring-4 focus:tw_ring-gray-200
                  tw_font-medium tw_rounded-lg tw_text-xs tw_px-2 tw_py-1.5 dark:tw_bg-gray-800 dark:tw_text-white 
                  dark:tw_border-gray-600 dark:hover:tw_bg-gray-700 dark:hover:tw_border-gray-600 dark:focus:tw_ring-gray-700">Delete</a>
                <a href="#" class="tw_pl-4 tw_text-sm tw_font-medium tw_text-blue-600 hover:tw_underline dark:tw_text-blue-500">Download</a>
            </div> 
          </div> 

      </div>

      
     

     
      

    
    </div>
  </div>
</div>