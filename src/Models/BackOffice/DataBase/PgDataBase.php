<?php

namespace FalconERP\Skeleton\Models\BackOffice\DataBase;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Attributes\Scope;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PgDataBase extends BaseModel
{
    use HasFactory;
    protected $connection = 'pgsql';

    protected $table = 'pg_database';

    protected $fillable = [];

    #[Scope]
    public function byDatname($query, string $datname)
    {
        return $query->where('datname', $datname);
    }

    public static function createDatabase(string $base): bool
    {
        self::killPid();

        return DB::unprepared("CREATE DATABASE {$base} WITH TEMPLATE bc_modelo");
    }

    private static function killPid()
    {
        DB::select("SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname='bc_modelo';");
    }
}
