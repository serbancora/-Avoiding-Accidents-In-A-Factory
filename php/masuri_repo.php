<?php
// masuri_repo.php (SQL Server - sqlsrv)

function fetch_all($stmt): array {
    $rows = [];
    while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $r;
    }
    return $rows;
}

function get_masuri_with_counts($conn, array $filters): array {
    $sql = "
        select
            m.id_masura,
            m.descriere_masura,
            m.termen_implementare,
            m.status,
            count(am.id_accident) as nr_accidente
        from Masura m
        left join Accident_Masura am on am.id_masura = m.id_masura
        where 1=1
    ";

    $params = [];

    if (!empty($filters['q'])) {
        $sql .= " and m.descriere_masura like ? ";
        $params[] = "%" . $filters['q'] . "%";
    }

    if (!empty($filters['status'])) {
        $sql .= " and m.status = ? ";
        $params[] = $filters['status'];
    }

    if (!empty($filters['from'])) {
        $sql .= " and m.termen_implementare >= ? ";
        $params[] = $filters['from'];
    }

    if (!empty($filters['to'])) {
        $sql .= " and m.termen_implementare <= ? ";
        $params[] = $filters['to'];
    }

    $sql .= "
        group by m.id_masura, m.descriere_masura, m.termen_implementare, m.status
        order by m.id_masura desc
    ";

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) return [];

    return fetch_all($stmt);
}

function get_masura_by_id($conn, int $id): ?array {
    $stmt = sqlsrv_query(
        $conn,
        "select id_masura, descriere_masura, termen_implementare, status from Masura where id_masura = ?",
        [$id]
    );
    if ($stmt === false) return null;

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return $row ?: null;
}

function insert_masura($conn, string $descriere, ?string $termen_implementare, string $status): int {
    // daca termen_implementare e '' sau null, trimitem null
    $termen = ($termen_implementare === null || trim($termen_implementare) === '') ? null : $termen_implementare;

    $sql = "
        insert into Masura(descriere_masura, termen_implementare, status)
        output inserted.id_masura
        values (?, ?, ?)
    ";

    $stmt = sqlsrv_query($conn, $sql, [$descriere, $termen, $status]);
    if ($stmt === false) {
        $errs = sqlsrv_errors();
        $msg = $errs ? $errs[0]['message'] : 'Unknown SQL error';
        throw new Exception("Eroare insert Masura: " . $msg);
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return (int)$row['id_masura'];
}

function update_masura($conn, int $id, string $descriere, ?string $termen_implementare, string $status): void {
    $termen = ($termen_implementare === null || trim($termen_implementare) === '') ? null : $termen_implementare;

    $stmt = sqlsrv_query(
        $conn,
        "update Masura set descriere_masura = ?, termen_implementare = ?, status = ? where id_masura = ?",
        [$descriere, $termen, $status, $id]
    );

    if ($stmt === false) {
        $errs = sqlsrv_errors();
        $msg = $errs ? $errs[0]['message'] : 'Unknown SQL error';
        throw new Exception("Eroare update Masura: " . $msg);
    }
}

function get_masura_usage_count($conn, int $id): int {
    $stmt = sqlsrv_query(
        $conn,
        "select count(*) as c from Accident_Masura where id_masura = ?",
        [$id]
    );
    if ($stmt === false) {
        return -1;
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return (int)($row['c'] ?? 0);
}

function delete_masura_if_unused($conn, int $id): bool {
    $cnt = get_masura_usage_count($conn, $id);

    // dacă nu putem calcula count-ul (eroare), nu ștergem
    if ($cnt < 0) {
        $errs = sqlsrv_errors();
        $msg = $errs ? $errs[0]['message'] : 'Unknown SQL error';
        throw new Exception("Eroare verificare utilizare Masura: " . $msg);
    }

    // dacă există accidente asociate, refuzăm delete
    if ($cnt !== 0) {
        return false;
    }

    $stmt = sqlsrv_query(
        $conn,
        "delete from Masura where id_masura = ?",
        [$id]
    );

    if ($stmt === false) {
        $errs = sqlsrv_errors();
        $msg = $errs ? $errs[0]['message'] : 'Unknown SQL error';
        throw new Exception("Eroare delete Masura: " . $msg);
    }

    return true;
}

