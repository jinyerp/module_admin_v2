<?php

namespace Jiny\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;

class DatabaseMigrationController extends Controller
{
    private $route;
    
    public function __construct()
    {
        $this->route = 'admin.database.';
    }

    /**
     * 마이그레이션 목록
     */
    public function index(Request $request): View
    {
        $query = DB::table('migrations');

        // 검색 필터
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('migration', 'like', "%{$search}%");
        }

        // 배치 필터
        if ($request->filled('batch')) {
            $query->where('batch', $request->input('batch'));
        }

        // 날짜 필터 (migration 이름의 날짜 prefix 기준)
        if ($request->filled('date_from')) {
            $dateFrom = str_replace('-', '_', $request->date_from); // 2025-07-14 -> 2025_07_14
            $query->where('migration', '>=', $dateFrom);
        }
        if ($request->filled('date_to')) {
            $dateTo = str_replace('-', '_', $request->date_to); // 2025-07-14 -> 2025_07_14
            $query->where('migration', '<=', $dateTo . '_999999999999'); // 해당 날짜의 마지막 migration까지 포함
        }

        // 정렬
        $sort = $request->get('sort', 'id');
        $dir = $request->get('direction', 'desc');
        $query->orderBy($sort, $dir);

        // 페이징
        $perPage = $request->get('per_page', 20);
        $migrations = $query->paginate($perPage)->appends($request->all());

        // 통계 데이터
        $stats = [
            'total' => DB::table('migrations')->count(),
            'latest_batch' => DB::table('migrations')->max('batch'),
            'total_batches' => DB::table('migrations')->distinct('batch')->count(),
        ];

        $route = $this->route;
        return view('jiny-admin::databases.index', compact('migrations', 'stats', 'sort', 'dir', 'route'));
    }

    /**
     * 마이그레이션 상세
     */
    public function show($id): View
    {
        $migration = DB::table('migrations')->where('id', $id)->first();
        if (!$migration) {
            abort(404, 'Migration not found');
        }
        $route = $this->route;

        // 마이그레이션 파일명에서 테이블명 추출 (예: create_users_table)
        $tableName = null;
        if (preg_match('/create_(.*?)_table/', $migration->migration, $matches)) {
            $tableName = $matches[1];
        }

        // 테이블 컬럼 정보 및 타입/코멘트 조회 (DBMS별 분기)
        $columns = null;
        if ($tableName && Schema::hasTable($tableName)) {
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                $columns = DB::select("PRAGMA table_info('$tableName')"); // name, type
            } elseif ($driver === 'mysql') {
                $dbName = DB::getDatabaseName();
                $columns = DB::select(
                    'SELECT COLUMN_NAME, DATA_TYPE, COLUMN_COMMENT FROM information_schema.columns WHERE table_schema = ? AND table_name = ? ORDER BY ORDINAL_POSITION',
                    [$dbName, $tableName]
                );
            }
        }

        return view('jiny-admin::databases.show', compact('migration', 'route', 'tableName', 'columns'));
    }
} 