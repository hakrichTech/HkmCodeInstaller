
<template x-for="blog in blogsData">

    <a :href="blog.link" class="tw_flex tw_mb-7 tw_flex-col tw_w-11/12 tw_items-center tw_bg-white  tw_rounded-lg tw_border tw_shadow-md md:tw_flex-row md:tw_max-w-xl lg:tw_max-w-3xl hover:tw_bg-slate-100 dark:tw_border-gray-700 dark:tw_bg-slate-800 dark:hover:tw_bg-slate-700">
       <template x-if="blog.is_featureImage">
             <img class="tw_object-cover tw_w-full tw_h-96 tw_rounded-t-lg md:tw_h-auto md:tw_w-48 lg:tw_w-72 md:tw_rounded-none md:tw_rounded-l-lg" :src="blog.featureImage+'_35'" alt="">
       </template> 
       <div class="tw_flex tw_flex-col tw_justify-between tw_p-4 tw_leading-normal">
            <h5 class="tw_mb-2 tw_text-2xl tw_font-bold tw_tracking-tight tw_text-gray-900 dark:tw_text-white" x-text="blog.title"></h5>
            <p class="tw_mb-3 tw_font-normal tw_text-gray-700 dark:tw_text-gray-400" x-text="blog.summary"></p>
            <p>
                <span class="tw_bg-blue-100 tw_text-blue-800 tw_text-xs tw_font-medium tw_inline-flex tw_items-center tw_px-2.5 tw_py-0.5 rounded dark:tw_bg-blue-200 dark:tw_text-blue-800">
                <svg class="tw_mr-1 tw_w-3 tw_h-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                2 minutes ago
                </span>
            </p>
        </div>
    </a>
    
</template>

