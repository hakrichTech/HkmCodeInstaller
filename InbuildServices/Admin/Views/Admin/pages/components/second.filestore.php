<div id="second" class="tw_hidden tw_relative tw_mt-5 ">

   <?php include __DIR__."/header.second.filestore.php"; ?>

    <div class="tw_px-4 tw_pb-4 tw_pt-24 tw_border-[1px] tw_z-10 tw_border-blue-500/25  tw_rounded-lg tw_overflow-y-auto  tw_max-h-[82vh]">
        <div class="post-section post-media " id = "filesChange">
            
         <template x-if="filestoreIsEmpty">
            <div class="tw_text-center tw_items-center">
                <div class="empty_icon">
                    <i class="fa fa-folder-open-o FILE"></i>
                </div>
                <div class="empty_info">
                    <strong class="tw_text-inherit">This folder is Empty</strong>
                    <div class="tw_text-inherit">Add something to make me happy :)</div>
                </div>
            </div>
         </template>
         <template x-if="!filestoreIsEmpty">
                <div class="post-media-inner  tw_flex-wrap tw_text-center tw_flex " >
                        <template x-for="data in initialisedFilestoreData()">
                                <div  class="post-media-image " >
                                    <div @click="currentFilestoreData = data" class="post-media-icon thumbnail tw_pt-5 tw_cursor-pointer" data-toggle="modal" data-target=".bd-example-modal-lg">
                                        
                                        <i :class="data.icon" ></i>

                                        <div class="nm-info">
                                            <span x-html="'<strong>Filename: </strong>'+data.file.filename"></span>
                                            <span x-html="'<strong>Size: </strong>'+data.file.size"></span>

                                        </div>
                                    </div>
                                </div>
                        </template> 
                    
                 </div>
         </template>
            
            
        </div>

        <div class="tw_flex tw_space-x-2 tw_justify-between tw_items-center tw_mb-8">
                <div class="tw_w-auto tw_text-sm">
                    <span x-html="'<strong>Filter</strong>: Showing '+ fldsd + ' of '+ fldsal+' media items <br>'"></span>
                    <span x-html="'<strong>All</strong>: Showing '+ fldl + ' of '+ flal+' media items <br>'"></span>
                </div>             

                
                    <button type="button"  class="btn tw_button_hkm primary"> Load more</button>
        </div>
        
    </div>
                      
                      
</div>