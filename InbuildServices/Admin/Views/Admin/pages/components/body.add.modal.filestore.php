
<template x-if="FL_active == 'upload'">

    <div class="tw_flex tw_justify-center tw_items-center tw_w-full">
        <label for="dropzone-file" class="tw_flex tw_flex-col tw_justify-center
        tw_items-center tw_w-full tw_h-64 tw_bg-gray-50 tw_rounded-lg tw_border-2 
        tw_border-gray-300 tw_border-dashed tw_cursor-pointer 
        dark:tw_bg-gray-700 hover:tw_bg-gray-100 dark:tw_border-gray-600
        dark:hover:tw_border-gray-500 dark:hover:tw_bg-gray-800"
        @click="$refs.Fl_div._x_model.set('#dropzone-file'); onFileStoreChange_FL()"
        >
            <div class="tw_flex tw_flex-col tw_justify-center tw_items-center tw_pt-5 tw_pb-6">
                <svg class="tw_mb-3 tw_w-10 tw_h-10 tw_text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                <p class="tw_mb-2 tw_text-sm tw_text-gray-500 dark:tw_text-gray-400"><span class="tw_font-semibold">Click to upload</span> or drag and drop</p>
                <p class="tw_text-xs tw_text-gray-500 dark:tw_text-gray-400">SVG, PNG, JPG or GIF (MAX. 800x400px)</p>
                <span x-ref="Fl_div" x-model="FL_fileInputData"></span>

            </div>
            <input id="dropzone-file" type="file" class="tw_hidden">
        </label>
    </div> 
</template>

<template x-if="FL_active == 'media'">
    <div class="">
        <div class="tw_grid tw_grid-cols-4 tw_gap-2">
           
            <template x-for="item in getMediaFileSType(FL_selector)">
              <div @click="select_FL(item);checkSelect_FL()" class="tw_relative hover:tw_border-2 hover:tw_border-blue-700 tw_cursor-pointer">
                <img class="tw_rounded tw_w-36 tw_h-36" :src="getFilestoreData_FL(item).other.link+'_5'" style="object-fit:cover;" alt="Extra large avatar">  
                <template x-if="FL_selectedFiles.includes(item)">
                     <span class="tw_absolute tw_w-full tw_h-full tw_top-0 tw_bg-gray-700/60">  
                </template>     
              </div>
            </template>
            
            
        </div>
        <div class=" tw_flex tw_space-x-2 tw_justify-between tw_pt-1 tw_items-center">
            <div class="tw_inline-flex tw_mt-2 xs:tw_mt-0">
                <button class="tw_inline-flex tw_items-center tw_py-2 tw_px-4 tw_text-sm tw_font-medium tw_text-white tw_bg-gray-800 tw_rounded-l hover:tw_bg-gray-900 dark:tw_bg-gray-800 dark:tw_border-gray-700 dark:tw_text-gray-400 dark:hover:tw_bg-gray-700 dark:hover:tw_text-white">
                    <svg class="tw_mr-2 tw_w-5 tw_h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                    Prev
                </button>
                <button class="tw_inline-flex tw_items-center tw_py-2 tw_px-4 tw_text-sm tw_font-medium tw_text-white tw_bg-gray-800 tw_rounded-r tw_border-0 tw_border-l tw_border-gray-700 hover:tw_bg-gray-900 dark:tw_bg-gray-800 dark:tw_border-gray-700 dark:tw_text-gray-400 dark:hover:tw_bg-gray-700 dark:hover:tw_text-white">
                    Next
                    <svg class="tw_ml-2 tw_w-5 tw_h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>
            <div class="tw_text-right ">
                <span class="tw_text-sm tw_text-gray-700 dark:tw_text-gray-400">
                      Showing <span class="tw_font-semibold tw_text-gray-900 dark:tw_text-white">1</span> to <span class="tw_font-semibold tw_text-gray-900 dark:tw_text-white">10</span> of <span class="tw_font-semibold tw_text-gray-900 dark:tw_text-white">100</span> Entries
                </span>
            </div>
        </div>
        
    </div> 
</template>

