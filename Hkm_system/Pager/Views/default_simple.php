<?php

use Hkm_code\Pager\PagerRenderer;

/**
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(0);
?>
<nav>
	<ul class="pager">
		<li <?= $pager->hasPrevious() ? '' : 'class="disabled"' ?>>
			<a href="<?= $pager->getPrevious() ?? '#' ?>" aria-label="<?= hkm_lang('Pager.previous') ?>">
				<span aria-hidden="true"><?= hkm_lang('Pager.newer') ?></span>
			</a>
		</li>
		<li <?= $pager->hasNext() ? '' : 'class="disabled"' ?>>
			<a href="<?= $pager->getnext() ?? '#' ?>" aria-label="<?= hkm_lang('Pager.next') ?>">
				<span aria-hidden="true"><?= hkm_lang('Pager.older') ?></span>
			</a>
		</li>
	</ul>
</nav>
