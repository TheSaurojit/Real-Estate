<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExportDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the entire database to db.sql file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mysqldumpPath = "C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqldump.exe";
        if (!file_exists($mysqldumpPath)) {
            $this->error('MYSQL location not found');
            exit;
        }

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $filePath = base_path('db.sql');

        $command = "\"{$mysqldumpPath}\" --user={$username} --password={$password} --host={$host} {$database} > \"{$filePath}\"";

        shell_exec($command);

        if (file_exists($filePath)) {
            $this->info('Database export successful: ' . $filePath);
        } else {
            $this->error('Database export failed.');
        }
    }
}









// namespace App\Console\Commands;

// use Illuminate\Console\Attributes\Description;
// use Illuminate\Console\Attributes\Signature;
// use Illuminate\Console\Command;

// #[Signature('app:export-database')]
// #[Description('Command description')]
// class ExportDatabase extends Command
// {
//     /**
//      * Execute the console command.
//      */
//     public function handle()
//     {
//         //
//     }
// }
