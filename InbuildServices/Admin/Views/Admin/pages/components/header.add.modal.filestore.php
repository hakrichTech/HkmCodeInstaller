
    <div class="tw_border-b tw_border-gray-200 dark:tw_border-slate-700">
        <ul class="tw_flex tw_flex-wrap tw_-mb-px tw_text-sm tw_font-medium tw_text-center tw_text-gray-500 dark:tw_text-gray-400">
            <li class="tw_mr-2">
                <a href="#" @click="FL_active='upload'" :class="FL_active == 'upload'?'active tw_tb_elem_active':'tw_tb_elem_inactive'" class=" tw_tb_elem tw_group">
                    <svg  :class="FL_active == 'upload'?' tw_tb_svg_active':'tw_tb_svg_inactive'" class="tw_tb_svg " fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    Upload
                </a>
            </li>
            <li class="tw_mr-2">
                <a href="#"  @click="FL_active='media'" :class="FL_active == 'media'?'active tw_tb_elem_active':'tw_tb_elem_inactive'" class=" tw_tb_elem tw_group" aria-current="page">
                    <svg  :class="FL_active == 'media'?' tw_tb_svg_active':'tw_tb_svg_inactive'" class="tw_tb_svg " fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>Media
                </a>
            </li>
        </ul>
    </div>


<button type="button" @click="toggle_FL();FL_current=''"  class="tw_text-gray-400 tw_bg-transparent hover:tw_bg-slate-200 hover:tw_text-gray-900 tw_lg tw_text-sm tw_p-1.5 tw_ml-auto tw_inline-flex tw_items-center dark:hover:tw_bg-slate-600 dark:hover:tw_text-white" data-modal-toggle="extralarge-modal">
    <svg class="tw_w-5 tw_h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
</button>
