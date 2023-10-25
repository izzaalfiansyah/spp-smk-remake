<?php

use Illuminate\Support\Facades\Route;

Route::get('/expired', function () {
  $dateNow = strtotime(date('Y-m-d'));
  $dateExpired = strtotime('2023-10-24');

  return [
    'data' => false,
    'time' => [
      'now' => $dateNow,
      'exp' => $dateExpired,
    ]
  ];
});
