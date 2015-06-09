{! '8 haziran pazartesi'}

{ @section('test.index') }

{ @end }

{ if($name == 'Ahmet' || $name = 'Mehmet') }
		Ahmet
{ elseif($name == 'Ahmet') }

{ else }
	isim yok
{ endif }

{ if($surname == 'ATAY') }

{ endif }

{ for($i=1; $i <= 5; $i++) }
	{ if($i == 1) }
			1
	{ elseif($i == 2) }
			2
	{ elseif($i == 3) }
			3
	{ elseif($i == 4) }
			4
	{ elseif($i == 5) }

	{endif}
{ endfor }

{ foreach([1,2,3,4,5] as $num) }
		{! $num }
{ endforeach }

{$g}

{var_dump([1,2,3,4,5])}

{!strlen($g)}

{! "Elisaaaaa"}

{while($x++ <= 10)}
{! $x}
{endwhile}