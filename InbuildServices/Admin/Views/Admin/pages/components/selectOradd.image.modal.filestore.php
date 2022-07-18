<!-- Extra Large Modal  tw_flex -->
<div id="extralarge-modal" tabindex="-1" :class = "FL_show?'tw_flex':'tw_hidden'"  class=" tw_items-center tw_overflow-y-auto tw_overflow-x-hidden tw_fixed tw_top-0 tw_right-0 tw_justify-center tw_left-0 tw_z-[10000] tw_w-full md:tw_inset-0 tw_h-modal md:tw_h-full tw_bg-slate-900/40">
    <div class="tw_relative tw_p-4 tw_w-full tw_max-w-2xl tw_h-full md:tw_h-auto">
        <!-- Modal content -->
        <div class="tw_relative tw_bg-white tw_rounded-lg tw_shadow dark:tw_bg-slate-700">
            <!-- Modal header -->
            <div class="tw_flex tw_justify-between tw_items-center tw_p-5 tw_rounded-t tw_border-b dark:tw_border-gray-600">
            <?php include __DIR__."/header.add.modal.filestore.php"; ?>
            </div>
            <!-- Modal body -->
            <div class="tw_p-6 tw_space-y-6">
             <?php include __DIR__."/body.add.modal.filestore.php"; ?>
             </div>
            <!-- Modal footer -->
            <div class="tw_flex tw_items-center tw_p-6 tw_space-x-2 tw_rounded-b tw_border-t tw_border-gray-200 dark:tw_border-gray-600">
                <button data-modal-toggle="extralarge-modal" type="button" class="tw_text-white tw_bg-blue-700 hover:tw_bg-blue-800 focus:tw_ring-4 focus:tw_outline-none focus:tw_ring-blue-300 tw_font-medium tw_rounded-lg tw_text-sm tw_px-5 tw_py-2.5 tw_text-center dark:tw_bg-blue-600 dark:hover:tw_bg-blue-700 dark:focus:tw_ring-blue-800">Save</button>
            </div>
        </div>
    </div>
</div>