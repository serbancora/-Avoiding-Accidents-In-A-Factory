<?php
// statistici_repo.php (SQL Server - sqlsrv)

function fetch_all($stmt): array {
    $rows = [];
    while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) $rows[] = $r;
    return $rows;
}

function fetch_one($stmt): ?array {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return $row ?: null;
}

// ---------------------------------------------------------
// KPI: total accidente + pe gravitate (cu scope pe manager)
// ---------------------------------------------------------
function stats_kpi_accidente($conn, string $rol, ?int $dept_id): array {
    $sql = "
        select
            count(*) as total,
            sum(case when a.gravitate = 'minor' then 1 else 0 end) as minor,
            sum(case when a.gravitate = 'grav' then 1 else 0 end) as grav,
            sum(case when a.gravitate = 'mortal' then 1 else 0 end) as mortal
        from Accident a
        where 1=1
    ";

    $params = [];

    if ($rol === 'manager') {
        $sql .= "
            and exists (
                select 1
                from Angajat_Accident aa
                join Angajat ang on ang.id_angajat = aa.id_angajat
                where aa.id_accident = a.id_accident
                  and ang.id_departament = ?
            )
        ";
        $params[] = (int)($dept_id ?? -1);
    }

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) return ['total'=>0,'minor'=>0,'grav'=>0,'mortal'=>0];

    $row = fetch_one($stmt);
    return $row ?: ['total'=>0,'minor'=>0,'grav'=>0,'mortal'=>0];
}

// ---------------------------------------------------------
// Accidente pe lună (ultimele 12 luni), scope manager inclus
// ---------------------------------------------------------
function stats_accidente_pe_luna($conn, string $rol, ?int $dept_id): array {
    $sql = "
        with luni as (
          select dateadd(month, v.n, datefromparts(year(getdate()), month(getdate()), 1)) as luna_start
          from (values (-11),(-10),(-9),(-8),(-7),(-6),(-5),(-4),(-3),(-2),(-1),(0)) v(n)
        )
        select
          format(l.luna_start, 'yyyy-MM') as luna,
          count(a.id_accident) as nr
        from luni l
        left join Accident a
          on a.data_accident >= l.luna_start
         and a.data_accident < dateadd(month, 1, l.luna_start)
    ";

    $params = [];

    if ($rol === 'manager') {
        $sql .= "
         and exists (
            select 1
            from Angajat_Accident aa
            join Angajat ang on ang.id_angajat = aa.id_angajat
            where aa.id_accident = a.id_accident
              and ang.id_departament = ?
         )
        ";
        $params[] = (int)($dept_id ?? -1);
    }

    $sql .= " group by l.luna_start order by l.luna_start;";

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) return [];

    return fetch_all($stmt);
}

// ---------------------------------------------------------
// Accidente pe departament (count distinct accidente asociate)
// ---------------------------------------------------------
function stats_accidente_pe_departament($conn, string $rol, ?int $dept_id): array {
    // numărăm accidentele asociate cu angajați din dept
    $sql = "
        select
          d.nume_departament as departament,
          count(distinct aa.id_accident) as nr
        from Departament d
        left join Angajat a on a.id_departament = d.id_departament
        left join Angajat_Accident aa on aa.id_angajat = a.id_angajat
        where 1=1
    ";
    $params = [];

    if ($rol === 'manager') {
        $sql .= " and d.id_departament = ? ";
        $params[] = (int)($dept_id ?? -1);
    }

    $sql .= "
        group by d.nume_departament
        order by nr desc, d.nume_departament;
    ";

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) return [];

    return fetch_all($stmt);
}

// ---------------------------------------------------------
// Top cauze (Top 8) după nr. de accidente
// ---------------------------------------------------------
function stats_top_cauze($conn, string $rol, ?int $dept_id): array {
    $sql = "
        select top 8
          c.descriere_cauza as cauza,
          count(distinct ac.id_accident) as nr
        from Cauza c
        join Accident_Cauza ac on ac.id_cauza = c.id_cauza
        join Accident a on a.id_accident = ac.id_accident
        where 1=1
    ";

    $params = [];

    if ($rol === 'manager') {
        $sql .= "
          and exists (
            select 1
            from Angajat_Accident aa
            join Angajat ang on ang.id_angajat = aa.id_angajat
            where aa.id_accident = a.id_accident
              and ang.id_departament = ?
          )
        ";
        $params[] = (int)($dept_id ?? -1);
    }

    $sql .= " group by c.descriere_cauza order by nr desc;";

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) return [];

    return fetch_all($stmt);
}

// ---------------------------------------------------------
// Măsuri: distribuție status (in curs / finalizat)
// doar pentru măsuri asociate accidentelor din scope
// ---------------------------------------------------------
function stats_masuri_status($conn, string $rol, ?int $dept_id): array {
    $sql = "
        select
          m.status,
          count(distinct m.id_masura) as nr
        from Masura m
        join Accident_Masura am on am.id_masura = m.id_masura
        join Accident a on a.id_accident = am.id_accident
        where 1=1
    ";

    $params = [];

    if ($rol === 'manager') {
        $sql .= "
          and exists (
            select 1
            from Angajat_Accident aa
            join Angajat ang on ang.id_angajat = aa.id_angajat
            where aa.id_accident = a.id_accident
              and ang.id_departament = ?
          )
        ";
        $params[] = (int)($dept_id ?? -1);
    }

    $sql .= " group by m.status order by m.status;";

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) return [];

    return fetch_all($stmt);
}
