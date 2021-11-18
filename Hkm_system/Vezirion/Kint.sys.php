<?php

namespace Hkm_code\Vezirion;

use Kint\Renderer\Renderer;


class Kint {
	

	   public static $plugins = null;
		public static $maxDepth = 6;
		public static $displayCalledFrom = true;
		public static $expanded = false;
		public static $richTheme = 'aante-light.css';
		public static $richFolder = false;
		public static $richSort = Renderer::SORT_FULL;
		public static $richObjectPlugins = null;
		public static $richTabPlugins = null;
		public static $cliColors = true;
		public static $cliForceUTF8 = false;
		public static $cliDetectWidth = true;
		public static $cliMinWidth = 40;
}
