<?php

namespace App\Console;

use App\Mail\LowStockNotification;
use App\Models\Products;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Mail;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $users = User::all();

            // Iterate over each user
            foreach ($users as $user) {
                $user->total_sales += $user->today_sales;
                $user->today_sales = 0;
                $user->save();
            }
        })->daily();


        $schedule->call(function () {
            $lowStockProducts = Products::where('stock', '<', 15)->get();

            foreach ($lowStockProducts as $product) {
                Mail::to('Admin207@gmail.com')->send(new LowStockNotification($product));
            }
        })->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}