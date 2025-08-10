<?php

namespace FalconERP\Skeleton\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Attributes\Scope;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PgDatabase extends BaseModel
{
    use HasFactory;

    protected $table = 'pg_database';

    protected $fillable = [];

    public static $title = 'Pg Database';

    #[Scope]
    public function byDatname($query, string $datname)
    {
        return $query->where('datname', 'bc_'.$datname);
    }

    public static function createDatabase(string $base)
    {
        self::killPid();

        DB::unprepared("CREATE DATABASE bc_{$base} WITH TEMPLATE bc_modelo");
    }

    private static function killPid()
    {
        DB::select("SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname='bc_modelo';");
    }
}
