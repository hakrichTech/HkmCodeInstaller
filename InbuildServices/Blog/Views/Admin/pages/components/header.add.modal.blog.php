<div class="tw_flex tw_space-x-5 tw_max-w-[1000px] tw_justify-between tw_items-center">
    <h3 class="tw_text-xl tw_font-medium tw_text-gray-900 dark:tw_text-white">
        Blog Title :
    </h3>
    <div class="">
        <input type="text" id="first_name" x-model="title" class="tw_input" placeholder="Title" required>
    </div>
    <div class="">
        
        <div class="tw_inline-block tw_mr-4" @click.away="categDropDown=false" >
            <button  @click="toggleCategBlog()" id="filterDButton" data-dropdown-toggle="filterD" class="tw_button_hkm tw_button_addon" type="button">
            <span > --Select category--</span> <svg width="6" height="3" class="tw_ml-2 tw_overflow-visible" aria-hidden="true"><path d="M0 0L3 3L6 0" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg></button>
            <!-- Dropdown menu -->
            <div id="filterD" :class="categDropDown?'tw_block':'tw_hidden'" class="tw_bropdown_body_hkm ">
                <ul x-data="bodyCategory" class="tw_py-1 tw_text-sm tw_text-gray-700 dark:tw_text-gray-200" aria-labelledby="filterDButton">
                <template x-for="(categ, index) in categoriesRef" :key="index">
                        <li >
                        <a href="#" :class="categSelectedBlog(index)?'tw_bg-sky-800 tw_text-white':''" @click="setCategBlog(index),toggleCategBlog()" class="tw_dropdown_elem_hkm" x-text="categ.name"></a>
                        </li>
                    </template>
                        
                </ul>
                <div class="tw_py-1">
                <a href="#" class="tw_dropdown_elem_hkm tw_dropdown_elem_separated_link">Uncategory</a>
                </div>
            </div>
        </div>

    </div>
    <div class="">
            <div class="tw_inline-block tw_mr-4" @click.away="tagDropDown=false">
                    <button  @click="toggleTagBlog()"  id="filterDButton" data-dropdown-toggle="filterD" class="tw_button_hkm tw_button_addon" type="button">
                    <span > --Select Tag--</span> <svg width="6" height="3" class="tw_ml-2 tw_overflow-visible" aria-hidden="true"><path d="M0 0L3 3L6 0" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg></button>
                    <!-- Dropdown menu -->
                    <div id="filterD" :class="tagDropDown?'tw_block':'tw_hidden'" class="tw_bropdown_body_hkm">
                        <ul x-data="bodyTag" class="tw_py-1 tw_text-sm tw_text-gray-700 dark:tw_text-gray-200" aria-labelledby="filterDButton">
                           <template x-for="(tagName, index) in tagsRef" :key="index">
                                <li >
                                    <a href="#" :class="tagSelectedBlog(index)?'tw_bg-sky-800 tw_text-white':''" @click="setTagBlog(index),toggleTagBlog()" class="tw_dropdown_elem_hkm" x-text="tagName.name" ></a>
                                </li>
                            </template>
                                
                        </ul>
                        <div class="tw_py-1">
                        <a href="#" class="tw_dropdown_elem_hkm tw_dropdown_elem_separated_link">Untag</a>
                        </div>
                    </div>
            </div>

    </div>
    <div class="tw_features"  @click.away="blogFeatures=false">
        <button type="button" @click="blogFeaturesToggle()" class="tw_text-white tw_bg-indigo-600 hover:tw_bg-indigo-600/90 focus:tw_ring-4 focus:tw_outline-none focus:tw_ring-indigo-600/50 tw_font-medium tw_rounded-lg tw_text-sm tw_px-5 tw_py-2.5 tw_text-center tw_inline-flex tw_items-center dark:focus:tw_ring-indigo-600/55 tw_mr-2 tw_mb-2">
          <span class="tw_w-4 tw_h-4 tw_mr-2 tw_-ml-1" n><i class="fa fa-plus" aria-hidden="true"></i></span>    
            Add Features
        </button>
        <div :class="blogFeatures?'':'tw_hidden'" class="tw_features-body tw_absolute">
            <div class="tw_feature">
                Add Feature Image: 
                <template x-if="CheckfeatureImage()">
                   <a :href="getFilestoreData_FL(onInit_FL_plugin('fi')).other.link" target="_blank" class="tw_text-sm tw_underline" rel="noopener noreferrer" x-text="getFilestoreData_FL(onInit_FL_plugin('fi')).other.link"></a> 
                </template>
                <br> <a href="#" @click="onInit_FL(onInit_FL_plugin('fi')),toggle_FL(),FL_current={'plugin':bodyAddModalBlogData(),'amount':1,'fl':'fi'};" class="tw_text-sm tw_underline">Edit</a>
            </div>
            <div class="tw_feature">
                
            
                <label for="message" class="tw_block tw_mb-2 tw_text-sm tw_font-medium tw_text-gray-900 dark:tw_text-gray-400">Add Blog Description</label>
                <textarea id="message" rows="4" x-model="description" class="tw_block tw_p-2.5 tw_w-full tw_text-sm tw_text-gray-900 tw_bg-slate-50 tw_rounded-lg tw_border tw_border-gray-300 focus:tw_ring-blue-500 focus:tw_border-blue-500 dark:tw_bg-slate-700 dark:tw_border-gray-600 dark:tw_placeholder-gray-400 dark:tw_text-white dark:focus:tw_ring-blue-500 dark:focus:tw_border-blue-500" placeholder="Your message..."></textarea>
                <div class="tw_text-xs tw_text-right tw_pt-1">0/170</div>
        
            </div>

        </div>
    </div>
</div>
<button  @click="addBlogButtonToggle()" type="button" class="tw_text-gray-400 tw_bg-transparent hover:tw_bg-slate-200 hover:tw_text-gray-900 tw_lg tw_text-sm tw_p-1.5 tw_ml-auto tw_inline-flex tw_items-center dark:hover:tw_bg-slate-600 dark:hover:tw_text-white" data-modal-toggle="extralarge-modal">
    <svg class="tw_w-5 tw_h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
</button> 