<?php

include_once 'Layout/Elisa.php';

$elisa = new Elisa;


$elisa->storage('storage/');
$elisa->ext('.html');
$elisa->master('test2.master2');

//echo $elisa->view('test.index', ['name' => 'Ahmet', 'surname' => 'ATAY', 'g' => 'Hellooooo', 'x' => 1]);

$elisa->composer('test2.index', ['name' => 'Ahmet', 'surname' => 'ATAY', 'g' => 'Hellooooo', 'x' => 1], true);


