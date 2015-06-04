

	<?php if($name == 'Ahmet' || $name = 'Mehmet'): ?>
		Ahmet

	<?php elseif($name == 'Ahmet'): ?>

	<?php else: ?>
		isim yok
	<?php endif; ?>

	<?php if($surname == 'ATAY'): ?>

	<?php endif; ?>

	{ for($i=1; $i <= 5; $i++) }
		<?php if($i == 1): ?>
			1
		<?php elseif($i == 2): ?>
			2
		<?php elseif($i == 3): ?>
			3
		<?php elseif($i == 4): ?>
			4
		<?php elseif($i == 5): ?>

		<?php endif; ?>
	{ endfor }

	{ foreach([1,2,3,4,5] as $num) }
		{ $num }
	{ endeach }
