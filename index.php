<?php

include_once 'Layout/Elisa.php';

$elisa = new Elisa;


$elisa->storage('storage/');
echo $elisa->view('test.index', ['name' => 'Ahmet', 'surname' => 'ATAY', 'g' => 'Hellooooo', 'x' => 1]);


