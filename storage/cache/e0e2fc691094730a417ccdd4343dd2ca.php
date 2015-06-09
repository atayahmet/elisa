<html>
	<head>
		<title>Elisa Template Engine</title>
	</head>
	<body>
		<h1><?php echo  'Hello Elisa!'; ?></h1>
		<table border="1">
			<?php foreach(['Ahmet' => 'ATAY', 'Mehmet' => 'YILDIZ'] as $name => $surname): ?>
			<tr>
				<td><?php echo  $name ; ?></td>
				<td><?php echo  $surname ; ?></td>
			</tr>
			<?php endforeach; ?>
		</table>
	</body>
</html>
<?php echo  '8 haziran pazartesi'; ?>

{ @section('test.index') }

{ @end }

<?php if($name == 'Ahmet' || $name = 'Mehmet'): ?>
		Ahmet
<?php elseif($name == 'Ahmet'): ?>

<?php else: ?>
	isim yok
<?php endif; ?>

<?php if($surname == 'ATAY'): ?>

<?php endif; ?>

<?php for($i=1; $i <= 5; $i++): ?>
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
<?php endfor; ?>

<?php foreach([1,2,3,4,5] as $num): ?>
		<?php echo  $num ; ?>
<?php endforeach; ?>

<?php echo $g; ?>

<?php var_dump([1,2,3,4,5]);?>

<?php echo strlen($g); ?>

<?php echo  "Elisaaaaa"; ?>

<?php while($x++ <= 10): ?>
<?php echo  $x; ?>
<?php endwhile; ?>