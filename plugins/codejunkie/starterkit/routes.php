<?php

Route::post('api/v1/resetpassword', ['uses' => 'Codejunkie\Starterkit\Controllers\ResetPassword@userResetPassword']);