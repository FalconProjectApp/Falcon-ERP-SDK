<?php

namespace FalconERP\Skeleton\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use QuantumTecnology\ModelBasicsExtension\BaseModel;

class PgDatabase extends BaseModel
{
    use HasFactory;

    protected $table = 'pg_database';

    protected $fillable = [];

    public static $title = 'Pg Database';

    public function scopeByDatname($query, string $datname)
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
