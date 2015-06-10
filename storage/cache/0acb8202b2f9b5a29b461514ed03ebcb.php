<html>
	<head>
		
		<title>Elisa Template Engine</title>
		
hello body gds df gdf gdf gdf
sdfsd fsdfsd fsd
hello
elisa
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
<?php $hello = 'test'; ?>
<?php echo  $hello ; ?>
<h1><?php echo  'Extend Html' ; ?></h1>

<h1><?php echo  'Extend 2 Html' ; ?></h1>

<h1><?php echo  'Extend 3 Html' ; ?></h1>

<h1><?php echo  'Extend 3 Html' ; ?></h1>
		
			
		
<h1><?php echo  'Extend 3 Html' ; ?></h1>
hello
footer
<script type="text/javascript">
			//alert('test 2');
			</script>
	</body>
</html>