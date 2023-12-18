<?php

use AutoCode\AppRouter\Route;
use AutoCode\AppRouter\Utils\RequestMethodEnum;

require 'vendor/autoload.php';

Route::post('{client}', function (){})->name('post');

Route::group('group', [
    Route::get('{id}', function (){})->name('get'),
    Route::group('one', [
        Route::post('test', function (){}),
    ]),


])->name('group1.')->prefix('{id}');

foreach (Route::getInstance()->getRoutes() as $route){
    var_dump($route->getInfo());
}