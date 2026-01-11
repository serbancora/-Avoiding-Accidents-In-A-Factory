<?php
// cauze_repo.php (SQL Server - sqlsrv)

function fetch_all($stmt): array {
    $rows = [];
    while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $r;
    }
    return $rows;
}

function get_cauze($conn, array $filters): array {
    $sql = "
        select
            c.id_cauza,
            c.descriere_cauza
        from Cauza c
        where 1=1
    ";

    $params = [];

    if (!empty($filters['q'])) {
        $sql .= " and c.descriere_cauza like ? ";
        $params[] = "%" . $filters['q'] . "%";
    }

    $sql .= " order by c.id_cauza desc ";

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        // debug util in dev; poti scoate ulterior
        // echo "<pre>"; print_r(sqlsrv_errors()); echo "</pre>";
        return [];
    }

    return fetch_all($stmt);
}

function get_cauza_by_id($conn, int $id): ?array {
    $stmt = sqlsrv_query(
        $conn,
        "select id_cauza, descriere_cauza from Cauza where id_cauza = ?",
        [$id]
    );
    if ($stmt === false) return null;

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return $row ?: null;
}

function insert_cauza($conn, string $descriere): int {
    $sql = "
        insert into Cauza(descriere_cauza)
        output inserted.id_cauza
        values (?)
    ";
    $stmt = sqlsrv_query($conn, $sql, [$descriere]);
    if ($stmt === false) {
        $errs = sqlsrv_errors();
        $msg = $errs ? $errs[0]['message'] : 'Unknown SQL error';
        throw new Exception("Eroare insert Cauza: " . $msg);
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return (int)$row['id_cauza'];
}

function update_cauza($conn, int $id, string $descriere): void {
    $stmt = sqlsrv_query(
        $conn,
        "update Cauza set descriere_cauza = ? where id_cauza = ?",
        [$descriere, $id]
    );
    if ($stmt === false) {
        $errs = sqlsrv_errors();
        $msg = $errs ? $errs[0]['message'] : 'Unknown SQL error';
        throw new Exception("Eroare update Cauza: " . $msg);
    }
}

/**
 * Folositor pentru statistici / afișări:
 * câte accidente au cauza respectivă (poate fi 0).
 */
function get_cauze_with_counts($conn, array $filters): array {
    $sql = "
        select
            c.id_cauza,
            c.descriere_cauza,
            count(ac.id_accident) as nr_accidente
        from Cauza c
        left join Accident_Cauza ac on ac.id_cauza = c.id_cauza
        where 1=1
    ";

    $params = [];

    if (!empty($filters['q'])) {
        $sql .= " and c.descriere_cauza like ? ";
        $params[] = "%" . $filters['q'] . "%";
    }

    $sql .= "
        group by c.id_cauza, c.descriere_cauza
        order by c.id_cauza desc
    ";

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) return [];

    return fetch_all($stmt);
}

function get_cauza_usage_count($conn, int $id): int {
    $stmt = sqlsrv_query(
        $conn,
        "select count(*) as c from Accident_Cauza where id_cauza = ?",
        [$id]
    );
    if ($stmt === false) return -1;

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return (int)($row['c'] ?? 0);
}

function delete_cauza_if_unused($conn, int $id): bool {
    $cnt = get_cauza_usage_count($conn, $id);
    if ($cnt !== 0) return false;

    $stmt = sqlsrv_query($conn, "delete from Cauza where id_cauza = ?", [$id]);
    if ($stmt === false) {
        $errs = sqlsrv_errors();
        $msg = $errs ? $errs[0]['message'] : 'Unknown SQL error';
        throw new Exception("Eroare delete Cauza: " . $msg);
    }
    return true;
}

