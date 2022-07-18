<main >
<div class="pd-ltr-20  customscroll customscroll-10-p height-100-p xs-pd-20-10">
			<div class="min-height-200px">
				<?php include_once __DIR__."/components/page.header.php";?>
				<div class="row">
					<?php
					 include_once __DIR__."/components/view_aside_profile.php";
					 include_once __DIR__."/components/tap.profile.php";
					  ?>
				</div>
			</div>
		</div>
</main>
<script src="/assets/js/script.js"></script>
	<script src="/assets/js/process.js"></script>
	<script src="/assets/js/layout-settings.js"></script>
	<script src="/assets/js/plugins/cropperjs/dist/cropper.js"></script>
	<script>
		window.addEventListener('DOMContentLoaded', function () {
			var image = document.getElementById('image');
			var cropBoxData;
			var canvasData;
			var cropper;

			$('#modal').on('shown.bs.modal', function () {
				cropper = new Cropper(image, {
					autoCropArea: 0.5,
					dragMode: 'move',
					aspectRatio: 3 / 3,
					restore: false,
					guides: false,
					center: false,
					highlight: false,
					cropBoxMovable: false,
					cropBoxResizable: false,
					toggleDragModeOnDblclick: false,
					ready: function () {
						cropper.setCropBoxData(cropBoxData).setCanvasData(canvasData);
					}
				});
			}).on('hidden.bs.modal', function () {
				cropBoxData = cropper.getCropBoxData();
				canvasData = cropper.getCanvasData();
				cropper.destroy();
			});
		});
	</script>