<?php

include_once 'Layout/Elisa.php';

$elisa = new Elisa;


$elisa->storage('storage/');
$elisa->ext('.html');
$elisa->master('master');

//echo $elisa->view('test.index', ['name' => 'Ahmet', 'surname' => 'ATAY', 'g' => 'Hellooooo', 'x' => 1]);

$elisa->composer('test.index', ['name' => 'Ahmet', 'surname' => 'ATAY', 'g' => 'Hellooooo', 'x' => 1]);


