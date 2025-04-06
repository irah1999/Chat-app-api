<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Route::get('/', function () {
    return 'Welcome to Chat App!';
});


Route::get('/insert-temp-users', function () {
    $users = [
            ['name' => 'Arun',      'email' => 'arun01@gmail.com',      'password' => 'Arun@123'],
            ['name' => 'Priya',     'email' => 'priya02@gmail.com',     'password' => 'Priya@123'],
            ['name' => 'Suresh',    'email' => 'suresh03@gmail.com',    'password' => 'Suresh@123'],
            ['name' => 'Divya',     'email' => 'divya04@gmail.com',     'password' => 'Divya@123'],
            ['name' => 'Ravi',      'email' => 'ravi05@gmail.com',      'password' => 'Ravi@123'],
            ['name' => 'Meena',     'email' => 'meena06@gmail.com',     'password' => 'Meena@123'],
            ['name' => 'Karthik',   'email' => 'karthik07@gmail.com',   'password' => 'Karthik@123'],
            ['name' => 'Anjali',    'email' => 'anjali08@gmail.com',    'password' => 'Anjali@123'],
            ['name' => 'Vinoth',    'email' => 'vinoth09@gmail.com',    'password' => 'Vinoth@123'],
            ['name' => 'Lavanya',   'email' => 'lavanya10@gmail.com',   'password' => 'Lavanya@123'],
            ['name' => 'Manoj',     'email' => 'manoj11@gmail.com',     'password' => 'Manoj@123'],
            ['name' => 'Swathi',    'email' => 'swathi12@gmail.com',    'password' => 'Swathi@123'],
            ['name' => 'Rahul',     'email' => 'rahul13@gmail.com',     'password' => 'Rahul@123'],
            ['name' => 'Nisha',     'email' => 'nisha14@gmail.com',     'password' => 'Nisha@123'],
            ['name' => 'Harish',    'email' => 'harish15@gmail.com',    'password' => 'Harish@123'],
            ['name' => 'Aarthi',    'email' => 'aarthi16@gmail.com',    'password' => 'Aarthi@123'],
            ['name' => 'Gokul',     'email' => 'gokul17@gmail.com',     'password' => 'Gokul@123'],
            ['name' => 'Revathi',   'email' => 'revathi18@gmail.com',   'password' => 'Revathi@123'],
            ['name' => 'Deepak',    'email' => 'deepak19@gmail.com',    'password' => 'Deepak@123'],
            ['name' => 'Sahana',    'email' => 'sahana20@gmail.com',    'password' => 'Sahana@123'],
            ['name' => 'Bala',      'email' => 'bala21@gmail.com',      'password' => 'Bala@123'],
            ['name' => 'Keerthi',   'email' => 'keerthi22@gmail.com',   'password' => 'Keerthi@123'],
            ['name' => 'Ramesh',    'email' => 'ramesh23@gmail.com',    'password' => 'Ramesh@123'],
            ['name' => 'Sneha',     'email' => 'sneha24@gmail.com',     'password' => 'Sneha@123'],
            ['name' => 'Naveen',    'email' => 'naveen25@gmail.com',    'password' => 'Naveen@123'],
            ['name' => 'Anu',       'email' => 'anu26@gmail.com',       'password' => 'Anu@123'],
            ['name' => 'Santosh',   'email' => 'santosh27@gmail.com',   'password' => 'Santosh@123'],
            ['name' => 'Vidya',     'email' => 'vidya28@gmail.com',     'password' => 'Vidya@123'],
            ['name' => 'Vikram',    'email' => 'vikram29@gmail.com',    'password' => 'Vikram@123'],
            ['name' => 'Ishwarya',  'email' => 'ishwarya30@gmail.com',  'password' => 'Ishwarya@123'],
        ];

    foreach ($users as $user) {
        $password = ucfirst($user['name']) . '@123'; // e.g., Ramesh@123
        User::updateOrCreate(
            ['email' => $user['email']], // Avoid duplicate
            [
                'name' => $user['name'],
                'password' => Hash::make($password),
            ]
        );
    }

    return '30 temp users inserted successfully.';
});
